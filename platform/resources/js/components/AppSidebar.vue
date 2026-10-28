<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { roleLabel } from '@/lib/labels';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, FolderKanban, LayoutGrid, Library, ScrollText, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();
const role = computed(() => page.props.auth.role);

const workNavItems = computed<NavItem[]>(() => [
    { title: 'Panel', href: '/dashboard', icon: LayoutGrid },
    { title: role.value === 'client' ? 'Mis informes' : 'Proyectos', href: '/engagements', icon: FolderKanban },
    ...(role.value !== 'client' ? [{ title: 'Plantillas', href: '/templates', icon: Library }] : []),
]);

const adminNavItems: NavItem[] = [
    { title: 'Clientes', href: '/admin/clients', icon: Building2 },
    { title: 'Usuarios', href: '/admin/users', icon: Users },
    { title: 'Auditoría', href: '/admin/activity', icon: ScrollText },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="workNavItems" label="Operación" />
            <NavMain v-if="role === 'admin'" :items="adminNavItems" label="Administración" class="mt-2" />
        </SidebarContent>

        <SidebarFooter>
            <div class="px-2 group-data-[collapsible=icon]:hidden">
                <span class="hud-label">Sesión · {{ roleLabel[role] }}</span>
                <p v-if="page.props.auth.user.client" class="mt-0.5 truncate text-xs text-muted-foreground">{{ page.props.auth.user.client.name }}</p>
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
