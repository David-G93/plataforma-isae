<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    Eye,
    Plus,
    Search,
    UserRound,
} from '@lucide/vue';
import { ref, watch } from 'vue';

interface Person {
    id: number;
    dni: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;

    profiles: {
        student: boolean;
        teacher: boolean;
        guardian: boolean;
    };

    user: {
        id: number;
        is_active: boolean;
        roles: string[];
    } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    people: {
        data: Person[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        links: PaginationLink[];
    };

    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search ?? '');

let timeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        router.get(
            route('people.index'),
            {
                search: value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 350);
});

const profileLabels = (person: Person) => {
    const profiles: string[] = [];

    if (person.profiles.student) {
        profiles.push('Estudiante');
    }

    if (person.profiles.teacher) {
        profiles.push('Docente');
    }

    if (person.profiles.guardian) {
        profiles.push('Responsable');
    }

    return profiles;
};
</script>

<template>
    <Head title="Personas" />

    <AuthenticatedLayout header="Personas">
        <div class="mx-auto max-w-7xl space-y-6">
            <section
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold text-cyan-700">
                        Identidad institucional
                    </p>

                    <h2
                        class="mt-1 text-2xl font-bold tracking-tight text-slate-950"
                    >
                        Personas
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Administrá estudiantes, docentes, responsables y
                        usuarios institucionales.
                    </p>
                </div>

<Link
    :href="route('people.create')"
    class="inline-flex h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold shadow-md transition hover:opacity-90"
    style="
        background-color: #071a35;
        color: #ffffff;
        border: 1px solid #071a35;
    "
>
    <Plus class="mr-2 h-4 w-4" />

    Nueva persona
</Link>
            </section>

            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="relative w-full max-w-md">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />

                        <Input
                            v-model="search"
                            type="search"
                            placeholder="Buscar por DNI, nombre o apellido..."
                            class="pl-10"
                        />
                    </div>

                    <p class="text-sm text-slate-500">
                        {{ people.total }}
                        {{ people.total === 1 ? 'persona' : 'personas' }}
                    </p>
                </div>

                <div
                    v-if="people.data.length === 0"
                    class="flex flex-col items-center px-6 py-16 text-center"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                    >
                        <UserRound class="h-6 w-6" />
                    </div>

                    <h3 class="mt-4 font-semibold text-slate-900">
                        No encontramos personas
                    </h3>

                    <p class="mt-2 max-w-sm text-sm text-slate-500">
                        Probá con otra búsqueda o registrá una nueva persona.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50">
                            <tr
                                class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                            >
                                <th class="px-5 py-4">
                                    Persona
                                </th>

                                <th class="px-5 py-4">
                                    DNI
                                </th>

                                <th class="px-5 py-4">
                                    Perfiles
                                </th>

                                <th class="px-5 py-4">
                                    Acceso
                                </th>

                                <th class="px-5 py-4">
                                    Contacto
                                </th>

                                <th class="px-5 py-4 text-right">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="person in people.data"
                                :key="person.id"
                                class="transition hover:bg-slate-50/70"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#071a35] text-sm font-bold text-cyan-300"
                                        >
                                            {{
                                                person.first_name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}{{
                                                person.last_name
                                                    .charAt(0)
                                                    .toUpperCase()
                                            }}
                                        </div>

                                        <div>
                                            <Link
                                                :href="
                                                    route(
                                                        'people.show',
                                                        person.id,
                                                    )
                                                "
                                                class="font-semibold text-slate-900 hover:text-cyan-700"
                                            >
                                                {{ person.last_name }},
                                                {{ person.first_name }}
                                            </Link>

                                            <p
                                                v-if="person.email"
                                                class="mt-0.5 text-xs text-slate-500"
                                            >
                                                {{ person.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-700"
                                >
                                    {{ person.dni }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <template
                                            v-if="
                                                profileLabels(person).length > 0
                                            "
                                        >
                                            <span
                                                v-for="profile in profileLabels(
                                                    person,
                                                )"
                                                :key="profile"
                                                class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-medium text-cyan-700"
                                            >
                                                {{ profile }}
                                            </span>
                                        </template>

                                        <span
                                            v-else
                                            class="text-sm text-slate-400"
                                        >
                                            Sin perfil
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        v-if="person.user?.is_active"
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                        />

                                        Habilitado
                                    </span>

                                    <span
                                        v-else-if="person.user"
                                        class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-amber-500"
                                        />

                                        Inactivo
                                    </span>

                                    <span
                                        v-else
                                        class="text-sm text-slate-400"
                                    >
                                        Sin usuario
                                    </span>
                                </td>

                                <td
                                    class="px-5 py-4 text-sm text-slate-500"
                                >
                                    {{ person.phone || '—' }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="
                                            route(
                                                'people.show',
                                                person.id,
                                            )
                                        "
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-950"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="people.last_page > 1"
                    class="flex items-center justify-between border-t border-slate-200 px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Mostrando {{ people.from }}–{{ people.to }} de
                        {{ people.total }}
                    </p>

                    <div class="flex items-center gap-1">
                        <Link
                            v-for="link in people.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            :class="[
                                'flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm transition',
                                link.active
                                    ? 'bg-[#071a35] font-semibold text-white'
                                    : link.url
                                      ? 'text-slate-600 hover:bg-slate-100'
                                      : 'pointer-events-none text-slate-300',
                            ]"
                            preserve-scroll
                        >
                            <ChevronLeft
                                v-if="link.label.includes('Previous')"
                                class="h-4 w-4"
                            />

                            <ChevronRight
                                v-else-if="link.label.includes('Next')"
                                class="h-4 w-4"
                            />

                            <span v-else v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>