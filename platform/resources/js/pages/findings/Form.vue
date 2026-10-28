<script setup lang="ts">
import CvssCalculator from '@/components/krit0s/CvssCalculator.vue';
import FormField from '@/components/krit0s/FormField.vue';
import PageHeader from '@/components/krit0s/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { DEFAULT_VECTOR } from '@/lib/cvss';
import type { BreadcrumbItem, Finding, FindingTemplate, Option } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Library, LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    engagement: { id: number; code: string; title: string };
    finding: (Partial<Finding> & { id?: number }) | null;
    statuses: Option[];
    templates: Pick<FindingTemplate, 'id' | 'title' | 'category' | 'cvss_vector'>[];
}>();

const editing = computed(() => Boolean(props.finding?.id));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proyectos', href: '/engagements' },
    { title: props.engagement.code, href: `/engagements/${props.engagement.id}` },
    { title: editing.value ? (props.finding!.reference ?? 'Editar') : 'Nuevo hallazgo', href: '#' },
];

const form = useForm({
    template_id: props.finding?.template_id ?? null,
    title: props.finding?.title ?? '',
    cvss_vector: props.finding?.cvss_vector ?? DEFAULT_VECTOR,
    status: props.finding?.status ?? 'open',
    affected_assets: props.finding?.affected_assets ?? '',
    description: props.finding?.description ?? '',
    proof_of_concept: props.finding?.proof_of_concept ?? '',
    impact: props.finding?.impact ?? '',
    remediation: props.finding?.remediation ?? '',
    references: props.finding?.references ?? '',
});

const groupedTemplates = computed(() => {
    const groups: Record<string, typeof props.templates> = {};
    for (const t of props.templates) (groups[t.category] ??= []).push(t);
    return groups;
});

// Starting from a template reloads the page with it pre-filled server-side.
const useTemplate = (event: Event) => {
    const id = (event.target as HTMLSelectElement).value;
    if (id) router.get(route('engagements.findings.create', props.engagement.id), { template: id });
};

const submit = () => {
    if (editing.value) {
        form.put(route('engagements.findings.update', [props.engagement.id, props.finding!.id!]));
    } else {
        form.post(route('engagements.findings.store', props.engagement.id));
    }
};
</script>

<template>
    <Head :title="editing ? 'Editar hallazgo' : 'Nuevo hallazgo'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6" @submit.prevent="submit">
            <PageHeader :eyebrow="`${engagement.code} · ${engagement.title}`" :title="editing ? `Editar ${finding?.reference}` : 'Nuevo hallazgo'" />

            <div v-if="!editing" class="panel flex flex-col gap-3 border-dashed p-4 sm:flex-row sm:items-center">
                <Library class="size-5 shrink-0 text-violet-400" />
                <div class="flex-1">
                    <p class="text-sm font-medium">Partir de una plantilla</p>
                    <p class="text-xs text-muted-foreground">Rellena descripción, impacto, remediación y vector CVSS desde la biblioteca.</p>
                </div>
                <select class="field sm:w-80" aria-label="Plantilla" :value="form.template_id ?? ''" @change="useTemplate">
                    <option value="">Sin plantilla</option>
                    <optgroup v-for="(items, category) in groupedTemplates" :key="category" :label="String(category)">
                        <option v-for="t in items" :key="t.id" :value="t.id">{{ t.title }}</option>
                    </optgroup>
                </select>
            </div>

            <section class="panel grid gap-5 p-5 md:grid-cols-[1fr_220px]">
                <FormField label="Título" for="title" :error="form.errors.title">
                    <Input id="title" v-model="form.title" required />
                </FormField>
                <FormField label="Estado" for="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" class="field">
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </FormField>
                <FormField label="Activos afectados" for="affected_assets" :error="form.errors.affected_assets" class="md:col-span-2">
                    <textarea id="affected_assets" v-model="form.affected_assets" rows="2" class="field font-mono text-xs" required />
                </FormField>
            </section>

            <section class="grid gap-2">
                <div class="flex items-baseline justify-between">
                    <h2 class="font-display font-semibold">Puntuación CVSS v3.1</h2>
                    <span class="text-xs text-muted-foreground">El servidor recalcula la nota al guardar.</span>
                </div>
                <CvssCalculator v-model="form.cvss_vector" />
                <p v-if="form.errors.cvss_vector" class="text-sm text-destructive">{{ form.errors.cvss_vector }}</p>
            </section>

            <section class="panel grid gap-5 p-5">
                <FormField label="Descripción" for="description" :error="form.errors.description">
                    <textarea id="description" v-model="form.description" rows="5" class="field" required />
                </FormField>
                <FormField label="Prueba de concepto" for="proof_of_concept" :error="form.errors.proof_of_concept" hint="Peticiones, comandos o pasos. Se muestra en monoespaciado en el informe.">
                    <textarea id="proof_of_concept" v-model="form.proof_of_concept" rows="5" class="field font-mono text-xs" spellcheck="false" />
                </FormField>
                <div class="grid gap-5 md:grid-cols-2">
                    <FormField label="Impacto" for="impact" :error="form.errors.impact">
                        <textarea id="impact" v-model="form.impact" rows="4" class="field" required />
                    </FormField>
                    <FormField label="Remediación" for="remediation" :error="form.errors.remediation">
                        <textarea id="remediation" v-model="form.remediation" rows="4" class="field" required />
                    </FormField>
                </div>
                <FormField label="Referencias" for="references" :error="form.errors.references">
                    <textarea id="references" v-model="form.references" rows="2" class="field font-mono text-xs" />
                </FormField>
            </section>

            <div class="flex justify-end gap-2">
                <Button variant="ghost" as-child>
                    <Link :href="route('engagements.show', engagement.id)">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    {{ editing ? 'Guardar' : 'Registrar hallazgo' }}
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
