<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';

const form = useForm({
    dni: '',
    first_name: '',
    last_name: '',
    birth_date: '',
    email: '',
    phone: '',
    address: '',
});

const submit = () => {
    form.post(route('people.store'));
};
</script>

<template>
    <Head title="Nueva persona" />

    <AuthenticatedLayout header="Nueva persona">
        <div class="mx-auto max-w-4xl">
            <Link
                :href="route('people.index')"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900"
            >
                <ArrowLeft class="h-4 w-4" />
                Volver a personas
            </Link>

            <form
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                @submit.prevent="submit"
            >
                <div class="border-b border-slate-200 p-6 sm:p-8">
                    <p class="text-sm font-semibold text-cyan-700">
                        Identidad institucional
                    </p>

                    <h2 class="mt-1 text-2xl font-bold text-slate-950">
                        Registrar persona
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Primero registramos los datos personales. Los perfiles y
                        el acceso a la plataforma se configuran después.
                    </p>
                </div>

                <div class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
                    <div class="space-y-2">
                        <Label for="dni">
                            DNI *
                        </Label>

                        <Input
                            id="dni"
                            v-model="form.dni"
                            inputmode="numeric"
                            required
                        />

                        <p
                            v-if="form.errors.dni"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.dni }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="birth_date">
                            Fecha de nacimiento
                        </Label>

                        <Input
                            id="birth_date"
                            v-model="form.birth_date"
                            type="date"
                        />

                        <p
                            v-if="form.errors.birth_date"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.birth_date }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="last_name">
                            Apellido *
                        </Label>

                        <Input
                            id="last_name"
                            v-model="form.last_name"
                            required
                        />

                        <p
                            v-if="form.errors.last_name"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.last_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="first_name">
                            Nombre *
                        </Label>

                        <Input
                            id="first_name"
                            v-model="form.first_name"
                            required
                        />

                        <p
                            v-if="form.errors.first_name"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.first_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">
                            Email
                        </Label>

                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                        />

                        <p
                            v-if="form.errors.email"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">
                            Teléfono
                        </Label>

                        <Input
                            id="phone"
                            v-model="form.phone"
                        />

                        <p
                            v-if="form.errors.phone"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div class="space-y-2 sm:col-span-2">
                        <Label for="address">
                            Domicilio
                        </Label>

                        <Input
                            id="address"
                            v-model="form.address"
                        />

                        <p
                            v-if="form.errors.address"
                            class="text-sm text-red-600"
                        >
                            {{ form.errors.address }}
                        </p>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:px-8"
                >
                    <Button variant="outline" as-child>
                        <Link :href="route('people.index')">
                            Cancelar
                        </Link>
                    </Button>

                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        <Save class="mr-2 h-4 w-4" />

                        {{
                            form.processing
                                ? 'Guardando...'
                                : 'Guardar persona'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>