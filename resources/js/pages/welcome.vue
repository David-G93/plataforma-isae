<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <Head title="Plataforma escolar" />

    <main class="min-h-screen bg-slate-950 px-6 py-12 text-slate-100">
        <div class="mx-auto flex max-w-6xl items-center justify-between">
            <span class="text-xl font-bold tracking-tight">ISAE</span>
            <Link :href="page.props.auth.user ? '/dashboard' : '/login'" class="rounded-md bg-amber-400 px-4 py-2 font-medium text-slate-950">
                {{ page.props.auth.user ? 'Ir al panel' : 'Ingresar' }}
            </Link>
        </div>
        <section class="mx-auto max-w-6xl py-28">
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Plataforma escolar</p>
            <h1 class="max-w-3xl text-5xl font-bold tracking-tight sm:text-6xl">Una base simple para gestionar ISAE.</h1>
        </section>
    </main>
</template>
