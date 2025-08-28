<script setup>

import { Button } from '@/components/ui/button';
import { onMounted, ref } from 'vue';
import { emitter, SHOW_ERROR_DIALOG } from '@/eventBus.js';

const show = ref(false);
const message = ref('');

function close() {
    show.value = false;
    message.value = '';
}

onMounted(() => {
    emitter.on(SHOW_ERROR_DIALOG, ({message: msg}) => {
        show.value = true;
        message.value = msg;
    });
})

</script>

<template>
    <div v-if="show">
        <div class="fixed inset-0 z-40 h-full w-full bg-black opacity-30"></div>
        <div class="fixed top-1/4 left-1/2 z-50 -translate-x-1/2 -translate-y-1/2 w-1/3 transform rounded-xl bg-white px-6 py-6 text-black">
            <h1 class="text-red-500">Error</h1>
            <p>{{ message }}</p>
            <div class="mt-5 flex justify-end gap-4">
                <Button
                    @click.prevent="close"
                    class="cursor-pointer border border-black bg-white p-3 text-black hover:text-white"
                >
                    OK
                </Button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
