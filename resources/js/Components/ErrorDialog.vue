<template>
    <Modal :show="show" max-width="md">
        <div class="p-6 bg-red-600">
            <h2 class="text-2xl mb-2 text-white font-semibold">Error</h2>
            <span class="text-white">{{ message }}</span>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close">OK</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>

<style scoped>

</style>
<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Modal from "@/Components/Modal.vue";
import {onMounted, ref} from "vue";
import {emitter, SHOW_ERROR_DIALOG} from "@/event-bus.js";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const show = ref(false);
const message = ref('');

const emit = defineEmits(['close']);

function close() {
    show.value = false;
    message.value = '';
}

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape)

    emitter.on(SHOW_ERROR_DIALOG, ({message: msg}) => {
        show.value = true;
        message.value = msg;
    })
})

function closeOnEscape(e) {
    if (e.key === 'Escape' && show) {
        close();
    }
}

</script>
