<script setup lang="ts">
import { SEVERITIES, severityMeta } from '@/lib/labels';
import type { Severity } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    breakdown: Record<Severity, number>;
}>();

const total = computed(() => SEVERITIES.reduce((sum, s) => sum + (props.breakdown[s] ?? 0), 0));
</script>

<template>
    <div>
        <div class="flex h-2 w-full overflow-hidden rounded-full bg-muted" role="img" :aria-label="`${total} hallazgos por severidad`">
            <div
                v-for="s in SEVERITIES"
                :key="s"
                class="h-full transition-all duration-700"
                :style="{ width: total ? `${(breakdown[s] / total) * 100}%` : '0%', background: severityMeta[s].color }"
            />
        </div>
        <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
            <li v-for="s in SEVERITIES" :key="s" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <span class="size-2 rounded-sm" :style="{ background: severityMeta[s].color }" />
                {{ severityMeta[s].label }}
                <span class="font-mono text-foreground">{{ breakdown[s] ?? 0 }}</span>
            </li>
        </ul>
    </div>
</template>
