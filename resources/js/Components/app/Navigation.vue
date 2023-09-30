<script setup>
import {Link} from '@inertiajs/vue3';
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import CreateNewDropdown from "@/Components/app/CreateNewDropdown.vue";
import NavLink from "@/Components/NavLink.vue";
</script>

<template>
    <nav class="min-w-[200px]">
        <Link :href="route('myFiles')" class="h-[80px] px-3 flex items-center gap-3">
            <ApplicationLogo
                class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
            />
            LaraStorage
        </Link>
        <div class="px-3">
            <CreateNewDropdown/>
            <div class="py-3">
                <NavLink :href="route('myFiles')" :active="$page.props.route_name === 'myFiles'">My Files</NavLink>
                <NavLink :href="route('myFavorites')" :active="$page.props.route_name === 'myFavorites'">My Favorites</NavLink>
                <NavLink :href="route('sharedWithMe')" :active="$page.props.route_name === 'sharedWithMe'">Shared With Me
                </NavLink>
                <NavLink :href="route('sharedByMe')" :active="$page.props.route_name === 'sharedByMe'">Shared By Me
                </NavLink>
                <NavLink :href="route('trash')" :active="$page.props.route_name === 'trash'">Trash</NavLink>
            </div>
        </div>
    </nav>
</template>

<style scoped>

</style>
