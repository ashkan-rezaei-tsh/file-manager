<template>
    <div class="w-[600px] h-[80px] flex items-center">
        <TextInput type="text"
                   class="block w-full me-2"
                   @keyup.enter="onSearch"
                   v-model="search" autocomplete
                   placeholder="Search for files and folders"/>
    </div>
</template>

<script setup>
import TextInput from "@/Components/TextInput.vue";
import {onMounted, ref} from "vue";
import {router} from "@inertiajs/vue3";
import {emitter, ON_SEARCH} from "@/event-bus.js";

const search = ref('');

let params = new URLSearchParams(window.location.search);

function onSearch() {
    params.set('search', search.value);
    router.get(window.location.pathname + '?' + params.toString());

    emitter.emit(ON_SEARCH, search.value);
}

onMounted(() => {
    params = new URLSearchParams(window.location.search);
    search.value = params.get('search');
})
</script>
