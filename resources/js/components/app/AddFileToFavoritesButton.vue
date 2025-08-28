<script setup>

import { showErrorDialog, showErrorNotification, showSuccessNotification } from '@/eventBus.js';
import { useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
    favoriteAllFiles: {
        type: Boolean,
        default: false,
        required: false
    },
    favoriteFilesIds: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['addFileToFavorite']);
const favoriteFilesForm = useForm({
    all: null,
    ids: [],
    parent_id: page.props.parentFolder.id
});

function onAddFavoriteClick() {
    if (! props.favoriteAllFiles && ! props.favoriteFilesIds.length) {
        showErrorDialog('Пожалуйста выберите хотя бы один файл!');
        return;
    }

    if (props.favoriteAllFiles) {
        favoriteFilesForm.all = true;
    } else {
        favoriteFilesForm.ids = props.favoriteFilesIds;
    }

    favoriteFilesForm.post(route('file.add-favorite'), {
        onSuccess: () => {
            emit('addFileToFavorite', {
                all: favoriteFilesForm.all,
                ids: props.favoriteFilesIds
            });
            showSuccessNotification('Files success added favorites');
        },
        onError: () => {
            showErrorNotification('Files error added favorites');
        }
    })
}

</script>

<template>
    <button @click="onAddFavoriteClick" class="flex cursor-pointer items-center gap-2 px-8 py-2 bg-gray-600 text-white rounded-xl hover:bg-red-700 active:scale-95 transition transform shadow-md">

    </button>
</template>

<style scoped>

</style>
