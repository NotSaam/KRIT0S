<script setup lang="ts">
defineProps<{
    title: string;
    eyebrow?: string;
    description?: string;
}>();
</script>

<template>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="hud-label mb-2">{{ eyebrow }}</p>
            <h1 class="font-display text-2xl font-semibold tracking-tight sm:text-3xl">{{ title }}</h1>
            <p v-if="description" class="mt-1.5 max-w-2xl text-sm text-muted-foreground">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
