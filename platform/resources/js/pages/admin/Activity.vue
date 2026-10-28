<script setup lang="ts">
import PageHeader from '@/components/krit0s/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDateTime } from '@/lib/labels';
import type { ActivityLog, BreadcrumbItem, Paginated } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps<{
    logs: Paginated<ActivityLog>;
    filters: { action: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Auditoría', href: '/admin/activity' }];

const scopes = [
    { value: '', label: 'Todo' },
    { value: 'engagement', label: 'Proyectos' },
    { value: 'finding', label: 'Hallazgos' },
    { value: 'report', label: 'Informes' },
    { value: 'user', label: 'Usuarios' },
    { value: 'client', label: 'Clientes' },
];

const filter = (action: string) => router.get(route('admin.activity.index'), { action: action || undefined }, { preserveState: true });

const tone = (action: string) =>
    action.endsWith('deleted') ? 'text-rose-400' : action.startsWith('report') ? 'text-violet-400' : action.endsWith('published') ? 'text-sky-400' : 'text-primary';
</script>

<template>
    <Head title="Auditoría" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Administración" title="Registro de auditoría" description="Traza append-only de quién hizo qué, cuándo y desde dónde." />

            <div class="flex flex-wrap gap-1.5">
                <button
                    v-for="s in scopes"
                    :key="s.value"
                    type="button"
                    class="rounded-full border px-3 py-1 text-sm transition-colors"
                    :class="props.filters.action === s.value ? 'border-primary/50 bg-primary/10 text-primary' : 'text-muted-foreground hover:text-foreground'"
                    @click="filter(s.value)"
                >
                    {{ s.label }}
                </button>
            </div>

            <div class="panel overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="hud-label px-5 py-3 font-normal">Cuándo</th>
                            <th class="hud-label px-3 py-3 font-normal">Acción</th>
                            <th class="hud-label px-3 py-3 font-normal">Detalle</th>
                            <th class="hud-label px-3 py-3 font-normal">Usuario</th>
                            <th class="hud-label px-5 py-3 font-normal">IP</th>
                        </tr>
                    </thead>
                    <tbody class="font-mono text-xs">
                        <tr v-for="log in logs.data" :key="log.id" class="border-b last:border-0 hover:bg-muted/40">
                            <td class="whitespace-nowrap px-5 py-2.5 text-muted-foreground">{{ formatDateTime(log.created_at) }}</td>
                            <td class="whitespace-nowrap px-3" :class="tone(log.action)">{{ log.action }}</td>
                            <td class="px-3 font-sans text-sm">{{ log.description }}</td>
                            <td class="whitespace-nowrap px-3 text-muted-foreground">{{ log.user?.email ?? 'sistema' }}</td>
                            <td class="px-5 text-muted-foreground">{{ log.ip_address ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="logs.last_page > 1" class="flex flex-wrap gap-1" aria-label="Paginación">
                <template v-for="link in logs.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-md border px-3 py-1.5 text-sm"
                        :class="link.active ? 'border-primary/50 text-primary' : 'text-muted-foreground hover:text-foreground'"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </div>
    </AppLayout>
</template>
