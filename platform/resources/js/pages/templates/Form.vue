<script setup lang="ts">
import CvssCalculator from '@/components/krit0s/CvssCalculator.vue';
import FormField from '@/components/krit0s/FormField.vue';
import PageHeader from '@/components/krit0s/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { DEFAULT_VECTOR } from '@/lib/cvss';
import type { BreadcrumbItem, FindingTemplate } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const props = defineProps<{
    template: FindingTemplate | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Plantillas', href: '/templates' },
    { title: props.template ? 'Editar' : 'Nueva', href: '#' },
];

const form = useForm({
    title: props.template?.title ?? '',
    category: props.template?.category ?? '',
    cvss_vector: props.template?.cvss_vector ?? DEFAULT_VECTOR,
    description: props.template?.description ?? '',
    impact: props.template?.impact ?? '',
    remediation: props.template?.remediation ?? '',
    references: props.template?.references ?? '',
});

const submit = () => (props.template ? form.put(route('templates.update', props.template.id)) : form.post(route('templates.store')));
</script>

<template>
    <Head :title="template ? 'Editar plantilla' : 'Nueva plantilla'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 md:p-6" @submit.prevent="submit">
            <PageHeader eyebrow="Biblioteca" :title="template ? 'Editar plantilla' : 'Nueva plantilla'" />

            <section class="panel grid gap-5 p-5 md:grid-cols-2">
                <FormField label="Título" for="title" :error="form.errors.title">
                    <Input id="title" v-model="form.title" required />
                </FormField>
                <FormField label="Categoría" for="category" :error="form.errors.category" hint="Ej.: A01:2021 Broken Access Control">
                    <Input id="category" v-model="form.category" required />
                </FormField>
            </section>

            <section class="grid gap-2">
                <h2 class="font-display font-semibold">Vector CVSS por defecto</h2>
                <CvssCalculator v-model="form.cvss_vector" />
                <p v-if="form.errors.cvss_vector" class="text-sm text-destructive">{{ form.errors.cvss_vector }}</p>
            </section>

            <section class="panel grid gap-5 p-5">
                <FormField label="Descripción" for="description" :error="form.errors.description">
                    <textarea id="description" v-model="form.description" rows="5" class="field" required />
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
                <Button variant="ghost" as-child><Link :href="route('templates.index')">Cancelar</Link></Button>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    Guardar plantilla
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
