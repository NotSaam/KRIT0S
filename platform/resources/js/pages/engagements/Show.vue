<script setup lang="ts">
import PageHeader from '@/components/krit0s/PageHeader.vue';
import SeverityBadge from '@/components/krit0s/SeverityBadge.vue';
import SeverityBar from '@/components/krit0s/SeverityBar.vue';
import StatusDot from '@/components/krit0s/StatusDot.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { engagementStatusMeta, engagementTypeLabel, findingStatusMeta, formatDate, formatDateTime } from '@/lib/labels';
import type { BreadcrumbItem, Engagement, Finding, Severity } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ChevronDown, Download, FileText, Pencil, Plus, Send, Trash2, Undo2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    engagement: Engagement;
    findings: Finding[];
    breakdown: Record<Severity, number>;
    can: { update: boolean; delete: boolean; publish: boolean; generateReport: boolean };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proyectos', href: '/engagements' },
    { title: props.engagement.code, href: `/engagements/${props.engagement.id}` },
];

const open = ref<number | null>(Number(window.location.hash.replace('#finding-', '')) || null);
const toggle = (id: number) => (open.value = open.value === id ? null : id);

const reports = computed(() => props.engagement.reports ?? []);
const pending = computed(() => reports.value.some((r) => r.status === 'queued'));

// While a PDF is rendering in the queue, refresh just the engagement prop.
let poll: ReturnType<typeof setInterval> | undefined;
watch(
    pending,
    (isPending) => {
        clearInterval(poll);
        if (isPending) poll = setInterval(() => router.reload({ only: ['engagement'] }), 2000);
    },
    { immediate: true },
);
onBeforeUnmount(() => clearInterval(poll));

const generating = ref(false);
const generate = () =>
    router.post(route('engagements.reports.store', props.engagement.id), {}, {
        preserveScroll: true,
        onStart: () => (generating.value = true),
        onFinish: () => (generating.value = false),
    });

const publish = () => {
    const message = props.engagement.published_at
        ? '¿Retirar el informe del portal del cliente?'
        : 'El cliente podrá ver el proyecto y descargar sus informes. ¿Publicar?';
    if (confirm(message)) router.post(route('engagements.publish', props.engagement.id), {}, { preserveScroll: true });
};

const destroyEngagement = () => {
    if (confirm(`Eliminar ${props.engagement.code} y todos sus hallazgos. Esta acción no se puede deshacer.`)) {
        router.delete(route('engagements.destroy', props.engagement.id));
    }
};

const destroyFinding = (finding: Finding) => {
    if (confirm(`¿Eliminar ${finding.reference}?`)) {
        router.delete(route('engagements.findings.destroy', [props.engagement.id, finding.id]), { preserveScroll: true });
    }
};

const kb = (bytes: number | null) => (bytes ? `${Math.round(bytes / 1024)} KB` : '');
</script>

<template>
    <Head :title="engagement.code" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader :eyebrow="`${engagement.code} · ${engagementTypeLabel[engagement.type]}`" :title="engagement.title">
                <template #actions>
                    <Button v-if="can.update" variant="outline" size="sm" as-child>
                        <Link :href="route('engagements.edit', engagement.id)"><Pencil class="size-4" />Editar</Link>
                    </Button>
                    <Button v-if="can.publish" :variant="engagement.published_at ? 'outline' : 'default'" size="sm" @click="publish">
                        <component :is="engagement.published_at ? Undo2 : Send" class="size-4" />
                        {{ engagement.published_at ? 'Retirar del portal' : 'Publicar al cliente' }}
                    </Button>
                    <Button v-if="can.delete" variant="ghost" size="sm" class="text-destructive" aria-label="Eliminar proyecto" @click="destroyEngagement">
                        <Trash2 class="size-4" />
                    </Button>
                </template>
            </PageHeader>

            <section class="grid gap-4 lg:grid-cols-3">
                <div class="panel p-5 lg:col-span-2">
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                        <span><span class="hud-label mr-2">Cliente</span>{{ engagement.client?.name }}</span>
                        <StatusDot v-bind="engagementStatusMeta[engagement.status]" />
                        <span class="font-mono text-xs text-muted-foreground">{{ formatDate(engagement.starts_on) }} → {{ formatDate(engagement.ends_on) }}</span>
                        <span v-if="engagement.published_at" class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs text-primary">
                            Publicado {{ formatDate(engagement.published_at) }}
                        </span>
                    </div>
                    <div class="mt-5"><SeverityBar :breakdown="breakdown" /></div>
                    <div v-if="engagement.executive_summary" class="mt-5 border-t pt-4">
                        <p class="hud-label mb-2">Resumen ejecutivo</p>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ engagement.executive_summary }}</p>
                    </div>
                </div>

                <div class="panel grid content-start gap-4 p-5">
                    <div>
                        <p class="hud-label mb-2">Alcance</p>
                        <pre class="whitespace-pre-wrap break-all font-mono text-xs leading-relaxed text-muted-foreground">{{ engagement.scope }}</pre>
                    </div>
                    <div>
                        <p class="hud-label mb-2">Equipo</p>
                        <ul class="space-y-1 text-sm">
                            <li v-if="engagement.lead">{{ engagement.lead.name }} <span class="text-xs text-primary">· responsable</span></li>
                            <li v-for="m in engagement.team" :key="m.id" class="text-muted-foreground">{{ m.name }}</li>
                        </ul>
                    </div>
                    <p v-if="engagement.methodology" class="text-xs text-muted-foreground">{{ engagement.methodology }} · CVSS v3.1</p>
                </div>
            </section>

            <!-- Findings -->
            <section class="panel overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b p-4 sm:px-5">
                    <h2 class="font-display text-lg font-semibold">Hallazgos <span class="font-mono text-sm text-muted-foreground">{{ findings.length }}</span></h2>
                    <Button v-if="can.update" size="sm" as-child>
                        <Link :href="route('engagements.findings.create', engagement.id)"><Plus class="size-4" />Añadir hallazgo</Link>
                    </Button>
                </div>

                <ul v-if="findings.length" class="divide-y">
                    <li v-for="finding in findings" :id="`finding-${finding.id}`" :key="finding.id" class="scroll-mt-20">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-muted/40 sm:px-5"
                            :aria-expanded="open === finding.id"
                            @click="toggle(finding.id)"
                        >
                            <span class="w-10 shrink-0 font-mono text-xs text-muted-foreground">{{ finding.reference }}</span>
                            <SeverityBadge :severity="finding.severity" :score="finding.cvss_score" class="w-[7.5rem] shrink-0 justify-between" />
                            <span class="min-w-0 flex-1 truncate text-sm font-medium">{{ finding.title }}</span>
                            <StatusDot class="hidden md:inline-flex" v-bind="findingStatusMeta[finding.status]" />
                            <ChevronDown class="size-4 shrink-0 text-muted-foreground transition-transform" :class="{ 'rotate-180': open === finding.id }" />
                        </button>

                        <div v-if="open === finding.id" class="grid gap-5 border-t bg-muted/20 px-4 py-5 sm:px-5 lg:grid-cols-2">
                            <div class="grid content-start gap-4">
                                <div>
                                    <p class="hud-label mb-1">Vector</p>
                                    <code class="break-all font-mono text-xs text-primary">{{ finding.cvss_vector }}</code>
                                </div>
                                <div>
                                    <p class="hud-label mb-1">Activos afectados</p>
                                    <p class="whitespace-pre-line break-all font-mono text-xs">{{ finding.affected_assets }}</p>
                                </div>
                                <div>
                                    <p class="hud-label mb-1">Descripción</p>
                                    <p class="whitespace-pre-line text-sm text-muted-foreground">{{ finding.description }}</p>
                                </div>
                                <div>
                                    <p class="hud-label mb-1">Impacto</p>
                                    <p class="whitespace-pre-line text-sm text-muted-foreground">{{ finding.impact }}</p>
                                </div>
                            </div>
                            <div class="grid min-w-0 content-start gap-4">
                                <div v-if="finding.proof_of_concept">
                                    <p class="hud-label mb-1">Prueba de concepto</p>
                                    <pre class="overflow-x-auto rounded-md border bg-[#06060a] p-3 font-mono text-xs leading-relaxed text-teal-100">{{ finding.proof_of_concept }}</pre>
                                </div>
                                <div>
                                    <p class="hud-label mb-1">Remediación</p>
                                    <p class="whitespace-pre-line text-sm text-muted-foreground">{{ finding.remediation }}</p>
                                </div>
                                <div v-if="finding.references">
                                    <p class="hud-label mb-1">Referencias</p>
                                    <p class="whitespace-pre-line break-all font-mono text-xs text-muted-foreground">{{ finding.references }}</p>
                                </div>
                                <div v-if="can.update" class="flex gap-2 pt-1">
                                    <Button size="sm" variant="outline" as-child>
                                        <Link :href="route('engagements.findings.edit', [engagement.id, finding.id])"><Pencil class="size-4" />Editar</Link>
                                    </Button>
                                    <Button size="sm" variant="ghost" class="text-destructive" @click="destroyFinding(finding)"><Trash2 class="size-4" />Eliminar</Button>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <p v-else class="p-8 text-center text-sm text-muted-foreground">Todavía no hay hallazgos registrados.</p>
            </section>

            <!-- Reports -->
            <section class="panel p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold">Informes</h2>
                        <p class="text-sm text-muted-foreground">Cada versión es inmutable y lleva su huella SHA-256.</p>
                    </div>
                    <Button v-if="can.generateReport" size="sm" variant="outline" :disabled="generating || pending" @click="generate">
                        <FileText class="size-4" />
                        {{ pending ? 'Generando…' : 'Generar nueva versión' }}
                    </Button>
                </div>

                <ul v-if="reports.length" class="mt-4 divide-y rounded-lg border">
                    <li v-for="r in reports" :key="r.id" class="flex flex-wrap items-center gap-x-4 gap-y-1 px-4 py-3">
                        <span class="font-display font-semibold">v{{ r.version }}</span>
                        <span class="text-sm text-muted-foreground">{{ formatDateTime(r.created_at) }} · {{ r.author?.name ?? '—' }}</span>
                        <span class="text-xs text-muted-foreground">{{ r.findings_count }} hallazgos {{ kb(r.size) }}</span>
                        <code v-if="r.sha256" class="hidden truncate font-mono text-[0.65rem] text-muted-foreground xl:block xl:max-w-[16rem]" :title="r.sha256">sha256:{{ r.sha256 }}</code>
                        <span class="ml-auto">
                            <a
                                v-if="r.status === 'ready'"
                                :href="route('engagements.reports.download', [engagement.id, r.id])"
                                class="inline-flex items-center gap-1.5 text-sm text-primary hover:underline"
                            >
                                <Download class="size-4" />PDF
                            </a>
                            <span v-else-if="r.status === 'queued'" class="animate-pulse font-mono text-xs text-violet-400">en cola…</span>
                            <span v-else class="font-mono text-xs text-destructive">error</span>
                        </span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-muted-foreground">Aún no se ha generado ningún informe.</p>
            </section>
        </div>
    </AppLayout>
</template>
