<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{ title: string }>();
</script>

<template>
    <Head :title="title" />

    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-12">
        <section class="w-full max-w-md rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <Link href="/" class="mb-8 block text-center text-2xl font-bold tracking-tight text-slate-900">
                ISAE
            </Link>
            <slot />
        </section>
    </main>
</template>
