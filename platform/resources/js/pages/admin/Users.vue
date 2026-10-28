<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import FormField from '@/components/krit0s/FormField.vue';
import PageHeader from '@/components/krit0s/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Option, Role, SharedData, User } from '@/types';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { Trash2, UserPlus } from 'lucide-vue-next';

defineProps<{
    users: User[];
    roles: Option[];
    clients: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Usuarios', href: '/admin/users' }];
const page = usePage<SharedData>();

const form = useForm({ name: '', email: '', password: '', role: 'pentester' as Role, client_id: null as number | null });

const submit = () => form.post(route('admin.users.store'), { preserveScroll: true, onSuccess: () => form.reset() });

const changeRole = (user: User, role: string, clientId: number | null = user.client_id) =>
    router.put(route('admin.users.update', user.id), { role, client_id: role === 'client' ? clientId : null }, { preserveScroll: true });

const destroy = (user: User) => {
    if (confirm(`¿Eliminar a ${user.email}?`)) router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <PageHeader eyebrow="Administración" title="Usuarios y roles" description="RBAC con tres roles: administrador, pentester y cliente (solo lectura de sus informes)." />

            <form class="panel grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_160px_180px_auto] xl:items-end" @submit.prevent="submit">
                <FormField label="Nombre" for="u_name" :error="form.errors.name"><Input id="u_name" v-model="form.name" required /></FormField>
                <FormField label="Email" for="u_email" :error="form.errors.email"><Input id="u_email" v-model="form.email" type="email" required /></FormField>
                <FormField label="Contraseña inicial" for="u_password" :error="form.errors.password"><Input id="u_password" v-model="form.password" type="password" autocomplete="new-password" required /></FormField>
                <FormField label="Rol" for="u_role" :error="form.errors.role">
                    <select id="u_role" v-model="form.role" class="field">
                        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </FormField>
                <FormField label="Cliente" for="u_client" :error="form.errors.client_id">
                    <select id="u_client" v-model="form.client_id" class="field" :disabled="form.role !== 'client'">
                        <option :value="null">—</option>
                        <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </FormField>
                <Button type="submit" :disabled="form.processing"><UserPlus class="size-4" />Crear</Button>
            </form>

            <InputError :message="(page.props.errors as Record<string, string>)?.role" />

            <div class="panel overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="hud-label px-5 py-3 font-normal">Usuario</th>
                            <th class="hud-label px-3 py-3 font-normal">Rol</th>
                            <th class="hud-label px-3 py-3 font-normal">Cliente</th>
                            <th class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-b last:border-0 hover:bg-muted/40">
                            <td class="px-5 py-3">
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="font-mono text-xs text-muted-foreground">{{ user.email }}</p>
                            </td>
                            <td class="px-3">
                                <select
                                    class="field h-8 w-40 py-0 text-xs"
                                    :value="user.role"
                                    :aria-label="`Rol de ${user.name}`"
                                    @change="changeRole(user, ($event.target as HTMLSelectElement).value, user.client_id ?? clients[0]?.id ?? null)"
                                >
                                    <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                                </select>
                            </td>
                            <td class="px-3">
                                <select
                                    v-if="user.role === 'client'"
                                    class="field h-8 w-44 py-0 text-xs"
                                    :value="user.client_id ?? ''"
                                    :aria-label="`Cliente de ${user.name}`"
                                    @change="changeRole(user, 'client', Number(($event.target as HTMLSelectElement).value))"
                                >
                                    <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <span v-else class="text-muted-foreground">—</span>
                            </td>
                            <td class="px-5 text-right">
                                <Button
                                    v-if="user.id !== page.props.auth.user.id"
                                    size="icon"
                                    variant="ghost"
                                    class="text-destructive"
                                    :aria-label="`Eliminar ${user.email}`"
                                    @click="destroy(user)"
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
