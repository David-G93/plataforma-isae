<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    BookOpen,
    CalendarDays,
    CheckCircle2,
    ChevronRight,
    GraduationCap,
    Layers3,
    LibraryBig,
    Plus,
    School,
    Sparkles,
    UsersRound,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';

type AcademicYear = {
    id: number;
    year: number;
    name: string;
    starts_at: string | null;
    ends_at: string | null;
    is_active: boolean;
    courses_count: number;
    enrollments_count: number;
};

type Grade = {
    id: number;
    name: string;
    code: string;
    order: number;
    is_active: boolean;
};

type Level = {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    grades: Grade[];
};

type Modality = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    is_active: boolean;
};

type StudyPlan = {
    id: number;
    name: string;
    code: string;
    level_id: number;
    level: string | null;
    effective_from_year: number | null;
    effective_to_year: number | null;
    is_active: boolean;
    subjects_count: number;

    subjects: {
        id: number;
        order: number;
        is_active: boolean;

        grade: {
            id: number;
            name: string;
        };

        subject: {
            id: number;
            name: string;
        };

        modality: {
            id: number;
            name: string;
        } | null;
    }[];
};

type Division = {
    id: number;
    name: string;
    shift: string | null;
    is_active: boolean;
};

type Course = {
    id: number;
    name: string;
    is_active: boolean;

    academic_year: {
        id: number;
        year: number;
        name: string;
    } | null;

    grade: {
        id: number;
        name: string;
        level_id: number;
        level: string | null;
    } | null;

    study_plan: {
        id: number;
        name: string;
    } | null;

    divisions: Division[];
};

type Subject = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    is_active: boolean;
};

const props = defineProps<{
    canManage: boolean;

    summary: {
        academic_years: number;
        courses: number;
        divisions: number;
        subjects: number;
        study_plans: number;
        teachings: number;
    };

    academicYears: AcademicYear[];
    levels: Level[];
    modalities: Modality[];
    studyPlans: StudyPlan[];
    courses: Course[];
    subjects: Subject[];
}>();

const showAcademicYearForm = ref(false);
const showCourseForm = ref(false);
const showDivisionForm = ref(false);
const showSubjectForm = ref(false);
const showStudyPlanForm = ref(false);
const showStudyPlanSubjectForm = ref(false);

const academicYearForm = useForm({
    year: new Date().getFullYear(),
    name: `Ciclo Lectivo ${new Date().getFullYear()}`,
    starts_at: '',
    ends_at: '',
    is_active: true,
});

const courseForm = useForm({
    academic_year_id: '',
    grade_id: '',
    study_plan_id: '',
    name: '',
    is_active: true,
});

const divisionForm = useForm({
    course_id: '',
    name: '',
    shift: '',
    is_active: true,
});

const subjectForm = useForm({
    name: '',
    code: '',
    description: '',
    is_active: true,
});

const studyPlanForm = useForm({
    level_id: '',
    name: '',
    code: '',
    effective_from_year: '',
    effective_to_year: '',
    is_active: true,
});

const studyPlanSubjectForm = useForm({
    study_plan_id: '',
    grade_id: '',
    subject_id: '',
    modality_id: '',
    order: 1,
    is_active: true,
});

const allGrades = computed(() => {
    return props.levels.flatMap((level) => {
        return level.grades.map((grade) => ({
            ...grade,
            level_id: level.id,
            level_name: level.name,
        }));
    });
});

const selectedGrade = computed(() => {
    return allGrades.value.find(
        (grade) =>
            grade.id === Number(courseForm.grade_id),
    );
});

const availableStudyPlans = computed(() => {
    if (!selectedGrade.value) {
        return props.studyPlans;
    }

    return props.studyPlans.filter(
        (plan) =>
            plan.level_id === selectedGrade.value?.level_id,
    );
});

const selectedStudyPlan = computed(() => {
    return props.studyPlans.find(
        (plan) =>
            plan.id === Number(
                studyPlanSubjectForm.study_plan_id,
            ),
    );
});

const availableGradesForPlan = computed(() => {
    if (!selectedStudyPlan.value) {
        return [];
    }

    const level = props.levels.find(
        (level) =>
            level.id === selectedStudyPlan.value?.level_id,
    );

    return level?.grades ?? [];
});

const submitAcademicYear = () => {
    academicYearForm.post(
        route('academic.years.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                academicYearForm.reset();

                const currentYear =
                    new Date().getFullYear();

                academicYearForm.year =
                    currentYear;

                academicYearForm.name =
                    `Ciclo Lectivo ${currentYear}`;

                academicYearForm.is_active =
                    true;

                showAcademicYearForm.value =
                    false;
            },
        },
    );
};

const submitCourse = () => {
    courseForm.post(
        route('academic.courses.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                courseForm.reset();
                courseForm.is_active = true;
                showCourseForm.value = false;
            },
        },
    );
};

const submitDivision = () => {
    divisionForm.post(
        route('academic.divisions.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                divisionForm.reset();
                divisionForm.is_active = true;
                showDivisionForm.value = false;
            },
        },
    );
};

const fillCourseName = () => {
    if (
        selectedGrade.value &&
        !courseForm.name
    ) {
        courseForm.name =
            selectedGrade.value.name;
    }

    courseForm.study_plan_id = '';
};

const formatDate = (
    value: string | null,
) => {
    if (!value) {
        return 'Sin definir';
    }

    return new Intl.DateTimeFormat(
        'es-AR',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            timeZone: 'UTC',
        },
    ).format(
        new Date(`${value}T00:00:00Z`),
    );
};

const submitSubject = () => {
    subjectForm.post(
        route('academic.subjects.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                subjectForm.reset();
                subjectForm.is_active = true;
                showSubjectForm.value = false;
            },
        },
    );
};

const submitStudyPlan = () => {
    studyPlanForm.post(
        route('academic.study-plans.store'),
        {
            preserveScroll: true,

            onSuccess: () => {
                studyPlanForm.reset();
                studyPlanForm.is_active = true;
                showStudyPlanForm.value = false;
            },
        },
    );
};

const submitStudyPlanSubject = () => {
    studyPlanSubjectForm.post(
        route(
            'academic.study-plan-subjects.store',
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                studyPlanSubjectForm.reset();
                studyPlanSubjectForm.order = 1;
                studyPlanSubjectForm.is_active = true;
            },
        },
    );
};
</script>

<template>
    <Head title="Académico" />

    <AuthenticatedLayout header="Académico">
        <div
            class="mx-auto max-w-7xl space-y-8"
        >
            <!-- HERO -->
            <section
                class="relative overflow-hidden rounded-3xl bg-[#071a35] px-7 py-8 text-white shadow-sm sm:px-9"
            >
                <div
                    class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl"
                />

                <div
                    class="absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <div
                            class="flex items-center gap-2 text-sm font-semibold text-cyan-300"
                        >
                            <School
                                class="h-4 w-4"
                            />

                            Gestión académica
                        </div>

                        <h2
                            class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Estructura académica
                            ISAE
                        </h2>

                        <p
                            class="mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base"
                        >
                            Administrá ciclos
                            lectivos, niveles,
                            cursos, divisiones,
                            modalidades y planes
                            de estudio.
                        </p>
                    </div>

                    <div
                        v-if="canManage"
                        class="flex items-center gap-2 rounded-2xl border border-cyan-300/15 bg-cyan-400/10 px-4 py-3"
                    >
                        <CheckCircle2
                            class="h-5 w-5 text-cyan-300"
                        />

                        <div>
                            <p
                                class="text-sm font-semibold text-white"
                            >
                                Acceso de gestión
                            </p>

                            <p
                                class="text-xs text-slate-400"
                            >
                                Podés administrar
                                la estructura
                                académica
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RESUMEN -->
            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6"
            >
                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <CalendarDays
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{
                            summary.academic_years
                        }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Ciclos lectivos
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <School
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{ summary.courses }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Cursos
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <Layers3
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{
                            summary.divisions
                        }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Divisiones
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <BookOpen
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{
                            summary.subjects
                        }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Materias
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <LibraryBig
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{
                            summary.study_plans
                        }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Planes
                    </p>
                </article>

                <article
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <GraduationCap
                        class="h-5 w-5 text-cyan-700"
                    />

                    <p
                        class="mt-4 text-3xl font-bold text-slate-950"
                    >
                        {{
                            summary.teachings
                        }}
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Cursadas
                    </p>
                </article>
            </section>

            <!-- CICLOS -->
            <section
                class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold text-cyan-700"
                        >
                            Ciclos lectivos
                        </p>

                        <h3
                            class="mt-1 text-xl font-bold text-slate-950"
                        >
                            Años académicos
                        </h3>
                    </div>

                    <button
                        v-if="canManage"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#071a35] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        @click="
                            showAcademicYearForm =
                                !showAcademicYearForm
                        "
                    >
                        <X
                            v-if="
                                showAcademicYearForm
                            "
                            class="h-4 w-4"
                        />

                        <Plus
                            v-else
                            class="h-4 w-4"
                        />

                        {{
                            showAcademicYearForm
                                ? 'Cancelar'
                                : 'Nuevo ciclo'
                        }}
                    </button>
                </div>

                <form
                    v-if="
                        canManage &&
                        showAcademicYearForm
                    "
                    class="border-b border-slate-100 bg-slate-50/70 p-6"
                    @submit.prevent="
                        submitAcademicYear
                    "
                >
                    <div
                        class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Año
                            </label>

                            <input
                                v-model.number="
                                    academicYearForm.year
                                "
                                type="number"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            />

                            <p
                                v-if="
                                    academicYearForm
                                        .errors.year
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    academicYearForm
                                        .errors.year
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Nombre
                            </label>

                            <input
                                v-model="
                                    academicYearForm.name
                                "
                                type="text"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            />

                            <p
                                v-if="
                                    academicYearForm
                                        .errors.name
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    academicYearForm
                                        .errors.name
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Inicio
                            </label>

                            <input
                                v-model="
                                    academicYearForm.starts_at
                                "
                                type="date"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            />
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Finalización
                            </label>

                            <input
                                v-model="
                                    academicYearForm.ends_at
                                "
                                type="date"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <label
                            class="flex items-center gap-3 text-sm text-slate-700"
                        >
                            <input
                                v-model="
                                    academicYearForm.is_active
                                "
                                type="checkbox"
                                class="rounded border-slate-300"
                            />

                            Marcar como ciclo
                            lectivo activo
                        </label>

                        <button
                            type="submit"
                            :disabled="
                                academicYearForm.processing
                            "
                            class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700 disabled:opacity-50"
                        >
                            Crear ciclo
                        </button>
                    </div>
                </form>

                <div
                    v-if="academicYears.length"
                    class="divide-y divide-slate-100"
                >
                    <div
                        v-for="
                            academicYear in academicYears
                        "
                        :key="
                            academicYear.id
                        "
                        class="grid gap-4 px-6 py-5 md:grid-cols-[1fr_auto_auto]"
                    >
                        <div>
                            <div
                                class="flex flex-wrap items-center gap-2"
                            >
                                <p
                                    class="font-semibold text-slate-950"
                                >
                                    {{
                                        academicYear.name
                                    }}
                                </p>

                                <span
                                    v-if="
                                        academicYear.is_active
                                    "
                                    class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"
                                >
                                    Activo
                                </span>
                            </div>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                {{
                                    formatDate(
                                        academicYear.starts_at,
                                    )
                                }}
                                —
                                {{
                                    formatDate(
                                        academicYear.ends_at,
                                    )
                                }}
                            </p>
                        </div>

                        <div
                            class="text-sm text-slate-500"
                        >
                            <span
                                class="font-semibold text-slate-800"
                            >
                                {{
                                    academicYear.courses_count
                                }}
                            </span>
                            cursos
                        </div>

                        <div
                            class="text-sm text-slate-500"
                        >
                            <span
                                class="font-semibold text-slate-800"
                            >
                                {{
                                    academicYear.enrollments_count
                                }}
                            </span>
                            matrículas
                        </div>
                    </div>
                </div>
            </section>

            <!-- NIVELES + MODALIDADES -->
            <section
                class="grid gap-6 xl:grid-cols-2"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 px-6 py-5"
                    >
                        <p
                            class="text-sm font-semibold text-cyan-700"
                        >
                            Estructura
                        </p>

                        <h3
                            class="mt-1 text-xl font-bold text-slate-950"
                        >
                            Niveles, grados y
                            años
                        </h3>
                    </div>

                    <div
                        class="space-y-4 p-6"
                    >
                        <div
                            v-for="
                                level in levels
                            "
                            :key="level.id"
                            class="rounded-2xl border border-slate-200 p-5"
                        >
                            <div
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p
                                        class="font-bold text-slate-950"
                                    >
                                        {{
                                            level.name
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        {{
                                            level.code
                                        }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                                >
                                    {{
                                        level
                                            .grades
                                            .length
                                    }}
                                </span>
                            </div>

                            <div
                                class="mt-4 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="
                                        grade in level.grades
                                    "
                                    :key="
                                        grade.id
                                    "
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700"
                                >
                                    {{
                                        grade.name
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 px-6 py-5"
                    >
                        <p
                            class="text-sm font-semibold text-cyan-700"
                        >
                            Secundaria
                        </p>

                        <h3
                            class="mt-1 text-xl font-bold text-slate-950"
                        >
                            Modalidades
                        </h3>
                    </div>

                    <div
                        class="space-y-3 p-6"
                    >
                        <div
                            v-for="
                                modality in modalities
                            "
                            :key="
                                modality.id
                            "
                            class="rounded-2xl border border-slate-200 p-5"
                        >
                            <div
                                class="flex items-start gap-4"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700"
                                >
                                    <Sparkles
                                        class="h-5 w-5"
                                    />
                                </div>

                                <div>
                                    <p
                                        class="font-semibold text-slate-950"
                                    >
                                        {{
                                            modality.name
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-sm leading-6 text-slate-500"
                                    >
                                        {{
                                            modality.description ??
                                            'Sin descripción.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CURSOS -->
            <section
                class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold text-cyan-700"
                        >
                            Organización anual
                        </p>

                        <h3
                            class="mt-1 text-xl font-bold text-slate-950"
                        >
                            Cursos y divisiones
                        </h3>
                    </div>

                    <div
                        v-if="canManage"
                        class="flex flex-wrap gap-2"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            @click="
                                showCourseForm =
                                    !showCourseForm
                            "
                        >
                            <Plus
                                class="h-4 w-4"
                            />

                            Nuevo curso
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#071a35] px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800"
                            @click="
                                showDivisionForm =
                                    !showDivisionForm
                            "
                        >
                            <Plus
                                class="h-4 w-4"
                            />

                            Nueva división
                        </button>
                    </div>
                </div>

                <!-- FORM CURSO -->
                <form
                    v-if="
                        canManage &&
                        showCourseForm
                    "
                    class="border-b border-slate-100 bg-slate-50/70 p-6"
                    @submit.prevent="
                        submitCourse
                    "
                >
                    <h4
                        class="font-bold text-slate-900"
                    >
                        Crear curso
                    </h4>

                    <div
                        class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Ciclo lectivo
                            </label>

                            <select
                                v-model="
                                    courseForm.academic_year_id
                                "
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option
                                    v-for="
                                        academicYear in academicYears
                                    "
                                    :key="
                                        academicYear.id
                                    "
                                    :value="
                                        academicYear.id
                                    "
                                >
                                    {{
                                        academicYear.name
                                    }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Grado / Año
                            </label>

                            <select
                                v-model="
                                    courseForm.grade_id
                                "
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                                @change="
                                    fillCourseName
                                "
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option
                                    v-for="
                                        grade in allGrades
                                    "
                                    :key="
                                        grade.id
                                    "
                                    :value="
                                        grade.id
                                    "
                                >
                                    {{
                                        grade.level_name
                                    }}
                                    ·
                                    {{
                                        grade.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    courseForm
                                        .errors
                                        .grade_id
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    courseForm
                                        .errors
                                        .grade_id
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Plan de estudio
                            </label>

                            <select
                                v-model="
                                    courseForm.study_plan_id
                                "
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Sin asignar
                                </option>

                                <option
                                    v-for="
                                        plan in availableStudyPlans
                                    "
                                    :key="
                                        plan.id
                                    "
                                    :value="
                                        plan.id
                                    "
                                >
                                    {{
                                        plan.name
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="
                                    courseForm
                                        .errors
                                        .study_plan_id
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    courseForm
                                        .errors
                                        .study_plan_id
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Nombre
                            </label>

                            <input
                                v-model="
                                    courseForm.name
                                "
                                type="text"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                                placeholder="3° Año"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-5 flex justify-end"
                    >
                        <button
                            type="submit"
                            :disabled="
                                courseForm.processing
                            "
                            class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 disabled:opacity-50"
                        >
                            Crear curso
                        </button>
                    </div>
                </form>

                <!-- FORM DIVISION -->
                <form
                    v-if="
                        canManage &&
                        showDivisionForm
                    "
                    class="border-b border-slate-100 bg-slate-50/70 p-6"
                    @submit.prevent="
                        submitDivision
                    "
                >
                    <h4
                        class="font-bold text-slate-900"
                    >
                        Crear división
                    </h4>

                    <div
                        class="mt-5 grid gap-5 md:grid-cols-3"
                    >
                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Curso
                            </label>

                            <select
                                v-model="
                                    divisionForm.course_id
                                "
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Seleccionar
                                </option>

                                <option
                                    v-for="
                                        course in courses
                                    "
                                    :key="
                                        course.id
                                    "
                                    :value="
                                        course.id
                                    "
                                >
                                    {{
                                        course
                                            .academic_year
                                            ?.year
                                    }}
                                    ·
                                    {{
                                        course
                                            .grade
                                            ?.level
                                    }}
                                    ·
                                    {{
                                        course
                                            .grade
                                            ?.name
                                    }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                División
                            </label>

                            <input
                                v-model="
                                    divisionForm.name
                                "
                                type="text"
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                                placeholder="A"
                            />

                            <p
                                v-if="
                                    divisionForm
                                        .errors.name
                                "
                                class="mt-1 text-xs text-red-600"
                            >
                                {{
                                    divisionForm
                                        .errors.name
                                }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="text-sm font-semibold text-slate-700"
                            >
                                Turno
                            </label>

                            <select
                                v-model="
                                    divisionForm.shift
                                "
                                class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                            >
                                <option value="">
                                    Sin definir
                                </option>

                                <option value="Mañana">
                                    Mañana
                                </option>

                                <option value="Tarde">
                                    Tarde
                                </option>

                                <option value="Vespertino">
                                    Vespertino
                                </option>

                                <option value="Noche">
                                    Noche
                                </option>
                            </select>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex justify-end"
                    >
                        <button
                            type="submit"
                            :disabled="
                                divisionForm.processing
                            "
                            class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-cyan-700 disabled:opacity-50"
                        >
                            Crear división
                        </button>
                    </div>
                </form>

                <!-- LISTADO -->
                <div
                    v-if="courses.length"
                    class="divide-y divide-slate-100"
                >
                    <div
                        v-for="
                            course in courses
                        "
                        :key="course.id"
                        class="px-6 py-5"
                    >
                        <div
                            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div>
                                <div
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    <p
                                        class="font-bold text-slate-950"
                                    >
                                        {{
                                            course
                                                .grade
                                                ?.level ??
                                            'Nivel'
                                        }}
                                        ·
                                        {{
                                            course
                                                .grade
                                                ?.name ??
                                            course.name
                                        }}
                                    </p>

                                    <span
                                        v-if="
                                            course.academic_year
                                        "
                                        class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700"
                                    >
                                        {{
                                            course
                                                .academic_year
                                                .year
                                        }}
                                    </span>
                                </div>

                                <p
                                    class="mt-1 text-sm text-slate-500"
                                >
                                    Plan:
                                    {{
                                        course
                                            .study_plan
                                            ?.name ??
                                        'Sin plan asignado'
                                    }}
                                </p>
                            </div>

                            <div
                                class="flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="
                                        division in course.divisions
                                    "
                                    :key="
                                        division.id
                                    "
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
                                >
                                    <ChevronRight
                                        class="h-3.5 w-3.5 text-slate-400"
                                    />

                                    División
                                    {{
                                        division.name
                                    }}

                                    <span
                                        v-if="
                                            division.shift
                                        "
                                        class="text-slate-400"
                                    >
                                        ·
                                        {{
                                            division.shift
                                        }}
                                    </span>
                                </span>

                                <span
                                    v-if="
                                        !course
                                            .divisions
                                            .length
                                    "
                                    class="text-sm text-slate-400"
                                >
                                    Sin divisiones
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="px-6 py-10 text-center"
                >
                    <p
                        class="font-semibold text-slate-700"
                    >
                        Todavía no hay cursos
                        creados
                    </p>

                    <p
                        class="mt-2 text-sm text-slate-500"
                    >
                        Creá el primer curso del
                        ciclo lectivo.
                    </p>
                </div>
            </section>

            <!-- PLANES -->
            <section
                class="rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="border-b border-slate-100 px-6 py-5"
                >
                    <p
                        class="text-sm font-semibold text-cyan-700"
                    >
                        Planificación
                    </p>

                    <h3
                        class="mt-1 text-xl font-bold text-slate-950"
                    >
                        Planes de estudio
                    </h3>
                    
                </div>
                

                <div
                    v-if="studyPlans.length"
                    class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="
                            plan in studyPlans
                        "
                        :key="plan.id"
                        class="rounded-2xl border border-slate-200 p-5"
                    >
                        <div
                            class="flex items-start justify-between gap-3"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <LibraryBig
                                    class="h-5 w-5"
                                />
                            </div>

                            <span
                                v-if="
                                    plan.is_active
                                "
                                class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700"
                            >
                                Activo
                            </span>
                        </div>

                        <h4
                            class="mt-4 font-bold text-slate-950"
                        >
                            {{ plan.name }}
                        </h4>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{
                                plan.level ??
                                'Sin nivel'
                            }}
                        </p>

                        <div
                            class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"
                        >
                            <span
                                class="text-sm text-slate-500"
                            >
                                Materias
                            </span>

                            <span
                                class="font-bold text-slate-900"
                            >
                                {{
                                    plan.subjects_count
                                }}
                            </span>
                        </div>
                    </article>
                </div>

                <div
                    v-else
                    class="px-6 py-10 text-center text-sm text-slate-500"
                >
                    Todavía no hay planes de
                    estudio cargados.
                </div>
            </section>

            <section
    class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]"
>
    <!-- MATERIAS -->
    <div
        class="rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <div
            class="flex items-center justify-between border-b border-slate-100 px-6 py-5"
        >
            <div>
                <p
                    class="text-sm font-semibold text-cyan-700"
                >
                    Catálogo
                </p>

                <h3
                    class="mt-1 text-xl font-bold text-slate-950"
                >
                    Materias
                </h3>
            </div>

            <button
                v-if="canManage"
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-[#071a35] px-4 py-2.5 text-sm font-semibold text-white"
                @click="
                    showSubjectForm =
                        !showSubjectForm
                "
            >
                <Plus class="h-4 w-4" />

                Nueva materia
            </button>
        </div>

        <form
            v-if="
                canManage &&
                showSubjectForm
            "
            class="border-b border-slate-100 bg-slate-50/70 p-6"
            @submit.prevent="
                submitSubject
            "
        >
            <div class="space-y-4">
                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Nombre
                    </label>

                    <input
                        v-model="
                            subjectForm.name
                        "
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="Matemática"
                    />
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Código
                    </label>

                    <input
                        v-model="
                            subjectForm.code
                        "
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="matematica"
                    />

                    <p
                        v-if="
                            subjectForm
                                .errors.code
                        "
                        class="mt-1 text-xs text-red-600"
                    >
                        {{
                            subjectForm
                                .errors.code
                        }}
                    </p>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Descripción
                    </label>

                    <textarea
                        v-model="
                            subjectForm.description
                        "
                        rows="3"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="
                        subjectForm.processing
                    "
                    class="w-full rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white"
                >
                    Crear materia
                </button>
            </div>
        </form>

        <div
            v-if="subjects.length"
            class="divide-y divide-slate-100"
        >
            <div
                v-for="
                    subject in subjects
                "
                :key="subject.id"
                class="px-6 py-4"
            >
                <p
                    class="font-semibold text-slate-950"
                >
                    {{ subject.name }}
                </p>

                <p
                    class="mt-1 text-xs text-slate-400"
                >
                    {{ subject.code }}
                </p>
            </div>
        </div>

        <div
            v-else
            class="px-6 py-10 text-center text-sm text-slate-500"
        >
            No hay materias cargadas.
        </div>
    </div>

    <!-- PLANES -->
    <div
        class="rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5"
        >
            <div>
                <p
                    class="text-sm font-semibold text-cyan-700"
                >
                    Planificación
                </p>

                <h3
                    class="mt-1 text-xl font-bold text-slate-950"
                >
                    Gestión de planes
                </h3>
            </div>

            <div
                v-if="canManage"
                class="flex gap-2"
            >
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700"
                    @click="
                        showStudyPlanForm =
                            !showStudyPlanForm
                    "
                >
                    Nuevo plan
                </button>

                <button
                    type="button"
                    class="rounded-xl bg-[#071a35] px-4 py-2.5 text-sm font-semibold text-white"
                    @click="
                        showStudyPlanSubjectForm =
                            !showStudyPlanSubjectForm
                    "
                >
                    Agregar materia
                </button>
            </div>
        </div>

        <!-- NUEVO PLAN -->
        <form
            v-if="
                canManage &&
                showStudyPlanForm
            "
            class="border-b border-slate-100 bg-slate-50/70 p-6"
            @submit.prevent="
                submitStudyPlan
            "
        >
            <div
                class="grid gap-4 md:grid-cols-2"
            >
                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Nivel
                    </label>

                    <select
                        v-model="
                            studyPlanForm.level_id
                        "
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <option
                            v-for="
                                level in levels
                            "
                            :key="level.id"
                            :value="level.id"
                        >
                            {{ level.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Nombre
                    </label>

                    <input
                        v-model="
                            studyPlanForm.name
                        "
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="Plan Secundario"
                    />
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Código
                    </label>

                    <input
                        v-model="
                            studyPlanForm.code
                        "
                        type="text"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="plan-secundario"
                    />
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Vigente desde
                    </label>

                    <input
                        v-model="
                            studyPlanForm.effective_from_year
                        "
                        type="number"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                        placeholder="2026"
                    />
                </div>
            </div>

            <button
                type="submit"
                class="mt-5 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white"
            >
                Crear plan
            </button>
        </form>

        <!-- AGREGAR MATERIA -->
        <form
            v-if="
                canManage &&
                showStudyPlanSubjectForm
            "
            class="border-b border-slate-100 bg-slate-50/70 p-6"
            @submit.prevent="
                submitStudyPlanSubject
            "
        >
            <div
                class="grid gap-4 md:grid-cols-2"
            >
                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Plan
                    </label>

                    <select
                        v-model="
                            studyPlanSubjectForm.study_plan_id
                        "
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <option
                            v-for="
                                plan in studyPlans
                            "
                            :key="plan.id"
                            :value="plan.id"
                        >
                            {{ plan.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Grado / Año
                    </label>

                    <select
                        v-model="
                            studyPlanSubjectForm.grade_id
                        "
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <option
                            v-for="
                                grade in availableGradesForPlan
                            "
                            :key="grade.id"
                            :value="grade.id"
                        >
                            {{ grade.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Materia
                    </label>

                    <select
                        v-model="
                            studyPlanSubjectForm.subject_id
                        "
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">
                            Seleccionar
                        </option>

                        <option
                            v-for="
                                subject in subjects
                            "
                            :key="subject.id"
                            :value="subject.id"
                        >
                            {{ subject.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Modalidad
                    </label>

                    <select
                        v-model="
                            studyPlanSubjectForm.modality_id
                        "
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    >
                        <option value="">
                            Común
                        </option>

                        <option
                            v-for="
                                modality in modalities
                            "
                            :key="modality.id"
                            :value="
                                modality.id
                            "
                        >
                            {{
                                modality.name
                            }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="text-sm font-semibold text-slate-700"
                    >
                        Orden
                    </label>

                    <input
                        v-model.number="
                            studyPlanSubjectForm.order
                        "
                        type="number"
                        min="0"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"
                    />
                </div>
            </div>

            <button
                type="submit"
                class="mt-5 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white"
            >
                Agregar al plan
            </button>
        </form>

        <!-- CONTENIDO PLANES -->
        <div
            v-if="studyPlans.length"
            class="space-y-4 p-6"
        >
            <article
                v-for="
                    plan in studyPlans
                "
                :key="plan.id"
                class="rounded-2xl border border-slate-200 p-5"
            >
                <div
                    class="flex items-center justify-between gap-4"
                >
                    <div>
                        <h4
                            class="font-bold text-slate-950"
                        >
                            {{ plan.name }}
                        </h4>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            {{ plan.level }}
                        </p>
                    </div>

                    <span
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"
                    >
                        {{
                            plan.subjects_count
                        }}
                        materias
                    </span>
                </div>

                <div
                    v-if="
                        plan.subjects.length
                    "
                    class="mt-5 space-y-2"
                >
                    <div
                        v-for="
                            item in plan.subjects
                        "
                        :key="item.id"
                        class="flex flex-col gap-1 rounded-xl bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold text-slate-900"
                            >
                                {{
                                    item.grade
                                        .name
                                }}
                                ·
                                {{
                                    item.subject
                                        .name
                                }}
                            </p>

                            <p
                                class="mt-1 text-xs text-slate-500"
                            >
                                {{
                                    item.modality
                                        ?.name ??
                                    'Materia común'
                                }}
                            </p>
                        </div>

                        <span
                            class="text-xs font-semibold text-slate-400"
                        >
                            Orden
                            {{ item.order }}
                        </span>
                    </div>
                </div>

                <p
                    v-else
                    class="mt-4 text-sm text-slate-400"
                >
                    Sin materias asignadas.
                </p>
            </article>
        </div>
    </div>
</section>

        </div>
    </AuthenticatedLayout>
</template>