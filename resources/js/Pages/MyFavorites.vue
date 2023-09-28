<template>
    <AuthenticatedLayout>
<!--        <nav class="flex items-center justify-end p-1 mb-3">
            <div>
                <DownloadFilesButton :all="allSelected" :ids="selectedIds" class="mr-2"/>
                <DeleteFilesButton :delete-all="allSelected" :delete-ids="selectedIds" @delete="onDelete"/>
            </div>
        </nav>-->
        <div class="flex-1 overflow-y-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                <tr>
<!--                    <th class="text-sm font-medium text-gray-900 px-2 py-4 text-left">
                        <Checkbox @change="onSelectAllChange" v-model:checked="allSelected"/>
                    </th>-->
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Name
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Path
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="file of allFiles.data" :key="file.id"
                    @click="toggleFileSelect(file)"
                    class="border-b transition duration-300 ease-in-out cursor-pointer"
                    :class="selected[file.id] || allSelected ?'bg-blue-50 hover:bg-blue-100':'bg-white hover:bg-gray-100'">
<!--                    <td class="px-2 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <Checkbox @change="onSelectCheckboxChange(file)" v-model:checked="selected[file.id]"
                                  :checked="selected[file.id || allSelected]"/>
                    </td>-->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                        <FileIcon :file="file"/>
                        {{ file.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ file.path }}
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
import FileIcon from "@/Components/app/FileIcon.vue";
import {computed, onMounted, onUpdated, ref} from "vue";
import {httpGet} from "@/Helper/http-helper.js";
import Checkbox from "@/Components/Checkbox.vue";
import DeleteFilesButton from "@/Components/app/DeleteFilesButton.vue";
import DownloadFilesButton from "@/Components/app/DownloadFilesButton.vue";

const props = defineProps({
    files: Object,
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

function resetForm() {
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


