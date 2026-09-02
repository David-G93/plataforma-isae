<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{ status?: string }>();
const form = useForm({ email: '' });
</script>

<template>
    <AuthLayout title="Recuperar contrasena">
        <h1 class="text-2xl font-semibold">Recuperar contrasena</h1>
        <p class="mt-2 text-sm text-slate-600">Te enviaremos un enlace para restablecerla.</p>
        <p v-if="status" class="mt-3 text-sm text-emerald-700">{{ status }}</p>
        <form class="mt-6 space-y-4" @submit.prevent="form.post('/forgot-password')">
            <label class="block text-sm font-medium">Correo<input v-model="form.email" class="mt-1 w-full rounded-md border-slate-300" type="email" required autofocus></label>
            <InputError :message="form.errors.email" />
            <button class="w-full rounded-md bg-slate-900 px-4 py-2 font-medium text-white" :disabled="form.processing">Enviar enlace</button>
        </form>
        <Link href="/login" class="mt-5 block text-sm text-slate-600 hover:text-slate-950">Volver a ingresar</Link>
    </AuthLayout>
</template>
