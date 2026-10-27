<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle2, Save, School, UserRoundCog, Users, UsersRound } from '@lucide/vue';
import { computed, reactive, ref } from 'vue';

type AcademicYear = { id: number; year: number; name: string; is_active: boolean };
type Modality = { id: number; name: string; code: string };
type StudentOption = { id: number; name: string; dni: string | null };
type TeacherOption = { id: number; name: string; dni: string | null };
type StaffOption = { person_id: number; name: string; dni: string | null; roles: string[] };
type DivisionValidation = {
    division_id: number;
    students: number;
    teachers: number;
    staff: number;
    missing_guardians: number;
    missing_modality: number;
    invalid_modality: number;
    has_preceptor: boolean;
    has_leadership: boolean;
    is_valid: boolean;
    issues: string[];
};
type Division = {
    id: number;
    name: string;
    shift: string | null;
    course: { id: number; name: string; grade_code: string; grade: string; level: string; level_code: string };
    requires_modality: boolean;
    students: Array<{ student_profile_id: number; name: string; modality_id: number | null; modality_name: string | null; guardians: Array<{ id: number; name: string; relationship: string | null; is_primary: boolean }> }>;
    teacher_profile_ids: number[];
    staff: Array<{ person_id: number; name: string; assignment_role: string }>;
    validation: DivisionValidation;
};

const props = defineProps<{
    canManage: boolean;
    academicYears: AcademicYear[];
    selectedYear: number;
    modalities: Modality[];
    students: StudentOption[];
    teachers: TeacherOption[];
    staffPeople: StaffOption[];
    divisions: Division[];
    validationSummary: {
        total: number;
        valid: number;
        with_issues: number;
        students: number;
        missing_guardians: number;
        missing_modality: number;
    };
}>();

const page = usePage();
const savingId = ref<number | null>(null);

const forms = reactive<Record<number, {
    students: Array<{ student_profile_id: number; modality_id: number | null }>;
    teacher_profile_ids: number[];
    staff: Array<{ person_id: number; assignment_role: string }>;
}>>({});

for (const division of props.divisions) {
    forms[division.id] = {
        students: division.students.map((student) => ({
            student_profile_id: student.student_profile_id,
            modality_id: student.modality_id,
        })),
        teacher_profile_ids: [...division.teacher_profile_ids],
        staff: division.staff.map((person) => ({
            person_id: person.person_id,
            assignment_role: person.assignment_role,
        })),
    };
}

const successMessage = computed(() => (page.props.flash as any)?.success ?? null);
const errors = computed(() => page.props.errors as Record<string, string>);

const changeYear = (event: Event) => {
    router.get(route('academic.divisions.manage'), {
        year: Number((event.target as HTMLSelectElement).value),
    });
};

const addStudent = (division: Division, event: Event) => {
    const id = Number((event.target as HTMLSelectElement).value);
    if (!id || forms[division.id].students.some((item) => item.student_profile_id === id)) return;
    forms[division.id].students.push({ student_profile_id: id, modality_id: null });
    (event.target as HTMLSelectElement).value = '';
};

const removeStudent = (divisionId: number, studentId: number) => {
    forms[divisionId].students = forms[divisionId].students.filter((item) => item.student_profile_id !== studentId);
};

const addStaff = (divisionId: number, event: Event) => {
    const personId = Number((event.target as HTMLSelectElement).value);
    if (!personId || forms[divisionId].staff.some((item) => item.person_id === personId)) return;
    const person = props.staffPeople.find((item) => item.person_id === personId);
    const preferredRole = person?.roles.find((role) => ['rector', 'director', 'vicedirector', 'secretario', 'preceptor', 'gestion'].includes(role)) ?? 'personal';
    forms[divisionId].staff.push({ person_id: personId, assignment_role: preferredRole });
    (event.target as HTMLSelectElement).value = '';
};

const removeStaff = (divisionId: number, personId: number) => {
    forms[divisionId].staff = forms[divisionId].staff.filter((item) => item.person_id !== personId);
};

const studentName = (id: number) => props.students.find((item) => item.id === id)?.name ?? `Estudiante #${id}`;
const staffName = (id: number) => props.staffPeople.find((item) => item.person_id === id)?.name ?? `Persona #${id}`;
const guardiansFor = (division: Division, studentId: number) => division.students.find((item) => item.student_profile_id === studentId)?.guardians ?? [];

const save = (division: Division) => {
    savingId.value = division.id;
    router.put(route('academic.divisions.composition.update', division.id), forms[division.id], {
        preserveScroll: true,
        onFinish: () => savingId.value = null,
    });
};
</script>

<template>
    <Head title="Divisiones" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section class="rounded-3xl bg-[#071a35] p-6 text-white shadow-xl sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 flex items-center gap-2 text-sm font-medium text-cyan-300"><School class="h-4 w-4" /> Configuración</div>
                        <h1 class="text-3xl font-bold">Divisiones</h1>
                        <p class="mt-2 max-w-3xl text-sm text-slate-300">Validación de estudiantes, modalidades, responsables, docentes y personal asociado a cada división.</p>
                    </div>
                    <select :value="selectedYear" class="rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-sm text-white" @change="changeYear">
                        <option v-for="year in academicYears" :key="year.id" :value="year.year" class="text-slate-900">{{ year.name }}</option>
                    </select>
                </div>
            </section>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Divisiones</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ validationSummary.total }}</p></div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Correctas</p><p class="mt-1 text-2xl font-bold text-emerald-800">{{ validationSummary.valid }}</p></div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Con observaciones</p><p class="mt-1 text-2xl font-bold text-amber-800">{{ validationSummary.with_issues }}</p></div>
                <div class="rounded-2xl border border-cyan-200 bg-cyan-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-cyan-600">Estudiantes</p><p class="mt-1 text-2xl font-bold text-cyan-800">{{ validationSummary.students }}</p></div>
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-rose-600">Sin resp. / modalidad</p><p class="mt-1 text-2xl font-bold text-rose-800">{{ validationSummary.missing_guardians + validationSummary.missing_modality }}</p></div>
            </div>

            <div v-if="successMessage" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ successMessage }}</div>
            <div v-if="Object.keys(errors).length" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div v-for="(message, key) in errors" :key="key">{{ message }}</div>
            </div>

            <section v-for="division in divisions" :key="division.id" class="overflow-hidden rounded-3xl border bg-white shadow-sm" :class="division.validation.is_valid ? 'border-slate-200' : 'border-amber-300'">
                <header class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wider text-cyan-700">{{ division.course.level }}</p>
                                <span v-if="division.validation.is_valid" class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700"><CheckCircle2 class="h-3.5 w-3.5" /> Validada</span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700"><AlertTriangle class="h-3.5 w-3.5" /> Revisar</span>
                            </div>
                            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ division.course.name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">División {{ division.name }}<span v-if="division.shift"> · {{ division.shift }}</span> · {{ division.validation.students }} estudiantes · {{ division.validation.teachers }} docentes</p>
                        </div>
                        <button v-if="canManage" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white disabled:opacity-60" :disabled="savingId === division.id" @click="save(division)">
                            <Save class="h-4 w-4" /> {{ savingId === division.id ? 'Guardando...' : 'Guardar división' }}
                        </button>
                    </div>
                    <div v-if="!division.validation.is_valid" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                        <p class="mb-1 font-semibold">Observaciones de validación</p>
                        <ul class="list-disc space-y-1 pl-5"><li v-for="issue in division.validation.issues" :key="issue">{{ issue }}</li></ul>
                    </div>
                </header>

                <div class="grid gap-8 p-6 xl:grid-cols-3">
                    <div>
                        <div class="mb-4 flex items-center gap-2"><UsersRound class="h-5 w-5 text-cyan-700" /><h3 class="font-bold text-slate-900">Estudiantes</h3></div>
                        <select v-if="canManage" class="mb-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" @change="addStudent(division, $event)">
                            <option value="">Agregar estudiante...</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}<template v-if="student.dni"> · {{ student.dni }}</template></option>
                        </select>
                        <div class="max-h-[420px] space-y-2 overflow-auto pr-1">
                            <div v-for="student in forms[division.id].students" :key="student.student_profile_id" class="rounded-xl border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-2"><p class="text-sm font-semibold text-slate-800">{{ studentName(student.student_profile_id) }}</p><button v-if="canManage" type="button" class="text-xs font-semibold text-red-600" @click="removeStudent(division.id, student.student_profile_id)">Quitar</button></div>
                                <div v-if="guardiansFor(division, student.student_profile_id).length" class="mt-2 rounded-lg bg-slate-50 px-2.5 py-2 text-xs text-slate-600"><span class="font-semibold text-slate-700">Responsable:</span><span v-for="(guardian, index) in guardiansFor(division, student.student_profile_id)" :key="guardian.id"><span v-if="index"> · </span>{{ guardian.name }}<template v-if="guardian.relationship"> ({{ guardian.relationship }})</template></span></div>
                                <div v-else class="mt-2 rounded-lg bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700">Sin responsable vinculado</div>
                                <select v-if="division.requires_modality" v-model="student.modality_id" :disabled="!canManage" class="mt-2 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"><option :value="null">Sin modalidad</option><option v-for="modality in modalities.filter((item) => ['ciencias-naturales', 'informatica'].includes(item.code))" :key="modality.id" :value="modality.id">{{ modality.name }}</option></select>
                            </div>
                            <p v-if="forms[division.id].students.length === 0" class="text-sm text-slate-400">Sin estudiantes cargados.</p>
                        </div>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-2"><Users class="h-5 w-5 text-cyan-700" /><h3 class="font-bold text-slate-900">Docentes de la división</h3></div>
                        <select v-model="forms[division.id].teacher_profile_ids" multiple :disabled="!canManage" class="min-h-[320px] w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.name }}<template v-if="teacher.dni"> · {{ teacher.dni }}</template></option></select>
                        <p class="mt-2 text-xs text-slate-400">Solo estos docentes estarán disponibles en las cursadas de esta división.</p>
                    </div>

                    <div>
                        <div class="mb-4 flex items-center gap-2"><UserRoundCog class="h-5 w-5 text-cyan-700" /><h3 class="font-bold text-slate-900">Personal / directivos</h3></div>
                        <select v-if="canManage" class="mb-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" @change="addStaff(division.id, $event)"><option value="">Agregar persona...</option><option v-for="person in staffPeople" :key="person.person_id" :value="person.person_id">{{ person.name }}</option></select>
                        <div class="space-y-2">
                            <div v-for="person in forms[division.id].staff" :key="person.person_id" class="rounded-xl border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-2"><p class="text-sm font-semibold text-slate-800">{{ staffName(person.person_id) }}</p><button v-if="canManage" type="button" class="text-xs font-semibold text-red-600" @click="removeStaff(division.id, person.person_id)">Quitar</button></div>
                                <select v-model="person.assignment_role" :disabled="!canManage" class="mt-2 w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm"><option value="gestion">Gestión</option><option value="rector">Rector</option><option value="director">Director</option><option value="vicedirector">Vicedirector</option><option value="secretario">Secretario</option><option value="preceptor">Preceptor</option><option value="tutor">Tutor/a de grado</option><option value="personal">Personal</option></select>
                            </div>
                            <p v-if="forms[division.id].staff.length === 0" class="text-sm text-slate-400">Sin personal asociado.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
