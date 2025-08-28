<script setup>

import { ref } from 'vue';
import { showErrorDialog, showErrorNotification, showSuccessNotification } from '@/eventBus.js';
import { useForm, usePage } from '@inertiajs/vue3';

const isShowShareDialog = ref(false);
const page = usePage();
const props = defineProps({
    shareAllFiles: {
        type: Boolean,
        default: false,
        required: false
    },
    shareFilesIds: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['share']);

const shareFilesForm = useForm({
    all: null,
    ids: [],
    parent_id: null,
    email: null
});

function onShareClick() {
    if (! props.shareAllFiles && ! props.shareFilesIds.length) {
        showErrorDialog('Пожалуйста выберите хотя бы один файл!');
        return;
    }

    isShowShareDialog.value = true;
}

function onShareCancel() {
    isShowShareDialog.value = false;
}

function onShareConfirm() {
    shareFilesForm.parent_id = page.props.parentFolder.id
    if (props.shareAllFiles) {
        shareFilesForm.all = true;
    } else {
        shareFilesForm.ids = props.shareFilesIds;
    }

    shareFilesForm.post(route('file.share'), {
        onSuccess: () => {
            isShowShareDialog.value = false;
            emit('share', {
                all: shareFilesForm.all,
                ids: props.shareFilesIds
            });
            showSuccessNotification('The file was sent successfully if a user with such email exists.');
        },
        onError: () => {
            showErrorNotification('Files error share');
        }
    })
}

</script>

<template>
    <button @click="onShareClick" class="flex cursor-pointer items-center gap-2 px-8 py-2 bg-gray-600 text-white rounded-xl hover:bg-red-700 active:scale-95 transition transform shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
        </svg>
    </button>

    <div v-if="isShowShareDialog">
        <div class="fixed inset-0 z-40 h-full w-full bg-black opacity-30"></div>
        <div class="fixed top-1/4 left-1/2 z-50 -translate-x-1/2 -translate-y-1/2 transform rounded-xl bg-white px-10 py-7 text-black">
            <div class="mt-3 flex justify-start gap-4 text-lg">
                Share Files
            </div>
            <div class="mt-6">
                <!-- input -->
                <div class="mb-4">
                    <input
                        type="email"
                        v-model="shareFilesForm.email"
                        placeholder="Enter email"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition text-sm"
                    >
                </div>

                <!-- кнопки -->
                <div class="flex gap-2 justify-end">
                    <button
                        @click="onShareCancel"
                        type="button"
                        class="cursor-pointer inline-flex items-center px-5 py-2.5 rounded-xl bg-gray-200 text-gray-800 hover:bg-gray-300 transition font-semibold text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel
                    </button>

                    <button
                        @click="onShareConfirm"
                        type="submit"
                        class="cursor-pointer inline-flex items-center px-5 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700 transition font-semibold text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
