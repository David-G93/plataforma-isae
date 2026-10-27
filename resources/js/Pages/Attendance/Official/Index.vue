<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { BookOpenText, CheckCircle2, Filter, Save } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';

type AcademicYear = { id: number; year: number; name: string; is_active: boolean };
type Period = { id: number; name: string; order: number; starts_at: string | null; ends_at: string | null };
type Division = { id: number; label: string; level: string };
type Option = { id: number; name: string };
type Student = { enrollment_id: number; name: string; dni: string | null; absences: string | number; justified_absences: string | number; observations: string | null };

const props = defineProps<{
    academicYears: AcademicYear[];
    selectedYear: number;
    periods: Period[];
    selectedPeriodId: number | null;
    divisions: Division[];
    selectedDivisionId: number | null;
    students: Student[];
    canManage: boolean;
    canViewAll: boolean;
    filters: {
        level: number | null;
        course: number | null;
    };
    filterOptions: {
        levels: Option[];
        courses: Option[];
    };
    boletinField: string;
}>();

const page = usePage();
const saving = ref(false);
const rows = reactive(props.students.map((student) => ({ ...student })));
const success = computed(() => (page.props.flash as any)?.success ?? null);

watch(() => props.students, (students) => {
    rows.splice(0, rows.length, ...students.map((student) => ({ ...student })));
}, { deep: true });

const reload = (overrides: Record<string, number | null>) => {
    router.get(route('attendance.official.index'), {
        year: overrides.year ?? props.selectedYear,
        level: overrides.level !== undefined ? overrides.level : props.filters.level,
        course: overrides.course !== undefined ? overrides.course : props.filters.course,
        division: overrides.division !== undefined ? overrides.division : props.selectedDivisionId,
        period: overrides.period !== undefined ? overrides.period : props.selectedPeriodId,
    }, { preserveState: false, preserveScroll: false });
};

const changeYear = (event: Event) => reload({
    year: Number((event.target as HTMLSelectElement).value),
    level: null,
    course: null,
    division: null,
    period: null,
});

const changeLevel = (event: Event) => reload({
    level: Number((event.target as HTMLSelectElement).value) || null,
    course: null,
    division: null,
});

const changeCourse = (event: Event) => reload({
    course: Number((event.target as HTMLSelectElement).value) || null,
    division: null,
});

const changeDivision = (event: Event) => reload({ division: Number((event.target as HTMLSelectElement).value) || null });
const changePeriod = (event: Event) => reload({ period: Number((event.target as HTMLSelectElement).value) || null });

const save = () => {
    if (!props.selectedDivisionId || !props.selectedPeriodId) return;
    saving.value = true;
    router.put(route('attendance.official.update', { division: props.selectedDivisionId, period: props.selectedPeriodId }), {
        records: rows.map((row) => ({
            student_enrollment_id: row.enrollment_id,
            absences: row.absences,
            justified_absences: row.justified_absences,
            observations: row.observations,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
};
</script>

<template>
    <Head title="Asistencia oficial" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-[#071a35] p-7 text-white shadow-xl">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-cyan-300"><BookOpenText class="h-4 w-4" /> Boletín</div>
                        <h1 class="text-3xl font-bold">Inasistencias oficiales por período</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-300">
                            <template v-if="canViewAll">
                                Vista global para supervisión. Podés filtrar por nivel, curso, división y período.
                            </template>
                            <template v-else>
                                Solo aparecen las divisiones que tenés habilitadas como preceptor/a o tutor/a de grado.
                            </template>
                            El campo “Inasistencias” queda preparado como fuente oficial para el boletín.
                        </p>
                    </div>
                    <Link :href="route('attendance.teachings.index', { year: selectedYear })" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900">Asistencia por cursada</Link>
                </div>
            </section>

            <div v-if="success" class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><CheckCircle2 class="h-5 w-5" /> {{ success }}</div>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <Filter class="h-5 w-5 text-cyan-700" />
                    <div>
                        <h2 class="font-bold text-slate-900">Filtros</h2>
                        <p class="text-xs text-slate-500">Las opciones están restringidas automáticamente según el rol y las divisiones habilitadas.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                    <label class="text-sm font-bold text-slate-700">Ciclo lectivo
                        <select :value="selectedYear" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeYear">
                            <option v-for="year in academicYears" :key="year.id" :value="year.year">{{ year.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">Nivel
                        <select :value="filters.level ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeLevel">
                            <option value="">Todos</option>
                            <option v-for="option in filterOptions.levels" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">Curso
                        <select :value="filters.course ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeCourse">
                            <option value="">Todos</option>
                            <option v-for="option in filterOptions.courses" :key="option.id" :value="option.id">{{ option.name }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">División
                        <select :value="selectedDivisionId ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changeDivision">
                            <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.label }}</option>
                        </select>
                    </label>

                    <label class="text-sm font-bold text-slate-700">Período
                        <select :value="selectedPeriodId ?? ''" class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-normal" @change="changePeriod">
                            <option v-for="period in periods" :key="period.id" :value="period.id">{{ period.name }}</option>
                        </select>
                    </label>
                </div>
            </section>

            <section v-if="periods.length === 0" class="rounded-3xl border border-amber-200 bg-amber-50 p-8 text-center text-amber-800">Este ciclo todavía no tiene períodos configurados.</section>

            <section v-else-if="divisions.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">No hay divisiones habilitadas que coincidan con los filtros seleccionados.</section>

            <section v-else class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="font-bold text-slate-900">Registro oficial para boletín</h2>
                    <p class="mt-1 text-sm text-slate-500">Admite valores decimales (por ejemplo 0.25 o 0.50) para conservar compatibilidad con medias faltas y criterios institucionales.</p>
                </header>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-white text-left text-xs font-bold uppercase tracking-wide text-slate-400">
                            <tr><th class="px-6 py-3">Alumno</th><th class="px-4 py-3">Inasistencias</th><th class="px-4 py-3">Justificadas</th><th class="px-4 py-3">Observaciones</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in rows" :key="row.enrollment_id">
                                <td class="px-6 py-4"><p class="font-semibold text-slate-900">{{ row.name }}</p><p class="text-xs text-slate-400">{{ row.dni || 'Sin DNI' }}</p></td>
                                <td class="px-4 py-4"><input v-model="row.absences" :disabled="!canManage" type="number" min="0" step="0.25" class="w-28 rounded-xl border border-slate-300 px-3 py-2 text-sm" /></td>
                                <td class="px-4 py-4"><input v-model="row.justified_absences" :disabled="!canManage" type="number" min="0" step="0.25" class="w-28 rounded-xl border border-slate-300 px-3 py-2 text-sm" /></td>
                                <td class="px-4 py-4"><input v-model="row.observations" :disabled="!canManage" type="text" maxlength="1000" class="min-w-64 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Opcional" /></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <div v-if="canManage && rows.length > 0" class="flex justify-end">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white disabled:opacity-60" :disabled="saving" @click="save"><Save class="h-4 w-4" /> {{ saving ? 'Guardando...' : 'Guardar período' }}</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
