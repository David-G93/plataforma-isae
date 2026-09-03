<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from '@lucide/vue';

const props = defineProps<{
    person: {
        id: number;
        dni: string;
        first_name: string;
        last_name: string;
        birth_date: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;
    };
}>();

const form = useForm({
    dni: props.person.dni,
    first_name: props.person.first_name,
    last_name: props.person.last_name,
    birth_date: props.person.birth_date ?? '',
    email: props.person.email ?? '',
    phone: props.person.phone ?? '',
    address: props.person.address ?? '',
});

const submit = () => {
    form.put(route('people.update', props.person.id));
};
</script>

<template>
    <Head title="Editar persona" />

    <AuthenticatedLayout header="Editar persona">
        <div class="mx-auto max-w-4xl">
            <Link
                :href="route('people.show', person.id)"
                class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900"
            >
                <ArrowLeft class="h-4 w-4" />

                Volver
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
                        Editar {{ person.last_name }}, {{ person.first_name }}
                    </h2>
                </div>

                <div class="grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
                    <div class="space-y-2">
                        <Label for="dni">
                            DNI *
                        </Label>

                        <Input
                            id="dni"
                            v-model="form.dni"
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
                        <Link :href="route('people.show', person.id)">
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
                                : 'Guardar cambios'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>