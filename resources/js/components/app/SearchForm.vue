<script setup lang="ts">

import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    search: '',
})

</script>

<template>

    <div class="w-96">
        <Input type="text" placeholder="Search for files and folders" v-model="form.search" class="border-slate-400"/>
    </div>

</template>

<style scoped>

</style>
