<script setup lang="ts">

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { ref } from 'vue';
import CreateFolderModal from '@/components/app/CreateFolderModal.vue';
import { Button } from '@/components/ui/button';
import FileUploadedMenuItem from '@/components/app/FileUploadedMenuItem.vue';
import FolderUploadedMenuItem from '@/components/app/FolderUploadedMenuItem.vue';

const isShowCreateFolderModal = ref(false);

function onClickShowCreateFolderModal() {
    isShowCreateFolderModal.value = true;
}

</script>

<template>

    <Menu as="div" class="z-30 relative block text-left">
        <div>
            <MenuButton
                class="cursor-pointer inline-flex w-full justify-center rounded-md bg-black/20 px-4 py-2 text-sm font-medium text-white hover:bg-black/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
            >
                Create new
            </MenuButton>
        </div>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems
                class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none"
            >
                <div class="px-1 py-1" @click.prevent="onClickShowCreateFolderModal">
                    <MenuItem v-slot="{ active }">
                        <Button href="#"
                                  :class="[
                                active ? 'text-white' : 'text-black bg-white',
                                'cursor-pointer group flex w-full items-center text-left rounded-md px-2 py-2 text-sm',
                            ]">
                            New Folder
                        </Button>
                    </MenuItem>
                </div>
                <div class="px-1 py-1">
                    <FileUploadedMenuItem/>
                    <FolderUploadedMenuItem/>
                </div>
            </MenuItems>
        </transition>
    </Menu>
    <CreateFolderModal v-if="isShowCreateFolderModal" v-model="isShowCreateFolderModal"/>
</template>

<style scoped>

</style>
