<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{ email: string; token: string }>();
const form = useForm({ email: props.email, token: props.token, password: '', password_confirmation: '' });
</script>

<template>
    <AuthLayout title="Restablecer contrasena">
        <h1 class="text-2xl font-semibold">Restablecer contrasena</h1>
        <form class="mt-6 space-y-4" @submit.prevent="form.post('/reset-password')">
            <label class="block text-sm font-medium">Correo<input v-model="form.email" class="mt-1 w-full rounded-md border-slate-300" type="email" required></label><InputError :message="form.errors.email" />
            <label class="block text-sm font-medium">Nueva contrasena<input v-model="form.password" class="mt-1 w-full rounded-md border-slate-300" type="password" required autofocus></label><InputError :message="form.errors.password" />
            <label class="block text-sm font-medium">Confirmar contrasena<input v-model="form.password_confirmation" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label>
            <button class="w-full rounded-md bg-slate-900 px-4 py-2 font-medium text-white" :disabled="form.processing">Restablecer</button>
        </form>
    </AuthLayout>
</template>
