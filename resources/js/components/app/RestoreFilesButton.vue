<script setup>

import { ref } from 'vue';
import { showErrorDialog, showErrorNotification, showSuccessNotification } from '@/eventBus.js';
import { useForm } from '@inertiajs/vue3';

const isShowRestoreDialog = ref(false);
const props = defineProps({
    restoreAllFiles: {
        type: Boolean,
        default: false,
        required: false
    },
    restoreFilesIds: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['restore']);
const restoreFilesForm = useForm({
    all: null,
    ids: [],
});

function onRestoreClick() {
    if (! props.restoreAllFiles && ! props.restoreFilesIds.length) {
        showErrorDialog('Пожалуйста выберите хотя бы один файл!');
        return;
    }

    isShowRestoreDialog.value = true;
}

function onRestoreCancel() {
    isShowRestoreDialog.value = false;
}

function onRestoreConfirm() {
    if (props.restoreAllFiles) {
        restoreFilesForm.all = true;
    } else {
        restoreFilesForm.ids = props.restoreFilesIds;
    }

    restoreFilesForm.post(route('restore'), {
        onSuccess: () => {
            isShowRestoreDialog.value = false;
            emit('restore', {
                all: restoreFilesForm.all,
                ids: props.restoreFilesIds
            });
            showSuccessNotification('Files success restore');
        },
        onError: () => {
            showErrorNotification('Files error restore');
        }
    })
}

</script>

<template>
    <button @click="onRestoreClick" class="flex cursor-pointer items-center gap-2 px-8 py-2 bg-gray-600 text-white rounded-xl hover:bg-red-700 active:scale-95 transition transform shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
        </svg>
    </button>

    <div v-if="isShowRestoreDialog">
        <div class="fixed inset-0 z-40 h-full w-full bg-black opacity-30"></div>
        <div class="fixed top-1/4 left-1/2 z-50 -translate-x-1/2 -translate-y-1/2 transform rounded-xl bg-white px-10 py-7 text-black">
            <h2 class="text-lg font-medium">Please Confirm</h2>
            <div class="mt-3 flex justify-end gap-4 text-lg">
                Are you sure you want to restore files?
            </div>
            <div class="flex gap-4 mt-6 justify-end">
                <button
                    @click="onRestoreCancel"
                    type="button"
                    class="cursor-pointer inline-flex items-center px-5 py-2.5 rounded-xl bg-gray-200 text-gray-800 hover:bg-gray-300 transition font-semibold text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </button>

                <button
                    @click="onRestoreConfirm"
                    type="submit"
                    class="cursor-pointer inline-flex items-center px-5 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700 transition font-semibold text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirm
                </button>
            </div>

        </div>
    </div>
</template>

<style scoped>

</style>
