<script setup lang="ts">
import SeverityBadge from '@/components/krit0s/SeverityBadge.vue';
import { baseScore, DEFAULT_VECTOR, METRIC_ORDER, METRICS, parseVector, severityOf, toVector, type MetricKey, type Metrics } from '@/lib/cvss';
import { severityMeta } from '@/lib/labels';
import { computed, ref, watch } from 'vue';

const vector = defineModel<string>({ required: true });

const metrics = ref<Metrics>(parseVector(vector.value) ?? (parseVector(DEFAULT_VECTOR) as Metrics));
const raw = ref(vector.value || DEFAULT_VECTOR);

const score = computed(() => baseScore(metrics.value));
const severity = computed(() => severityOf(score.value));
const rawInvalid = computed(() => parseVector(raw.value) === null);

function pick(key: MetricKey, value: string) {
    metrics.value = { ...metrics.value, [key]: value };
}

// Buttons → vector string.
watch(metrics, (m) => {
    const next = toVector(m);
    raw.value = next;
    vector.value = next;
});

// Pasted vector → buttons (only when it parses).
function onRawInput() {
    const parsed = parseVector(raw.value);
    if (parsed) metrics.value = parsed;
}

// External changes (e.g. picking a template) → buttons.
watch(vector, (v) => {
    if (v !== toVector(metrics.value)) {
        const parsed = parseVector(v);
        if (parsed) metrics.value = parsed;
    }
});
</script>

<template>
    <div class="panel hud-corners overflow-hidden">
        <div class="grid gap-0 lg:grid-cols-[1fr_200px]">
            <div class="grid gap-3 p-4 sm:grid-cols-2">
                <fieldset v-for="key in METRIC_ORDER" :key="key" class="min-w-0">
                    <legend class="hud-label mb-1.5">{{ key }} · {{ METRICS[key].label }}</legend>
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="option in METRICS[key].options"
                            :key="option.value"
                            type="button"
                            class="rounded-md border px-2.5 py-1 text-xs transition-colors"
                            :class="
                                metrics[key] === option.value
                                    ? 'border-primary/60 bg-primary/15 text-primary'
                                    : 'border-border text-muted-foreground hover:border-primary/30 hover:text-foreground'
                            "
                            :aria-pressed="metrics[key] === option.value"
                            @click="pick(key, option.value)"
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </fieldset>
            </div>

            <div class="flex flex-col items-center justify-center gap-2 border-t bg-muted/40 p-5 lg:border-l lg:border-t-0">
                <span class="hud-label">Puntuación base</span>
                <span class="font-display text-5xl font-semibold tabular-nums" :style="{ color: severityMeta[severity].color }">
                    {{ score.toFixed(1) }}
                </span>
                <SeverityBadge :severity="severity" />
            </div>
        </div>

        <div class="border-t p-3">
            <label for="cvss_raw" class="sr-only">Vector CVSS</label>
            <input
                id="cvss_raw"
                v-model="raw"
                class="field font-mono text-xs"
                :class="{ 'border-destructive': rawInvalid }"
                spellcheck="false"
                @input="onRawInput"
            />
            <p v-if="rawInvalid" class="mt-1 text-xs text-destructive">Vector incompleto o no válido — pega uno CVSS:3.1 completo.</p>
        </div>
    </div>
</template>
