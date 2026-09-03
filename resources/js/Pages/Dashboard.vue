<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    BookOpen,
    CalendarDays,
    ClipboardCheck,
    GraduationCap,
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

const permissions = computed<string[]>(() => {
    return user.value?.permissions ?? [];
});

const roleLabels: Record<string, string> = {
    admin: 'Administrador',
    gestion: 'Gestión',
    rector: 'Rector',
    director: 'Director',
    vicedirector: 'Vicedirector',
    secretario: 'Secretario',
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

const can = (permission: string) => {
    return permissions.value.includes(permission);
};

const allModules = [
    {
        title: 'Personas',
        description:
            'Estudiantes, docentes, responsables y usuarios institucionales.',
        icon: Users,
        permission: 'people.view',
        href: route('people.index'),
        available: true,
    },
    {
        title: 'Académico',
        description:
            'Ciclos lectivos, cursos, divisiones, materias y matrículas.',
        icon: BookOpen,
        permission: 'academic.view',
        href: '#',
        available: false,
    },
    {
        title: 'Asistencia',
        description:
            'Registro y seguimiento de asistencia institucional.',
        icon: ClipboardCheck,
        permission: 'attendance.view',
        href: '#',
        available: false,
    },
    {
        title: 'Calificaciones',
        description:
            'Evaluaciones, períodos, trayectorias y resultados académicos.',
        icon: GraduationCap,
        permission: 'grades.view',
        href: '#',
        available: false,
    },
    {
        title: 'Calendario',
        description:
            'Eventos institucionales, fechas y actividades importantes.',
        icon: CalendarDays,
        permission: 'calendar.view',
        href: '#',
        available: false,
    },
    {
        title: 'Comunicaciones',
        description:
            'Avisos, novedades y comunicación con la comunidad educativa.',
        icon: MessageSquare,
        permission: 'communications.view',
        href: '#',
        available: false,
    },
    {
        title: 'Reportes',
        description:
            'Información consolidada para seguimiento y toma de decisiones.',
        icon: BarChart3,
        permission: 'reports.view',
        href: '#',
        available: false,
    },
];

const modules = computed(() => {
    return allModules.filter((module) => can(module.permission));
});

const recentItems = [
    {
        title: 'Identidad institucional configurada',
        description:
            'Personas, perfiles institucionales y usuarios están disponibles.',
    },
    {
        title: 'Responsables y estudiantes vinculados',
        description:
            'Los vínculos familiares ya pueden administrarse desde Personas.',
    },
    {
        title: 'Roles y permisos configurados',
        description:
            'Cada usuario puede recibir acceso específico según su función.',
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
                        Este es tu espacio dentro de Plataforma ISAE. Los
                        módulos disponibles dependen de tus funciones y
                        permisos dentro de la institución.
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

            <!-- módulos -->
            <section>
                <div
                    class="mb-5 flex items-end justify-between gap-4"
                >
                    <div>
                        <p class="text-sm font-semibold text-cyan-700">
                            Tus accesos
                        </p>

                        <h2
                            class="mt-1 text-2xl font-bold tracking-tight text-slate-950"
                        >
                            Módulos de la plataforma
                        </h2>
                    </div>

                    <span
                        class="hidden text-sm text-slate-400 sm:block"
                    >
                        Según tus permisos institucionales
                    </span>
                </div>

                <div
                    v-if="modules.length"
                    class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <component
                        :is="module.available ? Link : 'article'"
                        v-for="module in modules"
                        :key="module.title"
                        :href="module.available ? module.href : undefined"
                        :class="[
                            'group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition',
                            module.available
                                ? 'cursor-pointer hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md'
                                : '',
                        ]"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"
                        >
                            <component
                                :is="module.icon"
                                class="h-5 w-5"
                            />
                        </div>

                        <h3
                            class="mt-5 text-lg font-bold text-slate-950"
                        >
                            {{ module.title }}
                        </h3>

                        <p
                            class="mt-2 min-h-12 text-sm leading-6 text-slate-500"
                        >
                            {{ module.description }}
                        </p>

                        <div
                            class="mt-5 flex items-center justify-between"
                        >
                            <span
                                :class="[
                                    'text-xs font-medium',
                                    module.available
                                        ? 'text-cyan-700'
                                        : 'text-slate-400',
                                ]"
                            >
                                {{
                                    module.available
                                        ? 'Abrir módulo'
                                        : 'Próximamente'
                                }}
                            </span>

                            <ArrowRight
                                :class="[
                                    'h-4 w-4 transition',
                                    module.available
                                        ? 'text-cyan-600 group-hover:translate-x-1'
                                        : 'text-slate-300',
                                ]"
                            />
                        </div>
                    </component>
                </div>

                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-12 text-center"
                >
                    <p class="font-semibold text-slate-700">
                        No tenés módulos asignados
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Un administrador deberá revisar tus permisos
                        institucionales.
                    </p>
                </div>
            </section>

            <!-- inferior -->
            <section
                class="grid gap-6 lg:grid-cols-[1.35fr_.65fr]"
            >
                <!-- estado -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-sm font-semibold text-slate-950"
                            >
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
                                <p
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    {{ item.title }}
                                </p>

                                <p
                                    class="mt-1 text-sm leading-6 text-slate-500"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- usuario -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                    >
                        <Users class="h-5 w-5" />
                    </div>

                    <h3
                        class="mt-5 text-lg font-bold text-slate-950"
                    >
                        Perfil institucional
                    </h3>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Tu acceso se adapta automáticamente a los roles y
                        permisos que tengas asignados.
                    </p>

                    <div
                        class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Roles actuales
                        </p>

                        <div
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="role in roles"
                                :key="role"
                                class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm"
                            >
                                {{ roleLabels[role] ?? role }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>