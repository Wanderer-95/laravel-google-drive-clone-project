<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import App from '@/pages/App.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

</script>

<template>

    <Head />

    <App :breadcrumbs="breadcrumbs">
        <slot/>
    </App>

</template>
