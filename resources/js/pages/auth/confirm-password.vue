<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

const form = useForm({ password: '' });
</script>

<template>
    <AuthLayout title="Confirmar contrasena">
        <h1 class="text-2xl font-semibold">Confirmar contrasena</h1>
        <form class="mt-6 space-y-4" @submit.prevent="form.post('/user/confirm-password')">
            <label class="block text-sm font-medium">Contrasena<input v-model="form.password" class="mt-1 w-full rounded-md border-slate-300" type="password" required autofocus></label>
            <InputError :message="form.errors.password" />
            <button class="w-full rounded-md bg-slate-900 px-4 py-2 font-medium text-white" :disabled="form.processing">Confirmar</button>
        </form>
    </AuthLayout>
</template>
