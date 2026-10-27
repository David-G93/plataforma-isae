<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, BookOpen, Check, CheckCircle2, RefreshCw, Save, UsersRound } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';

type AcademicYear = { id: number; year: number; name: string; is_active: boolean };
type Teacher = { id: number; name: string };
type TeachingValidation = {
    teaching_id: number;
    teacher_count: number;
    co_teaching: boolean;
    invalid_teacher_count: number;
    expected_students: number;
    eligible_students: number;
    student_mismatch: boolean;
    is_valid: boolean;
    issues: string[];
};
type Teaching = {
    id: number;
    subject: { id: number; name: string };
    modality: { id: number; name: string } | null;
    division: { id: number; name: string; shift: string | null };
    course: { id: number; name: string; grade: string; level: string };
    teachers: Teacher[];
    available_teachers: Teacher[];
    eligible_students_count: number;
    validation: TeachingValidation;
};

const props = defineProps<{
    canManage: boolean;
    academicYears: AcademicYear[];
    selectedYear: number;
    teachings: Teaching[];
    summary: {
        total: number;
        valid: number;
        with_issues: number;
        without_teachers: number;
        invalid_teacher_assignments: number;
        student_mismatches: number;
        co_teaching: number;
    };
}>();

const page = usePage();
const generating = ref(false);
const savingTeachingId = ref<number | null>(null);
const teacherSelections = reactive<Record<number, number[]>>({});

const syncSelections = () => {
    for (const teaching of props.teachings) {
        teacherSelections[teaching.id] = teaching.teachers.map((teacher) => teacher.id);
    }
};
syncSelections();
watch(() => props.teachings, syncSelections, { deep: true });

const groupedTeachings = computed(() => {
    const groups = new Map<number, { division: Teaching['division']; course: Teaching['course']; teachings: Teaching[] }>();
    for (const teaching of props.teachings) {
        if (!groups.has(teaching.division.id)) {
            groups.set(teaching.division.id, { division: teaching.division, course: teaching.course, teachings: [] });
        }
        groups.get(teaching.division.id)!.teachings.push(teaching);
    }
    return Array.from(groups.values());
});

const successMessage = computed(() => (page.props.flash as any)?.success ?? null);
const generationError = computed(() => (page.props.errors as any)?.generation ?? null);
const teacherError = computed(() => (page.props.errors as any)?.teacher_profile_ids ?? null);

const changeYear = (event: Event) => router.get(route('academic.teachings.index'), {
    year: Number((event.target as HTMLSelectElement).value),
});

const generateTeachings = () => {
    generating.value = true;
    router.post(route('academic.teachings.generate'), { year: props.selectedYear }, {
        preserveScroll: true,
        onFinish: () => generating.value = false,
    });
};

const saveTeachers = (teachingId: number) => {
    savingTeachingId.value = teachingId;
    router.put(route('academic.teachings.teachers.update', teachingId), {
        teacher_profile_ids: teacherSelections[teachingId] ?? [],
    }, {
        preserveScroll: true,
        onFinish: () => savingTeachingId.value = null,
    });
};
</script>

<template>
    <Head title="Cursadas" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-[#071a35] p-6 text-white shadow-xl sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-2 text-sm font-medium text-cyan-300"><BookOpen class="h-4 w-4" /> Configuración</div>
                        <h1 class="text-3xl font-bold">Cursadas</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-300">Validación de docentes, pertenencia a división, alumnos comunes/por modalidad y co-docencia.</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <select :value="selectedYear" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-sm text-white" @change="changeYear"><option v-for="year in academicYears" :key="year.id" :value="year.year" class="text-slate-900">{{ year.name }}</option></select>
                        <button v-if="canManage" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-semibold text-slate-950 disabled:opacity-60" :disabled="generating" @click="generateTeachings"><RefreshCw :class="['h-4 w-4', generating ? 'animate-spin' : '']" /> {{ generating ? 'Generando...' : 'Generar / actualizar' }}</button>
                    </div>
                </div>
            </section>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-7">
                <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Cursadas</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ summary.total }}</p></div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Correctas</p><p class="mt-1 text-2xl font-bold text-emerald-800">{{ summary.valid }}</p></div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Con observaciones</p><p class="mt-1 text-2xl font-bold text-amber-800">{{ summary.with_issues }}</p></div>
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Sin docente</p><p class="mt-1 text-2xl font-bold text-rose-800">{{ summary.without_teachers }}</p></div>
                <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-orange-600">Docente inválido</p><p class="mt-1 text-2xl font-bold text-orange-800">{{ summary.invalid_teacher_assignments }}</p></div>
                <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-violet-600">Alumnos desfasados</p><p class="mt-1 text-2xl font-bold text-violet-800">{{ summary.student_mismatches }}</p></div>
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-cyan-600">Co-docencia</p><p class="mt-1 text-2xl font-bold text-cyan-800">{{ summary.co_teaching }}</p></div>
            </div>

            <div v-if="successMessage" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><Check class="h-5 w-5" />{{ successMessage }}</div>
            <div v-if="generationError || teacherError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ generationError || teacherError }}</div>

            <div v-if="groupedTeachings.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"><BookOpen class="mx-auto h-10 w-10 text-slate-300" /><h2 class="mt-4 text-lg font-semibold text-slate-800">Todavía no hay cursadas</h2><p class="mt-2 text-sm text-slate-500">Generalas desde los planes de estudio del ciclo seleccionado.</p></div>

            <section v-for="group in groupedTeachings" :key="group.division.id" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-200 bg-slate-50 px-6 py-5"><div class="flex items-center justify-between gap-4"><div><p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">{{ group.course.level }}</p><h2 class="mt-1 text-xl font-bold text-slate-900">{{ group.course.name }}</h2><p class="mt-1 text-sm text-slate-500">División {{ group.division.name }}<span v-if="group.division.shift"> · {{ group.division.shift }}</span></p></div><div class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">{{ group.teachings.length }} materias</div></div></header>

                <div class="divide-y divide-slate-100">
                    <article v-for="teaching in group.teachings" :key="teaching.id" class="grid gap-5 p-6 xl:grid-cols-[1.1fr_0.8fr_2fr_auto]" :class="teaching.validation.is_valid ? '' : 'bg-amber-50/40'">
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><p class="font-semibold text-slate-900">{{ teaching.subject.name }}</p><span v-if="teaching.validation.is_valid" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-emerald-700"><CheckCircle2 class="h-3 w-3" /> OK</span><span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-amber-700"><AlertTriangle class="h-3 w-3" /> Revisar</span><span v-if="teaching.validation.co_teaching" class="rounded-full bg-cyan-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-cyan-700">Co-docencia</span></div>
                            <span v-if="teaching.modality" class="mt-2 inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">{{ teaching.modality.name }}</span><span v-else class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Común</span>
                            <ul v-if="!teaching.validation.is_valid" class="mt-3 list-disc space-y-1 pl-5 text-xs text-amber-800"><li v-for="issue in teaching.validation.issues" :key="issue">{{ issue }}</li></ul>
                        </div>
                        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Alumnos</p><div class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-3 py-2 font-semibold text-cyan-800"><UsersRound class="h-4 w-4" />{{ teaching.validation.eligible_students }} / {{ teaching.validation.expected_students }}</div><p class="mt-2 text-xs text-slate-400">Elegibles / esperados</p></div>
                        <div><p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">Docentes</p><select v-model="teacherSelections[teaching.id]" multiple :disabled="!canManage || teaching.available_teachers.length === 0" class="min-h-28 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm disabled:bg-slate-50"><option v-for="teacher in teaching.available_teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}</option></select><p class="mt-2 text-xs text-slate-400"><template v-if="teaching.available_teachers.length">Solo docentes previamente vinculados a esta división.</template><template v-else>Primero vinculá docentes desde Divisiones.</template></p></div>
                        <div v-if="canManage" class="flex items-end"><button type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60 xl:w-auto" :disabled="savingTeachingId === teaching.id || teaching.available_teachers.length === 0" @click="saveTeachers(teaching.id)"><Save class="h-4 w-4" /> {{ savingTeachingId === teaching.id ? 'Guardando...' : 'Guardar' }}</button></div>
                    </article>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
