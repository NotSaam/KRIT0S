<script setup lang="ts">
import FormField from '@/components/krit0s/FormField.vue';
import PageHeader from '@/components/krit0s/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { roleLabel } from '@/lib/labels';
import type { BreadcrumbItem, Engagement, Option, Role } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    engagement: (Engagement & { team: number[] }) | null;
    nextCode: string;
    clients: { id: number; name: string }[];
    staff: { id: number; name: string; role: Role }[];
    types: Option[];
    statuses: Option[];
}>();

const editing = computed(() => props.engagement !== null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proyectos', href: '/engagements' },
    editing.value
        ? { title: props.engagement!.code, href: `/engagements/${props.engagement!.id}` }
        : { title: 'Nuevo', href: '/engagements/create' },
];

const form = useForm({
    client_id: props.engagement?.client_id ?? props.clients[0]?.id ?? '',
    title: props.engagement?.title ?? '',
    type: props.engagement?.type ?? 'web',
    status: props.engagement?.status ?? 'planning',
    scope: props.engagement?.scope ?? '',
    methodology: props.engagement?.methodology ?? 'OWASP WSTG v4.2 · PTES',
    starts_on: props.engagement?.starts_on ?? new Date().toISOString().slice(0, 10),
    ends_on: props.engagement?.ends_on ?? '',
    executive_summary: props.engagement?.executive_summary ?? '',
    lead_id: props.engagement?.lead_id ?? props.staff[0]?.id ?? '',
    team: props.engagement?.team ?? ([] as number[]),
});

const submit = () => {
    if (editing.value) {
        form.put(route('engagements.update', props.engagement!.id));
    } else {
        form.post(route('engagements.store'));
    }
};

const toggleMember = (id: number) => {
    form.team = form.team.includes(id) ? form.team.filter((m) => m !== id) : [...form.team, id];
};
</script>

<template>
    <Head :title="editing ? `Editar ${nextCode}` : 'Nuevo proyecto'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-6" @submit.prevent="submit">
            <PageHeader :eyebrow="nextCode" :title="editing ? 'Editar proyecto' : 'Nuevo proyecto'" description="Alcance, ventana de pruebas y equipo asignado." />

            <section class="panel grid gap-5 p-5 md:grid-cols-2">
                <FormField label="Título" for="title" :error="form.errors.title" class="md:col-span-2">
                    <Input id="title" v-model="form.title" required placeholder="Pentest de banca online" />
                </FormField>

                <FormField label="Cliente" for="client_id" :error="form.errors.client_id">
                    <select id="client_id" v-model="form.client_id" class="field" required>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </FormField>

                <FormField label="Tipo" for="type" :error="form.errors.type">
                    <select id="type" v-model="form.type" class="field">
                        <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                </FormField>

                <FormField label="Inicio" for="starts_on" :error="form.errors.starts_on">
                    <Input id="starts_on" v-model="form.starts_on" type="date" required />
                </FormField>

                <FormField label="Fin" for="ends_on" :error="form.errors.ends_on">
                    <Input id="ends_on" v-model="form.ends_on" type="date" required />
                </FormField>

                <FormField label="Estado" for="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" class="field">
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </FormField>

                <FormField label="Metodología" for="methodology" :error="form.errors.methodology">
                    <Input id="methodology" v-model="form.methodology" />
                </FormField>

                <FormField label="Alcance" for="scope" :error="form.errors.scope" hint="Un activo por línea: URLs, rangos, apps o dominios." class="md:col-span-2">
                    <textarea id="scope" v-model="form.scope" rows="4" class="field font-mono text-xs" required />
                </FormField>
            </section>

            <section class="panel grid gap-5 p-5">
                <FormField label="Responsable" for="lead_id" :error="form.errors.lead_id" hint="El responsable firma el veredicto y publica el informe al cliente.">
                    <select id="lead_id" v-model="form.lead_id" class="field sm:max-w-sm">
                        <option v-for="u in staff" :key="u.id" :value="u.id">{{ u.name }} · {{ roleLabel[u.role] }}</option>
                    </select>
                </FormField>

                <fieldset>
                    <legend class="mb-2 text-sm font-medium">Equipo</legend>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="u in staff.filter((s) => s.id !== form.lead_id)"
                            :key="u.id"
                            type="button"
                            class="rounded-full border px-3 py-1 text-sm transition-colors"
                            :class="form.team.includes(u.id) ? 'border-primary/50 bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground'"
                            :aria-pressed="form.team.includes(u.id)"
                            @click="toggleMember(u.id)"
                        >
                            {{ u.name }}
                        </button>
                    </div>
                </fieldset>
            </section>

            <section class="panel p-5">
                <FormField label="Resumen ejecutivo" for="executive_summary" :error="form.errors.executive_summary" hint="Abre el informe PDF. Escríbelo para dirección, sin jerga.">
                    <textarea id="executive_summary" v-model="form.executive_summary" rows="6" class="field" />
                </FormField>
            </section>

            <div class="flex justify-end gap-2">
                <Button variant="ghost" as-child>
                    <Link :href="editing ? route('engagements.show', engagement!.id) : route('engagements.index')">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                    {{ editing ? 'Guardar cambios' : 'Crear proyecto' }}
                </Button>
            </div>
        </form>
    </AppLayout>
</template>
