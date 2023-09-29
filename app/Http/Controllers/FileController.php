<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileActionRequest;
use App\Http\Requests\ShareFilesRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\ToggleFavoriteRequest;
use App\Http\Requests\TrashFileRequest;
use App\Http\Resources\FileResource;
use App\Jobs\SendShareFileNotificationJob;
use App\Mail\SharedFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FileController extends Controller
{
    public function myFiles(Request $request, string $folder = null): AnonymousResourceCollection|Response
    {
        if ($folder) {
            $folder = File::query()
                ->where('created_by', Auth::id())
                ->where('path', $folder)
                ->firstOrFail();
        }

        if (!$folder) {
            $folder = $this->getRoot();
        }

        $files = File::query()
            ->with('starred')
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        // $folder->ancestors comes from set package ;)
        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
    }

    public function createFolder(StoreFolderRequest $request): void
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    public function store(StoreFileRequest $request): void
    {
        $data = $request->validated();
        $parent = $request->parent;
        $fileTree = $request->file_tree;
        $user = $request->user();

        if (!$parent) {
            $this->getRoot();
        }


        if (!empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            foreach ($data['files'] as $file) {
                $this->saveFile($file, $user, $parent);
            }
        }
    }

    public function destroy(FileActionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $parent = $request->parent;

        if ($data['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach ($data['ids'] ?? [] as $id) {
                $file = File::find($id);
                if ($file) {
                    $file->moveToTrash();
                }
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    public function trash(Request $request): AnonymousResourceCollection|Response
    {
        $files = File::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('Trash', compact('files'));
    }


    public function download(FileActionRequest $request): array
    {
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to download'
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name . '.zip';
        } else {
            if (count($ids) === 1) {
                $file = File::find($ids[0]);
                if ($file->is_folder) {
                    if ($file->children->count() === 0) {
                        return [
                            'message' => 'The folder is empty'
                        ];
                    } else {
                        $url = $this->createZip($file->children);
                        $filename = $file->name . '.zip';
                    }
                } else {
                    $dest = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $dest);

                    $url = asset(Storage::url($dest));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);

                $filename = $parent->name . '.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename,
        ];
    }


    public function restore(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->where('created_by', Auth::id())->get();
            foreach ($children as $child) {
                $child->restore();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->whereIn('id', $ids)
                ->get();

            foreach ($files as $file) {
                $file->restore();
            }
        }

        return to_route('trash');
    }

    public function deleteForever(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $children = File::onlyTrashed()->where('created_by', Auth::id())->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->whereIn('id', $ids)
                ->get();

            foreach ($files as $file) {
                $file->deleteForever();
            }
        }

        return to_route('trash');
    }

    public function toggleFavorite(ToggleFavoriteRequest $request)
    {
        $data = $request->validated();
//        $file = File::query()->find($data['id']);

        $starred = StarredFile::query()
            ->where('user_id', Auth::id())
            ->where('file_id', $data['id'])
            ->first();

        // TODO: ADD WHOLE FOLDER TO FAVORITE
//        if ($file->is_folder) {
//
//        }

        if (!$starred) {
            StarredFile::query()->create([
                'file_id' => $data['id'],
                'user_id' => Auth::id(),
            ]);
        } else {
            $starred->delete();
        }
        return response()->json(['status' => true]);
    }

    public function myFavorites()
    {
        $files = File::query()
            ->withWhereHas('starred')
            ->paginate(15);

        $files = FileResource::collection($files);


        return Inertia::render('MyFavorites', compact('files'));
    }

    public function share(ShareFilesRequest $request)
    {
        $data = $request->validated();
        $emails = $data['emails'];
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files to share'
            ];
        }

        $users = User::query()->whereIn('email', $emails)->get();
        //TODO: CHECK FOR NOT EXISTING USERS AND SEND SHARE AND INVITATION EMAIL TO THEM

        if ($users->isEmpty()) {
            return [
                'message' => 'Provided emails are invalid. Please provide exist user\'s emails',
            ];
        }


        if ($all) {
            $files = $parent->children;
        } else {
            $files = File::query()->find($ids);
        }

        foreach ($users as $user) {
            $sharedFiles = [];
            foreach ($files as $file) {
                $isExistInSharedFiles = FileShare::query()
                    ->where('file_id', $file->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($isExistInSharedFiles) {
                    continue;
                }

                FileShare::query()->create([
                    'file_id' => $file->id,
                    'user_id' => $user->id,
                ]);

                $sharedFiles[] = $file;
            }

            if (count($sharedFiles)) {
                dispatch(new SendShareFileNotificationJob($user, Auth::user(), $sharedFiles));
            }
        }

        return redirect()->back();
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    private function saveFileTree(mixed $fileTree, ?File $parent, mixed $user): void
    {
        foreach ($fileTree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->is_folder = 1;
                $folder->name = $name;

                $parent->appendNode($folder);

                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->saveFile($file, $user, $parent);
            }
        }
    }

    /**
     * @param mixed $file
     * @param mixed $user
     * @param File|null $parent
     * @return void
     */
    private function saveFile(mixed $file, mixed $user, ?File $parent): void
    {
        $path = $file->store('/files/' . $user->id);

        /** @var UploadedFile $file */
        $model = new File();
        $model->storage_path = $path;
        $model->is_folder = 0;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();

        $parent->appendNode($model);
    }

    private function createZip(mixed $files): string
    {
        $zipPath = 'zip/' . Str::random() . '.zip';
        $publicPath = "public/$zipPath";


        if (!is_dir(dirname($publicPath))) {
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = ''): void
    {
        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
            }
        }
    }
}
