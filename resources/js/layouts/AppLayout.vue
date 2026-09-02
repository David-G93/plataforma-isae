<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="border-b border-slate-200 bg-white">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link href="/dashboard" class="text-xl font-bold tracking-tight text-slate-900">ISAE</Link>
                <div class="flex items-center gap-4 text-sm">
                    <Link href="/settings/profile" class="text-slate-600 hover:text-slate-950">{{ page.props.auth.user?.name }}</Link>
                    <button class="text-slate-600 hover:text-slate-950" type="button" @click="logout">Salir</button>
                </div>
            </nav>
        </header>
        <main class="mx-auto max-w-6xl px-6 py-10">
            <slot />
        </main>
    </div>
</template>
