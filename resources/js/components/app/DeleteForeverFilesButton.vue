<script setup>

import { ref } from 'vue';
import { showErrorDialog, showErrorNotification, showSuccessNotification } from '@/eventBus.js';
import { useForm } from '@inertiajs/vue3';

const isShowDeleteForeverDialog = ref(false);

const props = defineProps({
    deleteForeverAllFiles: {
        type: Boolean,
        default: false,
        required: false
    },
    deleteForeverFilesIds: {
        type: Array,
        required: false
    }
})


const emit = defineEmits(['deleteForever']);
const deleteForeverFilesForm = useForm({
    all: null,
    ids: [],
});

function onDeleteForeverClick() {
    if (! props.deleteForeverAllFiles && ! props.deleteForeverFilesIds.length) {
        showErrorDialog('Пожалуйста выберите хотя бы один файл!');
        return;
    }

    isShowDeleteForeverDialog.value = true;
}

function onDeleteForeverCancel() {
    isShowDeleteForeverDialog.value = false;
}

function onDeleteForeverConfirm() {
    if (props.restoreAllFiles) {
        deleteForeverFilesForm.all = true;
    } else {
        deleteForeverFilesForm.ids = props.deleteForeverFilesIds;
    }

    deleteForeverFilesForm.delete(route('file.delete-forever'), {
        onSuccess: () => {
            isShowDeleteForeverDialog.value = false;
            emit('deleteForever', {
                all: deleteForeverFilesForm.all,
                ids: props.deleteForeverFilesIds
            });
            showSuccessNotification('Files success deleted forever');
        },
        onError: () => {
            showErrorNotification('Files error deleted forever');
        }
    })
}

</script>

<template>
    <button @click="onDeleteForeverClick" class="flex cursor-pointer items-center gap-2 px-8 py-2 bg-gray-600 text-white rounded-xl hover:bg-red-700 active:scale-95 transition transform shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
        </svg>
    </button>

    <div v-if="isShowDeleteForeverDialog">
        <div class="fixed inset-0 z-40 h-full w-full bg-black opacity-30"></div>
        <div class="fixed top-1/4 left-1/2 z-50 -translate-x-1/2 -translate-y-1/2 transform rounded-xl bg-white px-10 py-7 text-black">
            <h2 class="text-lg font-medium">Please Confirm</h2>
            <div class="mt-3 flex justify-end gap-4 text-lg">
                Are you sure you want to delete forever files?
            </div>
            <div class="flex gap-4 mt-6 justify-end">
                <button
                    @click="onDeleteForeverCancel"
                    type="button"
                    class="cursor-pointer inline-flex items-center px-5 py-2.5 rounded-xl bg-gray-200 text-gray-800 hover:bg-gray-300 transition font-semibold text-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </button>

                <button
                    @click="onDeleteForeverConfirm"
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
