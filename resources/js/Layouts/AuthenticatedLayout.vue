<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation/>
        <main class="flex flex-col flex-1 px-4 overflow-hidden">
            <div class="flex items-center justify-between w-full">
                <SearchForm/>
                <UserSettingsDropdown/>
            </div>
            <div class="flex-1 flex flex-col overflow-hidden">
<!--                <button @click="toggleDarkMode">Toggle</button>-->
                <slot/>
            </div>
        </main>
    </div>
</template>

<style>
:root {
    color-scheme: only light;
}

</style>
<script setup>
import {ref} from 'vue';
import Navigation from "@/Components/app/Navigation.vue";
import SearchForm from "@/Components/app/SearchForm.vue";
import UserSettingsDropdown from "@/Components/app/UserSettingsDropdown.vue";

// function toggleDarkMode() {
//     if (document.documentElement.classList.contains('dark')) {
//         document.documentElement.classList.remove('dark');
//     } else {
//         document.documentElement.classList.add('dark');
//     }
// }
</script>
