<template>
    <Modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Share Files
            </h2>
            <div class="mt-6">
                <InputLabel for="email" value="Email Addresses" class="sr-only"/>
                <TextInput type="text"
                           ref="emailInput"
                           id="email"
                           v-model="form.email"
                           class="mt-1 block w-full"
                           :class="form.errors.email?'border-red-500 focus:border-red-500 focus:ring-red-500':''"
                           placeholder="Enter Email Addresses"
                           @keyup.enter="shareFiles"
                />

                <InputError :message="form.errors.email" class="mt-2"/>
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" @click="shareFiles"
                               :class="{'opacity-25': form.processing}"
                               :disable="form.processing">Submit
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<style scoped>

</style>

<script setup>
import Modal from '@/Components/Modal.vue'
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import {useForm, usePage} from "@inertiajs/vue3";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {nextTick, ref} from "vue";
import {showSuccessNotification} from "@/event-bus.js";

const page = usePage();

const form = useForm({
    email: null,
    emails: [],
    all: false,
    ids: [],
    parent_id: null,
});

const emailInput = ref(null)

const props = defineProps({
    modelValue: Boolean,
    ids: [],
    all: Boolean,
})

const emit = defineEmits(['update:modelValue'])


function onShow() {
    nextTick(() => emailInput.value.focus());
}

function shareFiles() {
    form.parent_id = page.props.folder.id;
    form.emails = form.email.split(',');

    if (props.all) {
        form.all = props.all;
        form.ids = [];
    } else {
        form.ids = props.ids;
    }

    console.log(form)
    form.post(route('file.share'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessNotification('Selected files will be shared to users if their emails already exist in system');

            closeModal();
            form.reset();
        },
        onError: () => {
            emailInput.value.focus();
        }
    });
}

function closeModal() {
    emit('update:modelValue');
    form.clearErrors();
    form.reset();
}
</script>

