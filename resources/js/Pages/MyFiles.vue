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
                <DeleteFilesButton :delete-all="allSelected" :delete-ids="selectedIds" @delete="onDelete"/>
            </div>
        </nav>
        <div class="flex-1 overflow-y-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left w-[30px] max-w-[30px] pr-0">
                        <Checkbox @change="onSelectAllChange" v-model:checked="allSelected"/>
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-[30px] max-w-[30px] pr-0">
                        <Checkbox @change="onSelectCheckboxChange(file)" v-model:checked="selected[file.id]"
                                  :checked="selected[file.id || allSelected]"/>
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
import {httpGet} from "@/Helper/http-helper.js";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFilesButton from "@/Components/app/DeleteFilesButton.vue";

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


