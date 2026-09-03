<script setup lang="ts">
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useForm } from '@inertiajs/vue3';
import {
    Save,
    Search,
    Trash2,
    UserPlus,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';

interface StudentOption {
    id: number;
    person_id: number;
    dni: string;
    full_name: string;
}

interface LinkedStudent {
    id: number;
    person_id: number;
    dni: string;
    full_name: string;
    relationship: string | null;
    is_primary: boolean;
    authorized_pickup: boolean;
    receives_communications: boolean;
}

const props = defineProps<{
    guardianProfileId: number;
    students: LinkedStudent[];
    availableStudents: StudentOption[];
}>();

const search = ref('');

const form = useForm({
    students: props.students.map((student) => ({
        student_profile_id: student.id,
        full_name: student.full_name,
        dni: student.dni,
        relationship: student.relationship ?? '',
        is_primary: student.is_primary,
        authorized_pickup: student.authorized_pickup,
        receives_communications: student.receives_communications,
    })),
});

const filteredStudents = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return [];
    }

    const linkedIds = form.students.map(
        (student) => student.student_profile_id,
    );

    return props.availableStudents
        .filter(
            (student) =>
                !linkedIds.includes(student.id) &&
                (
                    student.full_name.toLowerCase().includes(query) ||
                    student.dni.toLowerCase().includes(query)
                ),
        )
        .slice(0, 8);
});

const addStudent = (student: StudentOption) => {
    form.students.push({
        student_profile_id: student.id,
        full_name: student.full_name,
        dni: student.dni,
        relationship: '',
        is_primary: false,
        authorized_pickup: false,
        receives_communications: true,
    });

    search.value = '';
};

const removeStudent = (index: number) => {
    form.students.splice(index, 1);
};

const submit = () => {
    form
        .transform((data) => ({
            students: data.students.map((student) => ({
                student_profile_id: student.student_profile_id,
                relationship: student.relationship,
                is_primary: student.is_primary,
                authorized_pickup: student.authorized_pickup,
                receives_communications: student.receives_communications,
            })),
        }))
        .put(
            route(
                'guardian-students.update',
                props.guardianProfileId,
            ),
            {
                preserveScroll: true,
            },
        );
};
</script>

<template>
    <section
        class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <!-- CABECERA -->
        <div
            class="border-b border-slate-200 px-5 py-5 sm:px-6 lg:px-8"
        >
            <div class="flex items-start gap-4">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#071a35] text-cyan-300"
                >
                    <Users class="h-5 w-5" />
                </div>

                <div>
                    <h3
                        class="text-lg font-bold text-slate-950"
                    >
                        Estudiantes a cargo
                    </h3>

                    <p
                        class="mt-1 text-sm leading-6 text-slate-500"
                    >
                        Vinculá los estudiantes de los que esta persona es
                        responsable.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div class="p-5 sm:p-6 lg:p-8">
                <!-- BUSCADOR -->
                <div class="relative w-full max-w-xl">
                    <Search
                        class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />

                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Buscar estudiante por nombre o DNI..."
                        class="h-11 w-full pl-11"
                    />

                    <!-- RESULTADOS -->
                    <div
                        v-if="filteredStudents.length"
                        class="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="student in filteredStudents"
                            :key="student.id"
                            type="button"
                            class="flex w-full items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50"
                            @click="addStudent(student)"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-semibold text-slate-900"
                                >
                                    {{ student.full_name }}
                                </p>

                                <p
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    DNI {{ student.dni }}
                                </p>
                            </div>

                            <UserPlus
                                class="h-4 w-4 shrink-0 text-cyan-600"
                            />
                        </button>
                    </div>
                </div>

                <!-- SIN VÍNCULOS -->
                <div
                    v-if="form.students.length === 0"
                    class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center"
                >
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm"
                    >
                        <Users class="h-6 w-6 text-slate-400" />
                    </div>

                    <p
                        class="mt-4 font-semibold text-slate-700"
                    >
                        Sin estudiantes vinculados
                    </p>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Buscá un estudiante por nombre o DNI para agregarlo.
                    </p>
                </div>

                <!-- ESTUDIANTES VINCULADOS -->
                <div
                    v-else
                    class="mt-7 space-y-5"
                >
                    <article
                        v-for="(student, index) in form.students"
                        :key="student.student_profile_id"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                    >
                        <!-- encabezado estudiante -->
                        <div
                            class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div
                                class="flex min-w-0 items-center gap-3"
                            >
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#071a35] text-sm font-bold text-cyan-300"
                                >
                                    {{
                                        student.full_name
                                            .split(' ')
                                            .slice(0, 2)
                                            .map((name) => name.charAt(0))
                                            .join('')
                                            .toUpperCase()
                                    }}
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate font-semibold text-slate-950"
                                    >
                                        {{ student.full_name }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs text-slate-500"
                                    >
                                        DNI {{ student.dni }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-9 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 text-sm font-medium text-red-700 transition hover:bg-red-100"
                                @click="removeStudent(index)"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />

                                Quitar
                            </button>
                        </div>

                        <!-- datos del vínculo -->
                        <div class="p-5">
                            <div
                                class="grid gap-5 xl:grid-cols-[minmax(220px,1fr)_auto_auto_auto]"
                            >
                                <!-- parentesco -->
                                <div class="space-y-2">
                                    <Label>
                                        Parentesco / vínculo
                                    </Label>

                                    <Input
                                        v-model="student.relationship"
                                        placeholder="Ej: Madre, Padre, Tutor..."
                                    />
                                </div>

                                <!-- principal -->
                                <label
                                    :class="[
                                        'flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition',
                                        student.is_primary
                                            ? 'border-cyan-300 bg-cyan-50'
                                            : 'border-slate-200 bg-white hover:border-slate-300',
                                    ]"
                                >
                                    <input
                                        v-model="student.is_primary"
                                        type="checkbox"
                                        class="h-4 w-4 shrink-0 accent-cyan-600"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            Principal
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-500"
                                        >
                                            Responsable principal
                                        </p>
                                    </div>
                                </label>

                                <!-- puede retirar -->
                                <label
                                    :class="[
                                        'flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition',
                                        student.authorized_pickup
                                            ? 'border-cyan-300 bg-cyan-50'
                                            : 'border-slate-200 bg-white hover:border-slate-300',
                                    ]"
                                >
                                    <input
                                        v-model="student.authorized_pickup"
                                        type="checkbox"
                                        class="h-4 w-4 shrink-0 accent-cyan-600"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            Puede retirar
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-500"
                                        >
                                            Retiro autorizado
                                        </p>
                                    </div>
                                </label>

                                <!-- comunicaciones -->
                                <label
                                    :class="[
                                        'flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition',
                                        student.receives_communications
                                            ? 'border-cyan-300 bg-cyan-50'
                                            : 'border-slate-200 bg-white hover:border-slate-300',
                                    ]"
                                >
                                    <input
                                        v-model="student.receives_communications"
                                        type="checkbox"
                                        class="h-4 w-4 shrink-0 accent-cyan-600"
                                    />

                                    <div>
                                        <p
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            Comunicaciones
                                        </p>

                                        <p
                                            class="mt-0.5 text-xs text-slate-500"
                                        >
                                            Recibe avisos
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </article>
                </div>

                <p
                    v-if="form.errors.students"
                    class="mt-4 text-sm text-red-600"
                >
                    {{ form.errors.students }}
                </p>
            </div>

            <!-- FOOTER -->
            <div
                class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8"
            >
                <p class="text-xs leading-5 text-slate-500">
                    Los cambios se aplican al vínculo entre este responsable y
                    sus estudiantes.
                </p>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex h-10 items-center justify-center rounded-xl px-5 text-sm font-semibold shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                    style="
                        background-color: #071a35;
                        color: #ffffff;
                        border: 1px solid #071a35;
                    "
                >
                    <Save class="mr-2 h-4 w-4" />

                    {{
                        form.processing
                            ? 'Guardando...'
                            : 'Guardar vínculos'
                    }}
                </button>
            </div>
        </form>
    </section>
</template>