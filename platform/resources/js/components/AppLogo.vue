<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-[#06060a] ring-1 ring-primary/30">
        <AppLogoIcon class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left">
        <span class="truncate font-display text-[0.95rem] font-semibold leading-none tracking-[0.12em]">
            KRIT<span class="font-mono text-primary">0</span>S
        </span>
        <span class="mt-1 truncate font-mono text-[0.6rem] uppercase tracking-[0.2em] text-muted-foreground">pentest reporting</span>
    </div>
</template>
