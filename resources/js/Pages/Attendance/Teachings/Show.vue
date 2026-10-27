<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, Save, UsersRound } from '@lucide/vue';
import { computed, reactive, ref } from 'vue';

type Student = {
    enrollment_id: number;
    name: string;
    dni: string | null;
    modality: string | null;
    status: string;
    notes: string | null;
};

type Status = { value: string; label: string; short: string };

const props = defineProps<{
    teaching: {
        id: number;
        subject: string;
        modality: string | null;
        course: string;
        division: string;
        level: string;
        academic_year: number;
        starts_at: string;
        ends_at: string;
    };
    date: string;
    backFilters: {
        year: number;
        level: number | null;
        course: number | null;
        division: number | null;
        subject: number | null;
    };
    canManage: boolean;
    statuses: Status[];
    students: Student[];
}>();

const page = usePage();
const saving = ref(false);
const records = reactive(props.students.map((student) => ({ ...student })));
const success = computed(() => (page.props.flash as any)?.success ?? null);

const changeDate = (event: Event) => {
    router.get(route('attendance.teachings.show', props.teaching.id), {
        date: (event.target as HTMLInputElement).value,
    }, { preserveState: false, preserveScroll: false });
};

const setAll = (status: string) => {
    records.forEach((record) => { record.status = status; });
};

const save = () => {
    saving.value = true;
    router.put(route('attendance.teachings.update', props.teaching.id), {
        date: props.date,
        records: records.map((record) => ({
            student_enrollment_id: record.enrollment_id,
            status: record.status,
            notes: record.notes,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false; },
    });
};
</script>

<template>
    <Head :title="`Asistencia · ${teaching.subject}`" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
            <Link :href="route('attendance.teachings.index', backFilters)" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
                <ArrowLeft class="h-4 w-4" /> Volver a cursadas
            </Link>

            <section class="rounded-3xl bg-[#071a35] p-7 text-white shadow-xl">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-300">{{ teaching.level }} · {{ teaching.course }} · División {{ teaching.division }}</p>
                        <h1 class="mt-2 text-3xl font-bold">{{ teaching.subject }}</h1>
                        <p class="mt-2 text-sm text-slate-300">Registro interno de asistencia del docente. No modifica automáticamente las inasistencias oficiales del boletín.</p>
                    </div>

                    <label class="text-sm font-semibold text-slate-200">
                        Fecha
                        <input type="date" :value="date" :min="teaching.starts_at" :max="teaching.ends_at" class="mt-1 block rounded-xl border border-white/10 bg-white px-4 py-2 text-slate-900" @change="changeDate" />
                    </label>
                </div>
            </section>

            <div v-if="success" class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                <CheckCircle2 class="h-5 w-5" /> {{ success }}
            </div>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <header class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-2">
                        <UsersRound class="h-5 w-5 text-cyan-700" />
                        <span class="font-bold text-slate-900">{{ records.length }} alumnos elegibles</span>
                    </div>
                    <div v-if="canManage" class="flex flex-wrap gap-2">
                        <button v-for="status in statuses" :key="status.value" type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100" @click="setAll(status.value)">Todos {{ status.label }}</button>
                    </div>
                </header>

                <div class="divide-y divide-slate-100">
                    <article v-for="(student, index) in records" :key="student.enrollment_id" class="grid gap-4 px-6 py-4 lg:grid-cols-[60px_1.5fr_1fr_1.4fr] lg:items-center">
                        <div class="text-sm font-bold text-slate-400">{{ index + 1 }}</div>
                        <div>
                            <p class="font-semibold text-slate-900">{{ student.name }}</p>
                            <p class="text-xs text-slate-400">{{ student.dni || 'Sin DNI' }}<span v-if="student.modality"> · {{ student.modality }}</span></p>
                        </div>
                        <select v-model="student.status" :disabled="!canManage" class="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.short }} · {{ status.label }}</option>
                        </select>
                        <input v-model="student.notes" :disabled="!canManage" type="text" maxlength="1000" placeholder="Observación opcional" class="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </article>
                </div>
            </section>

            <div v-if="canManage" class="flex justify-end">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white disabled:opacity-60" :disabled="saving" @click="save">
                    <Save class="h-4 w-4" /> {{ saving ? 'Guardando...' : 'Guardar asistencia' }}
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
