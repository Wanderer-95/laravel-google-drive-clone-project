<script setup>

import { Button } from '@/components/ui/button';
import { MenuItem } from '@headlessui/vue';
import { useTemplateRef } from 'vue';
import { emitter, FILE_UPLOADED_STARTED } from '@/eventBus.js';

const uploadedFolders = useTemplateRef('uploadedFolders');

function openInputFileSelect() {
    uploadedFolders.value.click();
}

function handleFilesChange(event) {
    emitter.emit(FILE_UPLOADED_STARTED, event.target.files);
}

</script>

<template>
    <MenuItem v-slot="{ active }">
        <Button
            @click="openInputFileSelect"
            :class="[active ? 'text-white' : 'bg-white text-black', 'group flex w-full cursor-pointer items-center rounded-md px-2 py-2 text-sm']"
        >
            <input @change="handleFilesChange" ref="uploadedFolders" class="hidden" type="file" multiple directory webkitdirectory />
            Upload Folder
        </Button>
    </MenuItem>
</template>

<style scoped></style>
