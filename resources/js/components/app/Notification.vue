<script setup>
import { ref, computed, onMounted } from 'vue'
import { emitter, SHOW_NOTIFICATION } from '@/eventBus.js';

// props
const show = ref(false);
const type = ref('success');
const message = ref('');

const classes = computed(() => {
    return type.value === 'success'
        ? 'bg-green-100 text-green-800 border-green-300'
        : 'bg-red-100 text-red-800 border-red-300'
})

function close() {
    show.value = false;
    type.value = '';
    message.value = '';
}

onMounted(() => {
    let timeout;

    emitter.on(SHOW_NOTIFICATION, function({type: t, message: msg}) {
        show.value = true;
        type.value = t;
        message.value = msg;

        if (timeout) clearTimeout(timeout);

        timeout = setTimeout(() => {
            close();
        }, 5000);
    })
})

</script>

<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="show"
            :class="`fixed top-5 right-5 border px-4 py-3 rounded-lg shadow-lg max-w-sm flex items-center gap-3 ${classes}`"
        >
            <span v-if="type === 'success'">✅</span>
            <span v-else>❌</span>
            <span>{{ message }}</span>
        </div>
    </transition>
</template>
