<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{ status?: string; mustVerifyEmail: boolean }>();
const page = usePage();
const user = page.props.auth.user!;
const form = useForm({ name: user.name, email: user.email });
</script>

<template>
    <Head title="Perfil" />
    <AppLayout>
        <h1 class="text-3xl font-bold tracking-tight">Perfil</h1>
        <form class="mt-8 max-w-lg space-y-4 rounded-lg bg-white p-6 shadow-sm ring-1 ring-slate-200" @submit.prevent="form.patch('/settings/profile')">
            <p v-if="status" class="text-sm text-emerald-700">{{ status }}</p>
            <label class="block text-sm font-medium">Nombre<input v-model="form.name" class="mt-1 w-full rounded-md border-slate-300" required></label><InputError :message="form.errors.name" />
            <label class="block text-sm font-medium">Correo<input v-model="form.email" class="mt-1 w-full rounded-md border-slate-300" type="email" required></label><InputError :message="form.errors.email" />
            <button class="rounded-md bg-slate-900 px-4 py-2 font-medium text-white" :disabled="form.processing">Guardar cambios</button>
        </form>
    </AppLayout>
</template>
