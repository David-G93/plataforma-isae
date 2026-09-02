<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{ canResetPassword: boolean; status?: string }>();

const form = useForm({ email: '', password: '', remember: false });

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') });
}
</script>

<template>
    <AuthLayout title="Ingresar">
        <h1 class="text-2xl font-semibold">Ingresar</h1>
        <p v-if="status" class="mt-2 text-sm text-emerald-700">{{ status }}</p>
        <form class="mt-6 space-y-4" @submit.prevent="submit">
            <label class="block text-sm font-medium">Correo<input v-model="form.email" class="mt-1 w-full rounded-md border-slate-300" type="email" required autofocus></label>
            <InputError :message="form.errors.email" />
            <label class="block text-sm font-medium">Contrasena<input v-model="form.password" class="mt-1 w-full rounded-md border-slate-300" type="password" required></label>
            <InputError :message="form.errors.password" />
            <label class="flex items-center gap-2 text-sm"><input v-model="form.remember" type="checkbox"> Recordarme</label>
            <button class="w-full rounded-md bg-slate-900 px-4 py-2 font-medium text-white disabled:opacity-50" :disabled="form.processing">Ingresar</button>
        </form>
        <div class="mt-5 flex justify-between text-sm">
            <Link v-if="canResetPassword" href="/forgot-password" class="text-slate-600 hover:text-slate-950">Olvide mi contrasena</Link>
            <Link href="/register" class="text-slate-600 hover:text-slate-950">Crear cuenta</Link>
        </div>
    </AuthLayout>
</template>
