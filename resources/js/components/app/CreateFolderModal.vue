<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useForm, usePage } from '@inertiajs/vue3';
import { onMounted, useTemplateRef } from 'vue';

const model = defineModel<boolean>();

const folderNameInput = useTemplateRef('folderNameInput');

const folder = usePage().props.parentFolder;

const form = useForm({
    name: '',
    parent_id: null
});

function createFolder() {
    form.parent_id = folder.id;

    form.post(route('folder.create'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => folderNameInput.value?.focus(),
    });
}

function closeModal() {
    model.value = false;
    form.clearErrors();
    form.reset();
}

onMounted(() => {
    folderNameInput.value?.focus();
});
</script>

<template>
    <div class="fixed inset-0 z-40 h-full w-full bg-black opacity-30"></div>
    <div class="fixed top-1/4 left-1/2 z-50 -translate-x-1/2 -translate-y-1/2 transform rounded-xl bg-white px-10 py-7 text-black">
        <h2 class="text-lg font-medium">Create new folder</h2>
        <Input ref="folderNameInput" type="text" v-model="form.name" placeholder="Folder Name" class="mt-3 w-76" @keyup.enter="createFolder" />
        <InputError type="text" :v-if="form.errors.name" :message="form.errors.name" class="mt-3 w-76" />
        <div class="mt-5 flex justify-end gap-4">
            <Button @click.prevent="closeModal" class="cursor-pointer border border-black bg-white p-5 text-black hover:text-white"> Cancel </Button>
            <Button
                @click.prevent="createFolder"
                :disabled="form.processing"
                class="cursor-pointer border border-black bg-white p-5 text-black hover:text-white"
            >
                Create
            </Button>
        </div>
    </div>
</template>

<style scoped></style>
