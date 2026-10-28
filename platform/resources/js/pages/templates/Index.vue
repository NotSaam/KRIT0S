<script setup lang="ts">
import PageHeader from '@/components/krit0s/PageHeader.vue';
import SeverityBadge from '@/components/krit0s/SeverityBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, FindingTemplate } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    templates: FindingTemplate[];
    filters: { search: string };
    can: { delete: boolean };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Plantillas', href: '/templates' }];
const search = ref(props.filters.search);

watch(
    search,
    useDebounceFn((value: string) => router.get(route('templates.index'), { search: value || undefined }, { preserveState: true, replace: true }), 250),
);

const destroy = (t: FindingTemplate) => {
    if (confirm(`¿Eliminar la plantilla «${t.title}»? Los hallazgos creados con ella se conservan.`)) {
        router.delete(route('templates.destroy', t.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Plantillas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Biblioteca" title="Plantillas de hallazgos" description="Redacta una vez, reutiliza en cada proyecto. Mapeadas a OWASP Top 10 y MITRE ATT&CK.">
                <template #actions>
                    <Button as-child>
                        <Link :href="route('templates.create')"><Plus class="size-4" />Nueva plantilla</Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="relative">
                <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="search" type="search" class="field pl-9" placeholder="Buscar por título o categoría…" aria-label="Buscar plantillas" />
            </div>

            <div class="panel overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="hud-label px-5 py-3 font-normal">Plantilla</th>
                            <th class="hud-label px-3 py-3 font-normal">Severidad</th>
                            <th class="hud-label px-3 py-3 font-normal">Usos</th>
                            <th class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in templates" :key="t.id" class="border-b last:border-0 hover:bg-muted/40">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ t.title }}</p>
                                <p class="font-mono text-xs text-muted-foreground">{{ t.category }}</p>
                            </td>
                            <td class="px-3"><SeverityBadge :severity="t.severity" :score="t.cvss_score" /></td>
                            <td class="px-3 font-mono tabular-nums text-muted-foreground">{{ t.findings_count }}</td>
                            <td class="px-5 text-right">
                                <div class="inline-flex gap-1">
                                    <Button size="icon" variant="ghost" as-child>
                                        <Link :href="route('templates.edit', t.id)" :aria-label="`Editar ${t.title}`"><Pencil class="size-4" /></Link>
                                    </Button>
                                    <Button v-if="can.delete" size="icon" variant="ghost" class="text-destructive" :aria-label="`Eliminar ${t.title}`" @click="destroy(t)">
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
