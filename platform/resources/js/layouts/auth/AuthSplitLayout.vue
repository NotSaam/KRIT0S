<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const lines = [
    { k: 'F-01', t: 'Inyección SQL en buscador', s: '9.8', c: '#f43f5e' },
    { k: 'F-02', t: 'IDOR en extractos', s: '7.1', c: '#fb923c' },
    { k: 'F-03', t: 'XSS reflejado', s: '6.1', c: '#facc15' },
    { k: 'F-04', t: 'Cabeceras ausentes', s: '3.1', c: '#38bdf8' },
];
</script>

<template>
    <div class="relative grid min-h-dvh lg:grid-cols-[1.1fr_1fr]">
        <aside class="relative hidden overflow-hidden border-r bg-[#06060a] p-10 text-[#e9e9f3] lg:flex lg:flex-col">
            <div class="grid-overlay pointer-events-none absolute inset-0 [mask-image:radial-gradient(ellipse_80%_60%_at_30%_20%,black_30%,transparent_75%)]" />
            <div class="pointer-events-none absolute -left-32 -top-32 size-[36rem] rounded-full bg-teal-500/20 blur-[120px]" />
            <div class="pointer-events-none absolute -bottom-40 right-0 size-[28rem] rounded-full bg-violet-600/15 blur-[120px]" />

            <div class="relative flex items-center gap-3">
                <AppLogoIcon class="size-9" />
                <span class="font-display text-lg font-semibold tracking-[0.18em]">KRIT<span class="font-mono text-teal-400">0</span>S</span>
            </div>

            <div class="relative mt-auto max-w-md">
                <p class="hud-label !text-teal-300/80">κριτής · el que juzga</p>
                <h2 class="mt-3 font-display text-4xl font-semibold leading-tight">Del hallazgo<br />al veredicto.</h2>
                <p class="mt-4 text-sm text-[#8a8aa0]">Gestiona proyectos de pentest, puntúa con CVSS 3.1 y entrega informes PDF versionados a tu cliente.</p>

                <div class="hud-corners mt-8 rounded-xl border border-white/10 bg-white/[0.03] p-4 font-mono text-xs backdrop-blur">
                    <div v-for="line in lines" :key="line.k" class="flex items-center gap-3 border-b border-white/5 py-2 last:border-0">
                        <span class="text-[#54546a]">{{ line.k }}</span>
                        <span class="flex-1 truncate text-[#c9c9d6]">{{ line.t }}</span>
                        <span class="rounded px-1.5 py-0.5 text-[0.65rem] font-semibold text-[#06060a]" :style="{ background: line.c }">{{ line.s }}</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-sm">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <AppLogoIcon class="size-8" />
                    <span class="font-display font-semibold tracking-[0.18em]">KRIT<span class="font-mono text-primary">0</span>S</span>
                </div>
                <div class="mb-6 space-y-1.5">
                    <h1 v-if="title" class="font-display text-2xl font-semibold tracking-tight">{{ title }}</h1>
                    <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                </div>
                <slot />
            </div>
        </main>
    </div>
</template>
