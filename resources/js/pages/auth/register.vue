<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

function submit() {
    form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') });
}
</script>

<template>
    <AuthLayout title="Crear cuenta">
        <h1 class="text-2xl font-semibold">Crear cuenta</h1>
        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <label class="block text-sm font-medium">Nombre<input v-model="form.name" class="mt-1 w-full rounded-md border-slate-300" required autofocus></label><InputError :message="form.errors.name" />
            <label class="block text-sm font-medium">Correo<input v-model="form.email" class="mt-1 w-full rounded-md border-slate-300" type="email" required></label><InputError :message="form.errors.email" />
            <label class="block text-sm font-medium">Contrasena<input v-model="form.password" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label><InputError :message="form.errors.password" />
            <label class="block text-sm font-medium">Confirmar contrasena<input v-model="form.password_confirmation" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label>
            <button class="w-full rounded-md bg-slate-900 px-4 py-2 font-medium text-white disabled:opacity-50" :disabled="form.processing">Crear cuenta</button>
        </form>
        <p class="mt-5 text-sm text-slate-600">Ya tenes cuenta? <Link href="/login" class="text-slate-950">Ingresar</Link></p>
    </AuthLayout>
</template>
