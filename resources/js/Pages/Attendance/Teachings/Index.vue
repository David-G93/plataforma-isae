<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpenCheck, CalendarCheck2, ClipboardCheck, Filter, RotateCcw, UsersRound } from '@lucide/vue';

type AcademicYear = { id: number; year: number; name: string; is_active: boolean };
type Option = { id: number; name: string };
type DivisionOption = { id: number; name: string; course: string };
type Teaching = {
    id: number;
    subject: string;
    subject_id: number;
    modality: string | null;
    course: string;
    course_id: number;
    division: string;
    division_id: number;
    level: string;
    level_id: number;
    students_count: number;
    teachers: string[];
};

const props = defineProps<{
    academicYears: AcademicYear[];
    selectedYear: number;
    teachings: Teaching[];
    canViewOfficial: boolean;
    canViewAll: boolean;
    filters: {
        level: number | null;
        course: number | null;
        division: number | null;
        subject: number | null;
    };
    filterOptions: {
        levels: Option[];
        courses: Option[];
        divisions: DivisionOption[];
        subjects: Option[];
    };
}>();

const reload = (overrides: Record<string, number | null>) => {
    router.get(route('attendance.teachings.index'), {
        year: overrides.year ?? props.selectedYear,
        level: overrides.level !== undefined ? overrides.level : props.filters.level,
        course: overrides.course !== undefined ? overrides.course : props.filters.course,
        division: overrides.division !== undefined ? overrides.division : props.filters.division,
        subject: overrides.subject !== undefined ? overrides.subject : props.filters.subject,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const changeYear = (event: Event) => reload({
    year: Number((event.target as HTMLSelectElement).value),
    level: null,
    course: null,
    division: null,
    subject: null,
});

const changeLevel = (event: Event) => reload({
    level: Number((event.target as HTMLSelectElement).value) || null,
    course: null,
    division: null,
    subject: null,
});

const changeCourse = (event: Event) => reload({
    course: Number((event.target as HTMLSelectElement).value) || null,
    division: null,
    subject: null,
});

const changeDivision = (event: Event) => reload({
    division: Number((event.target as HTMLSelectElement).value) || null,
    subject: null,
});

const changeSubject = (event: Event) => reload({
    subject: Number((event.target as HTMLSelectElement).value) || null,
});

const clearFilters = () => reload({
    level: null,
    course: null,
    division: null,
    subject: null,
});
</script>

<template>
    <Head title="Asistencia por cursada" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-[#071a35] p-7 text-white shadow-xl">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-cyan-300">
                            <ClipboardCheck class="h-4 w-4" /> Asistencia docente
                        </div>
                        <h1 class="text-3xl font-bold">Asistencia por cursada</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                            <template v-if="canViewAll">
                                Vista global de cursadas. Podés filtrar por nivel, curso, división y materia.
                            </template>
                            <template v-else>
                                Solo ves las cursadas que tenés asignadas. Los filtros se aplican únicamente dentro de esas materias.
                            </template>
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <select :value="selectedYear" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-sm text-white" @change="changeYear">
                            <option v-for="year in academicYears" :key="year.id" :value="year.year" class="text-slate-900">{{ year.name }}</option>
                        </select>

                        <Link v-if="canViewOfficial" :href="route('attendance.official.index', { year: selectedYear })" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900">
                            <CalendarCheck2 class="h-4 w-4" /> Asistencia oficial
                        </Link>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <Filter class="h-5 w-5 text-cyan-700" />
                        <div>
                            <h2 class="font-bold text-slate-900">Filtros</h2>
                            <p class="text-xs text-slate-500">{{ teachings.length }} cursadas visibles con los filtros actuales.</p>
                        </div>
                    </div>

                    <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50" @click="clearFilters">
                        <RotateCcw class="h-4 w-4" /> Limpiar
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="text-sm font-bold text-slate-700">
                        Nivel
                        <select :value="filters.level ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeLevel">
                            <option value="">Todos</option>
                            <option v-for="option in filterOptions.levels" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">
                        Curso
                        <select :value="filters.course ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeCourse">
                            <option value="">Todos</option>
                            <option v-for="option in filterOptions.courses" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">
                        División
                        <select :value="filters.division ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeDivision">
                            <option value="">Todas</option>
                            <option v-for="option in filterOptions.divisions" :key="option.id" :value="option.id">{{ option.course }} · {{ option.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">
                        Materia
                        <select :value="filters.subject ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeSubject">
                            <option value="">Todas</option>
                            <option v-for="option in filterOptions.subjects" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </label>
                </div>
            </section>

            <section v-if="teachings.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <BookOpenCheck class="mx-auto h-10 w-10 text-slate-300" />
                <h2 class="mt-4 text-lg font-bold text-slate-800">No hay cursadas disponibles</h2>
                <p class="mt-2 text-sm text-slate-500">No hay cursadas dentro de tu alcance que coincidan con los filtros seleccionados.</p>
            </section>

            <div v-else class="grid gap-4 lg:grid-cols-2">
                <Link v-for="teaching in teachings" :key="teaching.id" :href="route('attendance.teachings.show', { teaching: teaching.id, year: selectedYear, level: filters.level, course: filters.course, division: filters.division, subject: filters.subject })" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">{{ teaching.level }}</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ teaching.subject }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ teaching.course }} · División {{ teaching.division }}</p>
                        </div>
                        <span v-if="teaching.modality" class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">{{ teaching.modality }}</span>
                        <span v-else class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Común</span>
                    </div>

                    <div class="mt-5 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <UsersRound class="h-4 w-4 text-cyan-700" /> {{ teaching.students_count }} alumnos
                    </div>

                    <p class="mt-3 text-xs text-slate-400">Docentes: {{ teaching.teachers.join(', ') || 'Sin docente' }}</p>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
