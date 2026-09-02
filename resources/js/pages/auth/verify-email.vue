<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{ status?: string }>();
</script>

<template>
    <AuthLayout title="Verificar correo">
        <h1 class="text-2xl font-semibold">Verifica tu correo</h1>
        <p class="mt-3 text-sm text-slate-600">Antes de continuar, revisa el enlace de verificacion que enviamos a tu correo.</p>
        <p v-if="status" class="mt-3 text-sm text-emerald-700">{{ status }}</p>
        <div class="mt-6 flex items-center justify-between text-sm">
            <button class="rounded-md bg-slate-900 px-4 py-2 font-medium text-white" type="button" @click="router.post('/email/verification-notification')">Reenviar correo</button>
            <Link href="/logout" method="post" as="button" class="text-slate-600 hover:text-slate-950">Salir</Link>
        </div>
    </AuthLayout>
</template>
