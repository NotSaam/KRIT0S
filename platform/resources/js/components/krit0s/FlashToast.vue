<script setup lang="ts">
import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const page = usePage<SharedData>();
const message = ref<string | null>(null);
let timer: ReturnType<typeof setTimeout> | undefined;

watch(
    () => page.props.flash?.success,
    (value) => {
        if (!value) return;
        message.value = value;
        clearTimeout(timer);
        timer = setTimeout(() => (message.value = null), 3500);
    },
    { immediate: true },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        leave-active-class="transition duration-200 ease-in"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="message"
            role="status"
            class="fixed bottom-5 right-5 z-50 flex max-w-sm items-center gap-3 rounded-lg border border-primary/30 bg-card/95 px-4 py-3 text-sm shadow-lg shadow-primary/10 backdrop-blur"
        >
            <CheckCircle2 class="size-4 shrink-0 text-primary" />
            {{ message }}
        </div>
    </Transition>
</template>
