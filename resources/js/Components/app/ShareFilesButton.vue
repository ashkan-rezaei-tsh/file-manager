<template>
    <button
        @click="onClick"
        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus-ring-blue-700 focus-text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white mr-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
             class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/>
        </svg>
        Share
    </button>

    <ShareFilesModal v-model="showEmailsModal" :all="all" :ids="ids"/>
</template>

<style scoped>

</style>

<script setup>

import ConfirmationDialog from "@/Components/ConfirmationDialog.vue";
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/vue3";
import {showErrorDialog, showSuccessNotification} from "@/event-bus.js";
import ShareFilesModal from "@/Components/app/ShareFilesModal.vue";

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

const showEmailsModal = ref(false);

const emit = defineEmits(['share']);

function onCancel() {
    showEmailsModal.value = false;
}

function onConfirm() {
    if (props.all) {
        form.all = true;
    } else {
        form.ids = props.ids;
    }

    form.post(route('file.share'), {
        onSuccess() {
            emit('share');
            showSuccessNotification('Selected files have been shared.')
        },
        onFinish() {
            showEmailsModal.value = false;
        }
    });
}

function onClick() {
    if (!props.all && !props.ids.length) {
        showErrorDialog('Please select at least one file to share.');
        return;
    }
    showEmailsModal.value = true;
}
</script>
