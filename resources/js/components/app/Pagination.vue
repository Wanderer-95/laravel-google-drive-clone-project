<script setup>

defineProps({
    links: Array
})

</script>

<template>
    <div class="flex items-center gap-1 mt-6">
        <template v-for="(link, index) in links" :key="index">
            <component
                :is="link.url ? 'a' : 'span'"
                :href="link.url"
                class="px-3 py-1 text-sm rounded-md transition"
                :class="{
          'bg-gray-800 text-white hover:bg-gray-700': link.url && !link.active,
          'bg-indigo-600 text-white': link.active,
          'text-gray-500': !link.url,
        }"
                v-html="link.label"
            />
        </template>
    </div>
</template>

<style scoped>

</style>
