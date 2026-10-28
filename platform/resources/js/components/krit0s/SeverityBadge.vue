<script setup lang="ts">
import { severityMeta } from '@/lib/labels';
import type { Severity } from '@/types';

defineProps<{
    severity: Severity;
    score?: number;
}>();
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded px-2 py-0.5 font-mono text-[0.7rem] font-medium uppercase tracking-wider ring-1 ring-inset"
        :class="severityMeta[severity].classes"
    >
        {{ severityMeta[severity].label }}
        <span v-if="score !== undefined" class="opacity-80">{{ score.toFixed(1) }}</span>
    </span>
</template>
