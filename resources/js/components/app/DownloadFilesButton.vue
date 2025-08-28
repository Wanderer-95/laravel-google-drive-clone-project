<script setup>

import { httpGet } from '@/helper/httpHelper.js';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    all: {
        type: Boolean,
        default: false,
        required: false
    },
    ids: {
        type: Array,
        required: false
    },
    sharedWithMe: {
        type: Boolean,
        default: false
    },
    sharedByMe: {
        type: Boolean,
        default: false
    }
})

function getDownloadFilesUrl() {
    let url = 'file.download';
    if (props.sharedWithMe) {
        url = 'shared-with-me-download';
    } else if (props.sharedByMe) {
        url = 'shared-by-me-download';
    }
    return url;
}

function download() {
    const url = getDownloadFilesUrl();

    if (! props.all && props.ids.length === 0) {
        return;
    }

    const p = new URLSearchParams;
    if (url === 'file.download') {
        p.append('parent_id', usePage().props.parentFolder.id);
    }

    if (props.all) {
        p.append('all', props.all);
    } else {
        for (const id of props.ids) {
            p.append('ids[]', id);
        }
    }
    httpGet(route(url)+'?'+p.toString())
        .then(res => {
            if (! res.url) return;

            const a = document.createElement('a');

            a.download = res.filename;
            a.href = res.url;
            a.click();
        });
}

</script>

<template>
    <button @click="download" class="flex cursor-pointer items-center gap-2 px-8 py-2 bg-gray-600 text-white rounded-xl hover:bg-red-700 active:scale-95 transition transform shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
    </button>
</template>

<style scoped>

</style>
