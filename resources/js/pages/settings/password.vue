<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{ status?: string }>();
const form = useForm({ current_password: '', password: '', password_confirmation: '' });
</script>

<template>
    <Head title="Contrasena" />
    <AppLayout>
        <h1 class="text-3xl font-bold tracking-tight">Contrasena</h1>
        <form class="mt-8 max-w-lg space-y-4 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200" @submit.prevent="form.put('/settings/password', { onFinish: () => form.reset() })">
            <p v-if="status" class="text-sm text-emerald-700">{{ status }}</p>
            <label class="block text-sm font-medium">Contrasena actual<input v-model="form.current_password" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label><InputError :message="form.errors.current_password" />
            <label class="block text-sm font-medium">Nueva contrasena<input v-model="form.password" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label><InputError :message="form.errors.password" />
            <label class="block text-sm font-medium">Confirmar nueva contrasena<input v-model="form.password_confirmation" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label>
            <button class="rounded-md bg-slate-900 px-4 py-2 font-medium text-white" :disabled="form.processing">Actualizar contrasena</button>
        </form>
    </AppLayout>
</template>
