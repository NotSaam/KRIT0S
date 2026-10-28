<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <!-- KRIT0S mark: a K whose arm is the judge's stroke, with the verdict dot. -->
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" :class="className" v-bind="$attrs">
        <path d="M9 6v20M9 16l9.5-9.5M12.6 12.6 20.5 26" stroke="#2dd4bf" stroke-width="2.8" stroke-linecap="round" />
        <circle cx="24" cy="8" r="2.4" fill="#8b5cf6" />
    </svg>
</template>
