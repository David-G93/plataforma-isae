<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BookX,
    CheckCircle2,
    GraduationCap,
    Layers3,
    ShieldAlert,
    UserRoundX,
    UsersRound,
} from '@lucide/vue';

type AcademicYear = {
    id: number;
    year: number;
    name: string;
    is_active: boolean;
};

type Quality = {
    summary: {
        total_issues: number;
        is_clean: boolean;
        teachings_without_teacher: number;
        divisions_without_students: number;
        students_without_modality: number;
        students_without_guardian: number;
        teachers_outside_division: number;
    };
    teachings_without_teacher: Array<{
        teaching_id: number;
        subject: string;
        course: string;
        division: string;
        modality: string | null;
    }>;
    divisions_without_students: Array<{
        division_id: number;
        course: string;
        division: string;
        level: string;
    }>;
    students_without_modality: Array<{
        enrollment_id: number;
        student: string;
        course: string;
        division: string;
    }>;
    students_without_guardian: Array<{
        enrollment_id: number;
        student: string;
        course: string;
        division: string;
    }>;
    teachers_outside_division: Array<{
        teaching_id: number;
        teacher_profile_id: number;
        teacher: string;
        subject: string;
        course: string;
        division: string;
    }>;
};

const props = defineProps<{
    academicYears: AcademicYear[];
    selectedYear: number;
    quality: Quality;
}>();

const changeYear = (event: Event) => {
    router.get(
        route('academic.quality.index'),
        { year: Number((event.target as HTMLSelectElement).value) },
        { preserveState: false, preserveScroll: false },
    );
};

const categories = [
    {
        key: 'teachings_without_teacher',
        label: 'Cursadas sin docente',
        icon: BookX,
        description: 'Cursadas activas que todavía no tienen ningún docente asignado.',
    },
    {
        key: 'divisions_without_students',
        label: 'Divisiones sin estudiantes',
        icon: Layers3,
        description: 'Divisiones activas del ciclo sin matrículas activas.',
    },
    {
        key: 'students_without_modality',
        label: 'Alumnos sin modalidad',
        icon: GraduationCap,
        description: 'Estudiantes de 2° a 5° Secundaria que deben pertenecer a Cs. Naturales o Informática.',
    },
    {
        key: 'students_without_guardian',
        label: 'Estudiantes sin responsable',
        icon: UserRoundX,
        description: 'Matrículas activas cuyos estudiantes no tienen responsable/padre/tutor vinculado.',
    },
    {
        key: 'teachers_outside_division',
        label: 'Docentes fuera de la división',
        icon: ShieldAlert,
        description: 'Docentes asignados a una cursada sin estar previamente vinculados a esa división.',
    },
] as const;
</script>

<template>
    <Head title="Controles de calidad" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-[#071a35] p-6 text-white shadow-xl sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-2 text-sm font-medium text-cyan-300">
                            <ShieldAlert class="h-4 w-4" /> Configuración
                        </div>
                        <h1 class="text-3xl font-bold">Controles de calidad</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                            Alertas estructurales del ciclo para detectar datos incompletos antes de usar asistencia y calificaciones.
                        </p>
                    </div>

                    <select
                        :value="selectedYear"
                        class="rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-sm text-white outline-none"
                        @change="changeYear"
                    >
                        <option
                            v-for="year in academicYears"
                            :key="year.id"
                            :value="year.year"
                            class="text-slate-900"
                        >
                            {{ year.name }}
                        </option>
                    </select>
                </div>
            </section>

            <section
                :class="[
                    'rounded-3xl border p-6',
                    quality.summary.is_clean
                        ? 'border-emerald-200 bg-emerald-50'
                        : 'border-amber-200 bg-amber-50',
                ]"
            >
                <div class="flex items-start gap-4">
                    <CheckCircle2 v-if="quality.summary.is_clean" class="mt-0.5 h-7 w-7 text-emerald-600" />
                    <AlertTriangle v-else class="mt-0.5 h-7 w-7 text-amber-600" />
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ quality.summary.is_clean ? 'Estructura sin alertas' : `${quality.summary.total_issues} alerta(s) por revisar` }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ quality.summary.is_clean
                                ? 'Los cinco controles obligatorios están correctos para este ciclo.'
                                : 'Corregí estas alertas antes de avanzar con procesos académicos sensibles.' }}
                        </p>
                    </div>
                </div>
            </section>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    v-for="category in categories"
                    :key="category.key"
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between gap-3">
                        <component :is="category.icon" class="h-5 w-5 text-slate-500" />
                        <span
                            :class="[
                                'rounded-full px-2.5 py-1 text-xs font-bold',
                                quality.summary[category.key] === 0
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-amber-100 text-amber-800',
                            ]"
                        >
                            {{ quality.summary[category.key] }}
                        </span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-900">{{ category.label }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ category.description }}</p>
                </div>
            </div>

            <section class="space-y-4">
                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center gap-3"><BookX class="h-5 w-5 text-slate-500" /><h2 class="font-bold text-slate-900">Cursadas sin docente</h2></div>
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">{{ quality.teachings_without_teacher.length }}</span>
                    </header>
                    <div v-if="quality.teachings_without_teacher.length" class="divide-y divide-slate-100">
                        <div v-for="item in quality.teachings_without_teacher" :key="item.teaching_id" class="flex flex-col gap-1 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-medium text-slate-900">{{ item.subject }}</p><p class="text-sm text-slate-500">{{ item.course }} · División {{ item.division }}</p></div>
                            <span v-if="item.modality" class="text-xs font-semibold text-violet-700">{{ item.modality }}</span>
                        </div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-emerald-700">No hay cursadas sin docente.</p>
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center gap-3"><Layers3 class="h-5 w-5 text-slate-500" /><h2 class="font-bold text-slate-900">Divisiones sin estudiantes</h2></div>
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">{{ quality.divisions_without_students.length }}</span>
                    </header>
                    <div v-if="quality.divisions_without_students.length" class="divide-y divide-slate-100">
                        <div v-for="item in quality.divisions_without_students" :key="item.division_id" class="px-6 py-4"><p class="font-medium text-slate-900">{{ item.course }} · División {{ item.division }}</p><p class="text-sm text-slate-500">{{ item.level }}</p></div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-emerald-700">Todas las divisiones tienen estudiantes activos.</p>
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center gap-3"><GraduationCap class="h-5 w-5 text-slate-500" /><h2 class="font-bold text-slate-900">Alumnos de 2°–5° sin modalidad</h2></div>
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">{{ quality.students_without_modality.length }}</span>
                    </header>
                    <div v-if="quality.students_without_modality.length" class="divide-y divide-slate-100">
                        <div v-for="item in quality.students_without_modality" :key="item.enrollment_id" class="px-6 py-4"><p class="font-medium text-slate-900">{{ item.student }}</p><p class="text-sm text-slate-500">{{ item.course }} · División {{ item.division }}</p></div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-emerald-700">Todos los alumnos que requieren modalidad la tienen asignada.</p>
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center gap-3"><UsersRound class="h-5 w-5 text-slate-500" /><h2 class="font-bold text-slate-900">Estudiantes sin responsable</h2></div>
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">{{ quality.students_without_guardian.length }}</span>
                    </header>
                    <div v-if="quality.students_without_guardian.length" class="divide-y divide-slate-100">
                        <div v-for="item in quality.students_without_guardian" :key="item.enrollment_id" class="px-6 py-4"><p class="font-medium text-slate-900">{{ item.student }}</p><p class="text-sm text-slate-500">{{ item.course }} · División {{ item.division }}</p></div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-emerald-700">Todos los estudiantes tienen al menos un responsable.</p>
                </article>

                <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center gap-3"><ShieldAlert class="h-5 w-5 text-slate-500" /><h2 class="font-bold text-slate-900">Docentes en cursadas pero no en la división</h2></div>
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-bold text-slate-700">{{ quality.teachers_outside_division.length }}</span>
                    </header>
                    <div v-if="quality.teachers_outside_division.length" class="divide-y divide-slate-100">
                        <div v-for="item in quality.teachers_outside_division" :key="`${item.teaching_id}-${item.teacher_profile_id}`" class="px-6 py-4"><p class="font-medium text-slate-900">{{ item.teacher }}</p><p class="text-sm text-slate-500">{{ item.subject }} · {{ item.course }} · División {{ item.division }}</p></div>
                    </div>
                    <p v-else class="px-6 py-5 text-sm text-emerald-700">Todos los docentes de cursadas pertenecen a su división.</p>
                </article>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
