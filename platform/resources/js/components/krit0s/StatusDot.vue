<script setup lang="ts">
defineProps<{
    label: string;
    dot: string;
}>();
</script>

<template>
    <span class="inline-flex items-center gap-2 whitespace-nowrap text-sm text-muted-foreground">
        <span class="size-1.5 rounded-full" :class="dot" />
        {{ label }}
    </span>
</template>
