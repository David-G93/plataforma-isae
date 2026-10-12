<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Check,
    RefreshCw,
    Save,
    UsersRound,
} from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';

type AcademicYear = {
    id: number;
    year: number;
    name: string;
    is_active: boolean;
};

type Teacher = {
    id: number;
    name: string;
};

type Teaching = {
    id: number;

    subject: {
        id: number;
        name: string;
    };

    modality: {
        id: number;
        name: string;
    } | null;

    division: {
        id: number;
        name: string;
        shift: string | null;
    };

    course: {
        id: number;
        name: string;
        grade: string;
        level: string;
    };

    teachers: Teacher[];

    eligible_students_count: number;
};

const props = defineProps<{
    canManage: boolean;
    academicYears: AcademicYear[];
    selectedYear: number;
    teachers: Teacher[];
    teachings: Teaching[];
}>();

const page = usePage();

const generating = ref(false);
const savingTeachingId = ref<number | null>(null);

const teacherSelections = reactive<
    Record<number, number[]>
>({});

const syncTeacherSelections = () => {
    props.teachings.forEach((teaching) => {
        teacherSelections[teaching.id] =
            teaching.teachers.map(
                (teacher) => teacher.id,
            );
    });
};

syncTeacherSelections();

watch(
    () => props.teachings,
    () => {
        syncTeacherSelections();
    },
    {
        deep: true,
    },
);

const groupedTeachings = computed(() => {
    const groups = new Map<
        number,
        {
            division: Teaching['division'];
            course: Teaching['course'];
            teachings: Teaching[];
        }
    >();

    props.teachings.forEach((teaching) => {
        const key = teaching.division.id;

        if (!groups.has(key)) {
            groups.set(key, {
                division: teaching.division,
                course: teaching.course,
                teachings: [],
            });
        }

        groups.get(key)?.teachings.push(
            teaching,
        );
    });

    return Array.from(groups.values());
});

const successMessage = computed(() => {
    return (page.props.flash as any)?.success ?? null;
});

const generationError = computed(() => {
    return (page.props.errors as any)?.generation ?? null;
});

const changeYear = (
    event: Event,
) => {
    const year = Number(
        (event.target as HTMLSelectElement).value,
    );

    router.get(
        route('academic.teachings.index'),
        {
            year,
        },
        {
            preserveState: false,
            preserveScroll: false,
        },
    );
};

const generateTeachings = () => {
    generating.value = true;

    router.post(
        route('academic.teachings.generate'),
        {
            year: props.selectedYear,
        },
        {
            preserveScroll: true,

            onFinish: () => {
                generating.value = false;
            },
        },
    );
};

const saveTeachers = (
    teachingId: number,
) => {
    savingTeachingId.value = teachingId;

    router.put(
        route(
            'academic.teachings.teachers.update',
            teachingId,
        ),
        {
            teacher_profile_ids:
                teacherSelections[teachingId]
                ?? [],
        },
        {
            preserveScroll: true,

            onFinish: () => {
                savingTeachingId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Dictados" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <section
                class="overflow-hidden rounded-3xl bg-[#071a35] p-6 text-white shadow-xl sm:p-8"
            >
                <div
                    class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div
                            class="mb-3 flex items-center gap-2 text-sm font-medium text-cyan-300"
                        >
                            <BookOpen class="h-4 w-4" />

                            Académico
                        </div>

                        <h1
                            class="text-3xl font-bold tracking-tight"
                        >
                            Dictados
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-300"
                        >
                            Materias efectivamente dictadas por
                            división, docentes responsables y
                            alumnos incluidos automáticamente.
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-3 sm:flex-row"
                    >
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

                        <button
                            v-if="canManage"
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="generating"
                            @click="generateTeachings"
                        >
                            <RefreshCw
                                :class="[
                                    'h-4 w-4',
                                    generating
                                        ? 'animate-spin'
                                        : '',
                                ]"
                            />

                            {{
                                generating
                                    ? 'Generando...'
                                    : 'Generar / actualizar'
                            }}
                        </button>
                    </div>
                </div>
            </section>

            <div
                v-if="successMessage"
                class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                <Check class="h-5 w-5" />

                {{ successMessage }}
            </div>

            <div
                v-if="generationError"
                class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
            >
                {{ generationError }}
            </div>

            <div
                v-if="groupedTeachings.length === 0"
                class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"
            >
                <BookOpen
                    class="mx-auto h-10 w-10 text-slate-300"
                />

                <h2
                    class="mt-4 text-lg font-semibold text-slate-800"
                >
                    Todavía no hay dictados
                </h2>

                <p
                    class="mt-2 text-sm text-slate-500"
                >
                    Generá los dictados desde los planes de
                    estudio del ciclo seleccionado.
                </p>
            </div>

            <section
                v-for="group in groupedTeachings"
                :key="group.division.id"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
            >
                <header
                    class="border-b border-slate-200 bg-slate-50 px-6 py-5"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-wider text-cyan-700"
                            >
                                {{ group.course.level }}
                            </p>

                            <h2
                                class="mt-1 text-xl font-bold text-slate-900"
                            >
                                {{ group.course.name }}
                            </h2>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                División
                                {{ group.division.name }}

                                <span
                                    v-if="group.division.shift"
                                >
                                    ·
                                    {{
                                        group.division
                                            .shift
                                    }}
                                </span>
                            </p>
                        </div>

                        <div
                            class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                        >
                            {{
                                group.teachings.length
                            }}
                            materias
                        </div>
                    </div>
                </header>

                <div class="divide-y divide-slate-100">
                    <article
                        v-for="teaching in group.teachings"
                        :key="teaching.id"
                        class="grid gap-5 p-6 xl:grid-cols-[1.1fr_0.7fr_2fr_auto]"
                    >
                        <div>
                            <p
                                class="font-semibold text-slate-900"
                            >
                                {{
                                    teaching.subject.name
                                }}
                            </p>

                            <span
                                v-if="teaching.modality"
                                class="mt-2 inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700"
                            >
                                {{
                                    teaching.modality
                                        .name
                                }}
                            </span>

                            <span
                                v-else
                                class="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                            >
                                Común
                            </span>
                        </div>

                        <div>
                            <p
                                class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Alumnos
                            </p>

                            <div
                                class="inline-flex items-center gap-2 rounded-xl bg-cyan-50 px-3 py-2 font-semibold text-cyan-800"
                            >
                                <UsersRound
                                    class="h-4 w-4"
                                />

                                {{
                                    teaching.eligible_students_count
                                }}
                            </div>
                        </div>

                        <div>
                            <p
                                class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400"
                            >
                                Docentes
                            </p>

                            <select
                                v-model="
                                    teacherSelections[
                                        teaching.id
                                    ]
                                "
                                multiple
                                :disabled="!canManage"
                                class="min-h-28 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 disabled:bg-slate-50"
                            >
                                <option
                                    v-for="teacher in teachers"
                                    :key="teacher.id"
                                    :value="teacher.id"
                                >
                                    {{ teacher.name }}
                                </option>
                            </select>

                            <p
                                class="mt-2 text-xs text-slate-400"
                            >
                                Podés seleccionar uno o más
                                docentes.
                            </p>
                        </div>

                        <div
                            v-if="canManage"
                            class="flex items-end"
                        >
                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:opacity-60 xl:w-auto"
                                :disabled="
                                    savingTeachingId ===
                                    teaching.id
                                "
                                @click="
                                    saveTeachers(
                                        teaching.id,
                                    )
                                "
                            >
                                <Save class="h-4 w-4" />

                                {{
                                    savingTeachingId ===
                                    teaching.id
                                        ? 'Guardando...'
                                        : 'Guardar'
                                }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>