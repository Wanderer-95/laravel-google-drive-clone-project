<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import {
  NavigationMenuLink,
  type NavigationMenuLinkEmits,
  type NavigationMenuLinkProps,
  useForwardPropsEmits,
} from 'reka-ui'
import { usePage } from '@inertiajs/vue3';

const props = defineProps<NavigationMenuLinkProps & { class?: HTMLAttributes['class'], active?: boolean }>()
const emits = defineEmits<NavigationMenuLinkEmits>()
const delegatedProps = reactiveOmit(props, 'class')
const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <NavigationMenuLink
    data-slot="navigation-menu-link"
    v-bind="forwarded"
    :class="cn(`flex py-1.5 px-4 items-center hover:text-indigo-600 hover:bg-indigo-200 w-40 hover:rounded-lg text-sm mb-2 `,
    props.active ? 'bg-indigo-200 rounded-lg' : ''
    , props.class)"
  >
    <slot />
  </NavigationMenuLink>
</template>
