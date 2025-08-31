<script setup lang="ts">
import CreateNewDropdown from '@/components/app/CreateNewDropdown.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { NavigationMenu, NavigationMenuLink } from '@/components/ui/navigation-menu';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const cleanPath = computed(() => {
    const fullUrl = page.url;
    return new URL(fullUrl, window.location.origin).pathname;
});

</script>

<template>
    <nav class="min-w-[200px]">
        <Link class="flex h-[80px] items-center gap-3 px-6" :href="route('myFiles')">
            <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-black" />
            LaraStorage
        </Link>
        <div class="px-3">
            <CreateNewDropdown />

            <div class="mt-2">
                <NavigationMenu>
                    <NavigationMenuLink :href="route('myFiles')" :active="cleanPath === '/my-files'"> My Files </NavigationMenuLink>
                    <NavigationMenuLink :href="route('shared-with-me')" :active="cleanPath === '/sharedWithMe'"> Shared with me </NavigationMenuLink>
                    <NavigationMenuLink :href="route('shared-by-me')" :active="cleanPath === '/sharedByMe'"> Shared by me </NavigationMenuLink>
                    <NavigationMenuLink :href="route('trash')" :active="cleanPath === '/trash'"> Trash </NavigationMenuLink>
                </NavigationMenu>
            </div>
        </div>
    </nav>
</template>

<style scoped></style>
