<script setup>
const props = defineProps({
    uploadFileForm: Object
})
</script>

<template>
    <div>
        <div v-if="uploadFileForm.progress">
            Uploading {{ uploadFileForm.files.length }} files
        </div>

        <div class="w-96 h-4 bg-gray-700 rounded-full overflow-hidden shadow-inner" v-if="uploadFileForm.progress">
            <div
                class="h-full bg-gray-400 transition-all duration-300"
                :style="{ width: uploadFileForm.progress.percentage + '%' }"
            ></div>
        </div>
    </div>
</template>

<style scoped>

</style>
