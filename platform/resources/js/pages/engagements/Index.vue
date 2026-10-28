<script setup lang="ts">
import PageHeader from '@/components/krit0s/PageHeader.vue';
import StatusDot from '@/components/krit0s/StatusDot.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { engagementStatusMeta, engagementTypeLabel, formatDate } from '@/lib/labels';
import type { BreadcrumbItem, Engagement, Option, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Plus, Search } from 'lucide-vue-next';
import { reactive, watch } from 'vue';

const props = defineProps<{
    engagements: Paginated<Engagement>;
    filters: { search?: string; status?: string };
    statuses: Option[];
    can: { create: boolean };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Proyectos', href: '/engagements' }];

const filters = reactive({ search: props.filters.search ?? '', status: props.filters.status ?? '' });

const apply = useDebounceFn(() => {
    router.get(route('engagements.index'), { search: filters.search || undefined, status: filters.status || undefined }, { preserveState: true, replace: true });
}, 250);

watch(filters, apply);
</script>

<template>
    <Head title="Proyectos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader
                eyebrow="Engagements"
                :title="can.create ? 'Proyectos' : 'Mis informes'"
                :description="can.create ? 'Cada proyecto agrupa alcance, equipo, hallazgos y versiones del informe.' : 'Informes de seguridad que tu proveedor ha publicado para ti.'"
            >
                <template #actions>
                    <Button v-if="can.create" as-child>
                        <Link :href="route('engagements.create')"><Plus class="size-4" />Nuevo proyecto</Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="filters.search" type="search" class="field pl-9" placeholder="Buscar por título o código…" aria-label="Buscar proyectos" />
                </div>
                <select v-model="filters.status" class="field sm:w-52" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>

            <div v-if="engagements.data.length" class="grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                <Link
                    v-for="e in engagements.data"
                    :key="e.id"
                    :href="route('engagements.show', e.id)"
                    class="panel group flex flex-col gap-4 p-5 transition-colors hover:border-primary/40"
                >
                    <div class="flex items-start justify-between gap-3">
                        <span class="font-mono text-xs text-primary">{{ e.code }}</span>
                        <StatusDot v-bind="engagementStatusMeta[e.status]" />
                    </div>
                    <div>
                        <h2 class="font-display text-lg font-semibold leading-snug group-hover:text-primary">{{ e.title }}</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ e.client?.name }} · {{ engagementTypeLabel[e.type] }}</p>
                    </div>
                    <div class="mt-auto flex items-center justify-between border-t pt-3 font-mono text-xs text-muted-foreground">
                        <span>{{ formatDate(e.starts_on) }} → {{ formatDate(e.ends_on) }}</span>
                        <span><span class="text-foreground">{{ e.findings_count }}</span> hallazgos</span>
                    </div>
                </Link>
            </div>

            <div v-else class="panel grid place-items-center gap-2 p-12 text-center">
                <p class="font-display text-lg">Sin resultados</p>
                <p class="text-sm text-muted-foreground">Prueba con otro filtro o crea un proyecto nuevo.</p>
            </div>

            <nav v-if="engagements.last_page > 1" class="flex flex-wrap gap-1" aria-label="Paginación">
                <template v-for="link in engagements.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="link.active ? 'border-primary/50 text-primary' : 'text-muted-foreground hover:text-foreground'"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
