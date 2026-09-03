<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    CalendarDays,
    ClipboardCheck,
    MessageSquare,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user as any);

const fullName = computed(() => {
    return user.value?.person?.full_name ?? user.value?.name ?? 'Usuario';
});

const firstName = computed(() => {
    return user.value?.person?.first_name ?? fullName.value.split(' ')[0];
});

const roles = computed<string[]>(() => {
    return user.value?.roles ?? [];
});

const roleLabels: Record<string, string> = {
    admin: 'Administrador',
    gestion: 'Gestión',
    director: 'Director',
    preceptor: 'Preceptor',
    docente: 'Docente',
    alumno: 'Estudiante',
    responsable: 'Responsable',
};

const roleNames = computed(() => {
    return roles.value
        .map((role) => roleLabels[role] ?? role)
        .join(' · ');
});

const modules = [
    {
        title: 'Personas',
        description: 'Estudiantes, docentes y responsables.',
        icon: Users,
    },
    {
        title: 'Académico',
        description: 'Cursos, divisiones, materias y ciclos.',
        icon: BookOpen,
    },
    {
        title: 'Asistencia',
        description: 'Registro y seguimiento institucional.',
        icon: ClipboardCheck,
    },
    {
        title: 'Calendario',
        description: 'Eventos y fechas importantes.',
        icon: CalendarDays,
    },
];

const recentItems = [
    {
        title: 'Identidad institucional configurada',
        description: 'Personas, usuarios y perfiles ya están disponibles.',
    },
    {
        title: 'Acceso por DNI habilitado',
        description: 'El ingreso institucional ya utiliza DNI y contraseña.',
    },
    {
        title: 'Roles institucionales configurados',
        description: 'Los perfiles de acceso están listos para los próximos módulos.',
    },
];
</script>

<template>
    <Head title="Inicio" />

    <AuthenticatedLayout header="Inicio">
        <div class="mx-auto max-w-7xl space-y-8">
            <!-- bienvenida -->
            <section
                class="relative overflow-hidden rounded-3xl bg-[#071a35] px-7 py-8 text-white shadow-sm sm:px-9 sm:py-10"
            >
                <div
                    class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"
                />

                <div
                    class="absolute bottom-[-100px] left-1/3 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"
                />

                <div class="relative">
                    <p class="text-sm font-semibold text-cyan-300">
                        {{ roleNames || 'Usuario ISAE' }}
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        ¡Bienvenido, {{ firstName }}!
                    </h2>

                    <p
                        class="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base"
                    >
                        Este es tu espacio dentro de Plataforma ISAE. Desde acá
                        vas a poder acceder a las herramientas y la información
                        que correspondan a tu rol institucional.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <span
                            v-for="role in roles"
                            :key="role"
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-medium text-slate-200"
                        >
                            {{ roleLabels[role] ?? role }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- resumen -->
            <section>
                <div class="mb-5 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-cyan-700">
                            Accesos principales
                        </p>

                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950">
                            Módulos de la plataforma
                        </h2>
                    </div>

                    <span class="hidden text-sm text-slate-400 sm:block">
                        Se habilitarán progresivamente
                    </span>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="module in modules"
                        :key="module.title"
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"
                        >
                            <component
                                :is="module.icon"
                                class="h-5 w-5"
                            />
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-950">
                            {{ module.title }}
                        </h3>

                        <p class="mt-2 min-h-12 text-sm leading-6 text-slate-500">
                            {{ module.description }}
                        </p>

                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-400">
                                Próximamente
                            </span>

                            <ArrowRight
                                class="h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-cyan-600"
                            />
                        </div>
                    </article>
                </div>
            </section>

            <!-- inferior -->
            <section class="grid gap-6 lg:grid-cols-[1.35fr_.65fr]">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-950">
                                Estado de la plataforma
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Últimos avances de configuración
                            </p>
                        </div>

                        <div
                            class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
                        >
                            Operativa
                        </div>
                    </div>

                    <div class="mt-6 divide-y divide-slate-100">
                        <div
                            v-for="item in recentItems"
                            :key="item.title"
                            class="flex gap-4 py-4 first:pt-0 last:pb-0"
                        >
                            <div
                                class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-cyan-500"
                            />

                            <div>
                                <p class="text-sm font-semibold text-slate-900">
                                    {{ item.title }}
                                </p>

                                <p class="mt-1 text-sm leading-6 text-slate-500">
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                    >
                        <MessageSquare class="h-5 w-5" />
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-950">
                        Comunicaciones
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Cuando habilitemos el módulo, acá vas a encontrar
                        novedades, avisos y mensajes institucionales.
                    </p>

                    <div
                        class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center"
                    >
                        <p class="text-sm font-medium text-slate-400">
                            Sin novedades por ahora
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>