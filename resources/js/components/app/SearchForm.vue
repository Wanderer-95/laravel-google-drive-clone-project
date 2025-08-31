<script setup>

import { Input } from '@/components/ui/input';
import { onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const search = ref('');
const params = ref('');

function onSearch() {
    params.value.append('search', search.value);
    router.get(window.location.pathname + '?' + params.value.toString());
}

onMounted(() => {
    params.value = new URLSearchParams(window.location.search);
    search.value = params.value.get('search');
});

</script>

<template>

    <div class="w-96">
        <Input @keyup.enter.prevent="onSearch" type="text" placeholder="Search for files and folders" v-model="search" class="border-slate-400"/>
    </div>

</template>

<style scoped>

</style>
