<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import type { SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const page = usePage<SharedData>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// Fictitious accounts seeded by DatabaseSeeder (APP_DEMO=true only).
const demoAccounts = [
    { role: 'Admin', email: 'admin@krit0s.test' },
    { role: 'Pentester', email: 'ana@krit0s.test' },
    { role: 'Cliente', email: 'cliente@aurora.test' },
];

const useDemo = (email: string) => {
    form.email = email;
    form.password = 'krit0s-demo';
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Accede a KRIT0S" description="Introduce tus credenciales para continuar">
        <Head title="Acceso" />

        <div v-if="status" class="mb-4 text-sm font-medium text-primary">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="tu@empresa.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Contraseña</Label>
                        <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">¿La has olvidado?</TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="••••••••"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" v-model:checked="form.remember" :tabindex="3" />
                    <span>Recordarme</span>
                </Label>

                <Button type="submit" class="mt-2 w-full" tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Entrar
                </Button>
            </div>

            <p class="text-center text-xs text-muted-foreground">Sin registro público: las cuentas las crea un administrador.</p>
        </form>

        <div v-if="page.props.demo" class="mt-8 rounded-lg border border-dashed border-primary/30 p-4">
            <p class="hud-label mb-3">Demo pública · datos ficticios</p>
            <div class="grid gap-2">
                <button
                    v-for="account in demoAccounts"
                    :key="account.email"
                    type="button"
                    class="flex items-center justify-between rounded-md border px-3 py-2 text-left text-sm transition-colors hover:border-primary/40 hover:bg-primary/5"
                    @click="useDemo(account.email)"
                >
                    <span class="font-medium">{{ account.role }}</span>
                    <span class="font-mono text-xs text-muted-foreground">{{ account.email }}</span>
                </button>
            </div>
            <p class="mt-3 font-mono text-[0.7rem] text-muted-foreground">contraseña: krit0s-demo</p>
        </div>
    </AuthBase>
</template>
