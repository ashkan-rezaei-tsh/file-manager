<style>
:root {
    color-scheme: only light;
}

.dropzone {
    width: 100%;
    height: 100%;
    color: #8d8d8d;
    border: 2px dashed rgb(128, 128, 128);
    display: flex;
    justify-content: center;
    align-items: center;
}

</style>

<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation/>
        <main @drop.prevent="handleDrop" @dragover.prevent="handleDragOver" @dragleave.prevent="handleDragLeave"
              class="flex flex-col flex-1 px-4 overflow-hidden" :class="dragOver?'dropzone':''">
            <template v-if="dragOver" class="text-gray-500 text-center py-8">
                Drop files here to upload
            </template>
            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm/>
                    <UserSettingsDropdown/>
                </div>
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!--                <button @click="toggleDarkMode">Toggle</button>-->
                    <slot/>
                </div>
            </template>
        </main>
    </div>

    <ErrorDialog/>
    <FormProgress :form="fileUploadForm"/>
    <Notification/>
</template>

<script setup>
import {onMounted, ref} from 'vue';
import Navigation from "@/Components/app/Navigation.vue";
import SearchForm from "@/Components/app/SearchForm.vue";
import UserSettingsDropdown from "@/Components/app/UserSettingsDropdown.vue";
import {emitter, FILE_UPLOAD_STARTED, showErrorDialog, showSuccessNotification} from "@/event-bus.js";
import {useForm, usePage} from "@inertiajs/vue3";
import FormProgress from "@/Components/app/FormProgress.vue";
import ErrorDialog from "@/Components/ErrorDialog.vue";
import Notification from "@/Components/Notification.vue";

// function toggleDarkMode() {
//     if (document.documentElement.classList.contains('dark')) {
//         document.documentElement.classList.remove('dark');
//     } else {
//         document.documentElement.classList.add('dark');
//     }
// }

const page = usePage();

const fileUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null
})

const dragOver = ref(false);

function handleDragOver() {
    dragOver.value = true;
}

function handleDragLeave() {
    dragOver.value = false;
}

function handleDrop(event) {
    dragOver.value = false;
    const files = event.dataTransfer.files;

    uploadFiles(files);
}

function uploadFiles(files) {
    fileUploadForm.parent_id = page.props.folder.id;
    fileUploadForm.files = files;
    fileUploadForm.relative_paths = [...files].map(file => file.webkitRelativePath);

    fileUploadForm.post(route('file.store'), {
        onSuccess() {
            showSuccessNotification(`${files.length} files have been uploaded.`)
        },
        onError(errors) {
            let message = '';

            if (Object.keys(errors).length) {
                message = errors[Object.keys(errors)[0]];
            } else {
                message = 'Error during file upload. Please try again later.';
            }

            showErrorDialog(message);
        },
        onFinish() {
            fileUploadForm.clearErrors();
            fileUploadForm.reset();
        }
    });
}


onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles);
})

</script>
