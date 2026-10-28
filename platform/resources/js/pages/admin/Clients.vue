<script setup lang="ts">
import FormField from '@/components/krit0s/FormField.vue';
import PageHeader from '@/components/krit0s/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Client } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';

defineProps<{
    clients: Client[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Clientes', href: '/admin/clients' }];

const form = useForm({ name: '', sector: '', contact_name: '', contact_email: '' });

const submit = () => form.post(route('admin.clients.store'), { preserveScroll: true, onSuccess: () => form.reset() });

const destroy = (client: Client) => {
    if (confirm(`Eliminar «${client.name}» borra también sus ${client.engagements_count} proyectos. ¿Continuar?`)) {
        router.delete(route('admin.clients.destroy', client.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Administración" title="Clientes" description="Organizaciones auditadas. Sus usuarios solo ven los informes publicados." />

            <form class="panel grid gap-4 p-5 md:grid-cols-[1fr_1fr_1fr_1fr_auto] md:items-end" @submit.prevent="submit">
                <FormField label="Nombre" for="name" :error="form.errors.name"><Input id="name" v-model="form.name" required /></FormField>
                <FormField label="Sector" for="sector" :error="form.errors.sector"><Input id="sector" v-model="form.sector" /></FormField>
                <FormField label="Contacto" for="contact_name" :error="form.errors.contact_name"><Input id="contact_name" v-model="form.contact_name" /></FormField>
                <FormField label="Email" for="contact_email" :error="form.errors.contact_email"><Input id="contact_email" v-model="form.contact_email" type="email" /></FormField>
                <Button type="submit" :disabled="form.processing"><Plus class="size-4" />Añadir</Button>
            </form>

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="client in clients" :key="client.id" class="panel flex flex-col gap-3 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-lg font-semibold">{{ client.name }}</h2>
                            <p class="text-sm text-muted-foreground">{{ client.sector ?? 'Sin sector' }}</p>
                        </div>
                        <Button size="icon" variant="ghost" class="text-destructive" :aria-label="`Eliminar ${client.name}`" @click="destroy(client)">
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                    <p class="text-sm">{{ client.contact_name }} <span class="font-mono text-xs text-muted-foreground">{{ client.contact_email }}</span></p>
                    <div class="mt-auto flex gap-4 border-t pt-3 font-mono text-xs text-muted-foreground">
                        <span><span class="text-foreground">{{ client.engagements_count }}</span> proyectos</span>
                        <span><span class="text-foreground">{{ client.users_count }}</span> usuarios</span>
                    </div>
                </article>
            </div>
        </div>
    </AppLayout>
</template>
