<script setup>
import FileIcon from '@/components/app/FileIcon.vue';
import { httpGet } from '@/helper/httpHelper.js';
import Dashboard from '@/pages/Dashboard.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DeleteFilesButton from '@/components/app/DeleteFilesButton.vue';
import DownloadFilesButton from '@/components/app/DownloadFilesButton.vue';

const loadMoreIntersect = ref(null);
const selectAllFiles = ref(false);
const selectedFiles = ref({});
const isShiftPressed = ref(false);
const lastSelectedIndex = ref(null);

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
});

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next,
});

function loadMore() {
    if (allFiles.value.next === null) {
        return;
    }

    httpGet(allFiles.value.next).then((res) => {
        allFiles.value.data = [...allFiles.value.data, ...res.data];
        allFiles.value.next = res.links.next;
    });
}

function openFolder(file) {
    if (!file.is_folder) {
        return;
    }
    router.get(route('myFiles', { folder: file.path }), {
        preserveScroll: true,
    });
}

function onSelectAllFiles() {
    allFiles.value.data.forEach((file) => {
        selectedFiles.value[file.id] = selectAllFiles.value;
    });
}

function onToggleFileSelect(file) {
    const index = allFiles.value.data.findIndex(f => f.id === file.id);

    if (isShiftPressed.value && lastSelectedIndex.value !== null) {
        const start = Math.min(lastSelectedIndex.value, index);
        const end = Math.max(lastSelectedIndex.value, index);

        for (let i = start; i <= end; i++) {
            const id = allFiles.value.data[i].id;
            selectedFiles.value[id] = true;
        }
    } else {
        selectedFiles.value[file.id] = !selectedFiles.value[file.id];
    }

    lastSelectedIndex.value = index;

    onSelectCheckboxChange(file);
}

function onSelectCheckboxChange(file) {
    if (!selectedFiles.value[file.id]) {
        selectAllFiles.value = false;
        return;
    }

    selectAllFiles.value = allFiles.value.data.every((f) => selectedFiles.value[f.id]);
}

const selectIds = computed(() => Object.entries(selectedFiles.value).filter(f => f[1]).map(f => f[0]));

function onDelete(deletedFiles) {
    selectAllFiles.value = false;
    selectedFiles.value = {};
    allFiles.value.data = allFiles.value.data.filter(f => !deletedFiles.ids.includes(String(f.id)));

}

onMounted(() => {
    const observer = new IntersectionObserver((entries) => entries.forEach((entry) => entry.isIntersecting && loadMore()), {
        rootMargin: '-250px 0px 0px 0px',
    });

    observer.observe(loadMoreIntersect.value);

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Shift') isShiftPressed.value = true;
    });

    window.addEventListener('keyup', (e) => {
        if (e.key === 'Shift') isShiftPressed.value = false;
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', () => {});
    window.removeEventListener('keyup', () => {});
});

watch(
    () => props.files,
    (newFiles) => {
        allFiles.value = {
            data: newFiles.data,
            next: newFiles.links.next,
        };
    },
);
</script>

<template>
    <Dashboard>
        <nav class="mb-6 flex items-center space-x-2 text-sm text-gray-600">
            <template v-for="ancestor in ancestors" :key="ancestor.id">
                <Link v-if="!ancestor.parent_id" class="flex cursor-pointer items-center hover:text-blue-600" :href="route('myFiles')">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="mr-2 size-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                        />
                    </svg>
                    MyFiles
                </Link>
                <div v-else class="flex items-center space-x-1 font-medium text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                    <Link :href="route('myFiles', { folder: ancestor.path })" class="cursor-pointer hover:text-blue-600">
                        {{ ancestor.name }}
                    </Link>
                </div>
            </template>
            <DownloadFilesButton :all="selectAllFiles" :ids="selectIds" />
            <DeleteFilesButton :deletedAllFiles="selectAllFiles" :deleteFilesIds="selectIds" @delete="onDelete" />
        </nav>
        <div class="max-h-[500px] overflow-auto">
            <table class="min-w-full rounded-lg border border-gray-200 bg-white shadow-md">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm tracking-wider text-gray-700 uppercase">
                        <th v-if="allFiles.data.length > 0" class="border-b px-6 py-3 pr-0">
                            <input
                                @change="onSelectAllFiles"
                                v-model="selectAllFiles"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring focus:ring-blue-400"
                            />
                        </th>
                        <th class="border-b px-6 py-3">Name</th>
                        <th class="border-b px-6 py-3">Owner</th>
                        <th class="border-b px-6 py-3">Last Modified</th>
                        <th class="border-b px-6 py-3">Size</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    <tr
                        class="cursor-pointer transition-colors hover:bg-gray-50"
                        v-for="file of allFiles.data"
                        :key="file.id"
                        @dblclick="openFolder(file)"
                        @click="onToggleFileSelect(file)"
                    >
                        <td class="border-b px-6 py-3 pr-0">
                            <input
                                v-model="selectedFiles[file.id]"
                                :checked="selectedFiles[file.id] || selectAllFiles"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring focus:ring-blue-400"
                            />
                        </td>
                        <td class="flex items-center gap-2 border-b px-6 py-4">
                            <FileIcon :file="file" />
                            {{ file.name }}
                        </td>
                        <td class="border-b px-6 py-4">{{ file.owner }}</td>
                        <td class="border-b px-6 py-4">{{ file.updated_at }}</td>
                        <td class="border-b px-6 py-4">{{ file.size }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="allFiles.data.length === 0" class="py-10 text-center text-lg text-gray-400">There is no data in this folder</div>
            <div ref="loadMoreIntersect"></div>
        </div>
    </Dashboard>
</template>

<style scoped></style>
