<script setup>
import Navigation from '@/components/app/Navigation.vue';
import SearchForm from '@/components/app/SearchForm.vue';
import UserSettingsDropdown from '@/components/app/UserSettingsDropdown.vue';
import { emitter, FILE_UPLOADED_STARTED, showErrorDialog, showSuccessNotification } from '@/eventBus.js';
import { onMounted, onUnmounted, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import FileProgress from '@/components/app/FileProgress.vue';
import ErrorDialog from '@/components/app/ErrorDialog.vue';
import Notification from '@/components/app/Notification.vue';

const page = usePage();
const dragOver = ref(false);
const uploadFileForm = useForm({
    files: [],
    relativePaths: [],
    parent_id: null
});

onMounted(() => {
    emitter.on(FILE_UPLOADED_STARTED, uploadFiles);
});
onUnmounted(() => {
    emitter.off(FILE_UPLOADED_STARTED, uploadFiles);
});

function handleDrop(e) {
    dragOver.value = false;
    const files = e.dataTransfer.files;

    if (!files.length) {
        return;
    }

    uploadFiles(files);
}

function uploadFiles(files) {
    uploadFileForm.relativePaths = [...files].map(file => file.webkitRelativePath)
    uploadFileForm.parent_id = page.props.parentFolder.id;
    uploadFileForm.files = files;
    uploadFileForm.post(route('file.store'), {
        onSuccess: () => {
            showSuccessNotification(files.length + ' Files success downloaded');
        },
        onError: (err) => {
            let message = '';

            if (Object.keys(err).length > 0) {
                message = err[Object.keys(err)[0]];
            } else {
                message = 'User error!!!';
            }

            showErrorDialog(message)
        },
        onFinish: () => {
            uploadFileForm.clearErrors();
            uploadFileForm.reset();
        }
    });
}

function onDragOver() {
    dragOver.value = true;
}

function onDragLeave() {
    dragOver.value = false;
}

</script>

<template>
    <div class="flex h-screen w-full gap-4 bg-gray-200">
        <Navigation />
        <main
            class="flex flex-1 flex-col gap-3 px-4"
            :class="dragOver ? 'dropzone' : ''"
            @drop.prevent="handleDrop"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragLeave"
        >
            <transition name="fade">
                <div v-if="dragOver" class="bg-opacity-50 absolute inset-0 z-50 flex flex-col items-center justify-center bg-black">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mb-4 h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 014-4h10a4 4 0 014 4v3H3v-3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10V7a5 5 0 0110 0v3" />
                    </svg>
                    <p class="text-xl text-white">Отпустите файлы для загрузки</p>
                </div>
            </transition>
            <div class="mt-6 flex w-full items-center justify-between">
                <SearchForm />
                <UserSettingsDropdown />
            </div>
            <ErrorDialog />
            <FileProgress :uploadFileForm="uploadFileForm"/>
            <Notification/>
            <div class="flex flex-1 flex-col">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
