<script setup lang="ts">
import PageHeader from '@/components/krit0s/PageHeader.vue';
import SeverityBadge from '@/components/krit0s/SeverityBadge.vue';
import StatusDot from '@/components/krit0s/StatusDot.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { engagementStatusMeta, findingStatusMeta, formatDate, formatDateTime } from '@/lib/labels';
import type { ActivityLog, BreadcrumbItem, Engagement, EngagementStatus, Finding, SharedData, Severity } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowUpRight } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    engagements: number;
    active: number;
    findings: number;
    remediated: number;
    averageCvss: number;
    severity: { value: Severity; label: string; color: string; total: number }[];
    status: { value: EngagementStatus; label: string; total: number }[];
}

const props = defineProps<{
    stats: Stats;
    critical: Finding[];
    engagements: Engagement[];
    activity: ActivityLog[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Panel', href: '/dashboard' }];
const page = usePage<SharedData>();
const isClient = computed(() => page.props.auth.role === 'client');

const openTotal = computed(() => props.stats.severity.reduce((sum, s) => sum + s.total, 0));

// Donut geometry: one circle per severity, offset by the running total.
const RADIUS = 52;
const CIRC = 2 * Math.PI * RADIUS;
const segments = computed(() => {
    let offset = 0;
    return props.stats.severity
        .filter((s) => s.total > 0)
        .map((s) => {
            const length = (s.total / openTotal.value) * CIRC;
            const segment = { ...s, dash: `${length} ${CIRC - length}`, offset: -offset };
            offset += length;
            return segment;
        });
});

const remediationRate = computed(() => (props.stats.findings ? Math.round((props.stats.remediated / props.stats.findings) * 100) : 0));

const kpis = computed(() => [
    { label: isClient.value ? 'Informes publicados' : 'Proyectos', value: props.stats.engagements, hint: `${props.stats.active} en curso` },
    { label: 'Hallazgos totales', value: props.stats.findings, hint: `${openTotal.value} sin remediar` },
    { label: 'CVSS medio', value: props.stats.averageCvss.toFixed(1), hint: 'puntuación base' },
    { label: 'Remediación', value: `${remediationRate.value}%`, hint: `${props.stats.remediated} cerrados` },
]);
</script>

<template>
    <Head title="Panel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Sala de control"
                :title="`Hola, ${page.props.auth.user.name.split(' ')[0]}`"
                :description="isClient ? 'Estado de seguridad de tus activos según los informes entregados.' : 'Riesgo abierto en los proyectos que te tocan.'"
            />

            <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div v-for="kpi in kpis" :key="kpi.label" class="panel p-4">
                    <p class="hud-label">{{ kpi.label }}</p>
                    <p class="mt-2 font-display text-3xl font-semibold tabular-nums">{{ kpi.value }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ kpi.hint }}</p>
                </div>
            </section>

            <section class="grid gap-4 xl:grid-cols-[minmax(0,340px)_1fr]">
                <div class="panel hud-corners p-5">
                    <p class="hud-label">Riesgo abierto por severidad</p>
                    <div class="mt-4 flex items-center gap-6">
                        <svg viewBox="0 0 140 140" class="size-36 shrink-0 -rotate-90" role="img" :aria-label="`${openTotal} hallazgos abiertos`">
                            <circle cx="70" cy="70" :r="RADIUS" fill="none" class="stroke-muted" stroke-width="14" />
                            <circle
                                v-for="seg in segments"
                                :key="seg.value"
                                cx="70"
                                cy="70"
                                :r="RADIUS"
                                fill="none"
                                :stroke="seg.color"
                                stroke-width="14"
                                :stroke-dasharray="seg.dash"
                                :stroke-dashoffset="seg.offset"
                            />
                            <text x="70" y="70" class="rotate-90 fill-foreground font-display text-[26px] font-semibold" text-anchor="middle" dominant-baseline="central" transform-origin="70 70">
                                {{ openTotal }}
                            </text>
                        </svg>
                        <ul class="grid flex-1 gap-1.5 text-sm">
                            <li v-for="s in stats.severity" :key="s.value" class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2 text-muted-foreground">
                                    <span class="size-2 rounded-sm" :style="{ background: s.color }" />{{ s.label }}
                                </span>
                                <span class="font-mono tabular-nums">{{ s.total }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="panel p-5">
                    <div class="flex items-center justify-between">
                        <p class="hud-label">Lo más grave sin cerrar</p>
                        <Link :href="route('engagements.index')" class="text-xs text-primary hover:underline">Ver proyectos</Link>
                    </div>
                    <ul v-if="critical.length" class="mt-3 divide-y">
                        <li v-for="finding in critical" :key="finding.id">
                            <Link
                                :href="route('engagements.show', finding.engagement_id) + `#finding-${finding.id}`"
                                class="group flex items-center gap-3 py-2.5"
                            >
                                <SeverityBadge :severity="finding.severity" :score="finding.cvss_score" class="w-[7.5rem] justify-between" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm group-hover:text-primary">{{ finding.title }}</span>
                                    <span class="font-mono text-xs text-muted-foreground">{{ finding.engagement?.code }} · {{ finding.reference }}</span>
                                </span>
                                <StatusDot class="hidden sm:inline-flex" v-bind="findingStatusMeta[finding.status]" />
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="mt-6 text-sm text-muted-foreground">Nada abierto. Buen trabajo.</p>
                </div>
            </section>

            <section class="grid gap-4" :class="activity.length ? 'xl:grid-cols-[1fr_minmax(0,380px)]' : ''">
                <div class="panel overflow-hidden">
                    <p class="hud-label p-5 pb-3">{{ isClient ? 'Informes recientes' : 'Proyectos recientes' }}</p>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm">
                            <tbody>
                                <tr v-for="e in engagements" :key="e.id" class="border-t transition-colors hover:bg-muted/40">
                                    <td class="py-3 pl-5 pr-3">
                                        <Link :href="route('engagements.show', e.id)" class="font-medium hover:text-primary">{{ e.title }}</Link>
                                        <p class="font-mono text-xs text-muted-foreground">{{ e.code }} · {{ e.client?.name }}</p>
                                    </td>
                                    <td class="px-3"><StatusDot v-bind="engagementStatusMeta[e.status]" /></td>
                                    <td class="px-3 font-mono text-xs text-muted-foreground">{{ formatDate(e.starts_on) }}</td>
                                    <td class="px-3 text-right font-mono tabular-nums">{{ e.findings_count }}</td>
                                    <td class="pr-5 text-right">
                                        <Link :href="route('engagements.show', e.id)" class="inline-flex text-muted-foreground hover:text-primary" :aria-label="`Abrir ${e.code}`">
                                            <ArrowUpRight class="size-4" />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="activity.length" class="panel p-5">
                    <p class="hud-label">Actividad</p>
                    <ol class="mt-4 space-y-4 border-l pl-4">
                        <li v-for="log in activity" :key="log.id" class="relative">
                            <span class="absolute -left-[1.3rem] top-1.5 size-2 rounded-full bg-primary/70 ring-4 ring-background" />
                            <p class="text-sm">{{ log.description }}</p>
                            <p class="font-mono text-xs text-muted-foreground">{{ log.user?.name ?? 'sistema' }} · {{ formatDateTime(log.created_at) }}</p>
                        </li>
                    </ol>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
