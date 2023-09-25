<template>
    <button
        @click="onClick"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus-ring-blue-700 focus-text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
        </svg>

        Restore
    </button>

    <ConfirmationDialog :show="showConfirmationDialog"
                        message="Are you sure you want to restore selected files?"
                        @cancel="onCancel"
                        @confirm="onConfirm"
    />
</template>

<style scoped>

</style>

<script setup>

import ConfirmationDialog from "@/Components/ConfirmationDialog.vue";
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";

const props = defineProps({
    all: {
        type: Boolean,
        required: false,
        default: false
    },
    ids: {
        type: Array,
        required: false,
    }
})

const page = usePage();
const form = useForm({
    all: null,
    ids: [],
    parent_id: null,
})

const showConfirmationDialog = ref(false);

const emit = defineEmits(['restore']);

function onCancel() {
    showConfirmationDialog.value = false;
}

function onConfirm() {
    if (props.all) {
        form.all = true;
    } else {
        form.ids = props.ids;
    }

    form.post(route('file.restore'), {
        onSuccess() {
            emit('restore');
            showSuccessNotification('Selected files have been restored.')
        },
        onFinish() {
            showConfirmationDialog.value = false;
        }
    });
}

function onClick() {
    if (!props.all && !props.ids.length) {
        showErrorDialog('Please select at least one file to restore.');
        return;
    }
    showConfirmationDialog.value = true;
}
</script>
