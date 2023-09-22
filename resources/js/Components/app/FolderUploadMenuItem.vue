<template>
    <MenuItem v-slot="{active}">
        <a class="text-gray-700 block px-4 py-2 text-sm relative">
            Upload Folder
            <label class="absolute inset-0 cursor-pointer">
                <input @change="onChange" type="file" class="opacity-0 invisible" multiple directory webkitdirectory>
            </label>
        </a>
    </MenuItem>
</template>

<style scoped>

</style>

<script setup>
import {MenuItem} from "@headlessui/vue";
import {emitter, FILE_UPLOAD_STARTED} from "@/event-bus.js";

function onChange(event) {
    console.log(event.target.files);
    emitter.emit(FILE_UPLOAD_STARTED, event.target.files);
}

</script>
