<template>
    <AuthenticatedLayout>
        <nav class="flex items-center justify-between p-1 mb-3">
            <ul class="inline-flex items-center space-x-1 md:space-x-3">
                <li v-for="ancestor of ancestors.data" :key="ancestor.id" class="inline-flex items-center">
                    <Link v-if="!ancestor.parent_id" :href="route('myFiles')"
                          class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <HomeIcon class="h-4 w-4"/>
                        My Files
                    </Link>
                    <div v-else class="flex items-center">
                        <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path>
                        </svg>
                        <Link :href="route('myFiles', {folder: ancestor.path})"
                              class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white">
                            {{ ancestor.name }}
                        </Link>
                    </div>
                </li>
            </ul>

            <div>
                <DownloadFilesButton :all="allSelected" :ids="selectedIds" class="mr-2"/>
                <DeleteFilesButton :delete-all="allSelected" :delete-ids="selectedIds" @delete="onDelete"/>
            </div>
        </nav>
        <div class="flex-1 overflow-y-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="text-sm font-medium text-gray-900 px-2 py-4 text-left">
                        <Checkbox @change="onSelectAllChange" v-model:checked="allSelected"/>
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-2 py-4 text-left">
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Name
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Owner
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Last Modified
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Size
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="file of allFiles.data" :key="file.id"
                    @click="toggleFileSelect(file)"
                    @dblclick="openFolder(file,$event)"
                    class="border-b transition duration-300 ease-in-out cursor-pointer"
                    :class="selected[file.id] || allSelected ?'bg-blue-50 hover:bg-blue-100':'bg-white hover:bg-gray-100'">
                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <Checkbox @change="onSelectCheckboxChange(file)" v-model:checked="selected[file.id]"
                                  :checked="selected[file.id || allSelected]"/>
                    </td>
                    <td class="py-4 px-2 whitespace-nowrap text-sm font-medium text-gray-900 text-yellow-500">
                        <button @click.stop.prevent="toggleFavorite(file)">
                            <svg v-if="file.is_starred" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                 fill="currentColor"
                                 class="w-6 h-6">
                                <path fill-rule="evenodd"
                                      d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                            </svg>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                        <FileIcon :file="file"/>
                        {{ file.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ file.owner }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ file.updated_at }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ file.size }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div v-if="!allFiles.data.length" class="py-8 text-center text-lg text-gray-400">
                There is no data in this folder
            </div>
            <div ref="loadMoreIntersect"></div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {router, usePage} from "@inertiajs/vue3";
import {Link} from '@inertiajs/vue3'
import {HomeIcon} from "@heroicons/vue/20/solid/index.js";
import FileIcon from "@/Components/app/FileIcon.vue";
import {computed, onMounted, onUpdated, ref} from "vue";
import {httpGet, httpPost} from "@/Helper/http-helper.js";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFilesButton from "@/Components/app/DeleteFilesButton.vue";
import DownloadFilesButton from "@/Components/app/DownloadFilesButton.vue";

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
})

const loadMoreIntersect = ref(null);
const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
});
const allSelected = ref(false);
const selected = ref({});

const page = usePage();


const selectedIds = computed(() => Object.entries(selected.value).filter(a => a[1]).map(a => a[0]));

function onSelectAllChange() {
    allFiles.value.data.forEach(file => {
        selected.value[file.id] = allSelected.value;
    });
}

function toggleFileSelect(file) {
    selected.value[file.id] = !selected.value[file.id];
    onSelectCheckboxChange(file);
}

function onSelectCheckboxChange(file) {
    if (!selected.value[file.id]) {
        allSelected.value = false;
    } else {
        let checked = true;

        for (const f of allFiles.value.data) {
            if (!selected.value[f.id]) {
                checked = false;
                break;
            }
        }

        allSelected.value = checked;
    }
}

function openFolder(file, event) {
    if (event.target.type === 'checkbox') {
        return;
    }

    if (!file.is_folder) {
        return;
    }

    router.visit(route('myFiles', {folder: file.path}))
}

function loadMore() {
    if (allFiles.value.next === null) {
        return;
    }

    httpGet(allFiles.value.next)
        .then(response => {
            allFiles.value.data = [...allFiles.value.data, ...response.data];
            allFiles.value.next = response.links.next;
        })
}

function onDelete() {
    allSelected.value = null;
    selected.value = {};
}

function toggleFavorite(file) {
    httpPost(route('file.toggleFavorite'), {
        id: file.id,
    }).then((response) => {
        file.is_starred = !file.is_starred;
    }).catch((error) => {
        console.log(error);
    })
}

onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next
    }
})

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        return entries.forEach(entry => entry.isIntersecting && loadMore())
    }, {
        rootMargin: '-250px 0px 0px 0px'
    })

    observer.observe(loadMoreIntersect.value);
})

</script>


