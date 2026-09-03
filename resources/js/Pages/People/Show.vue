<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Eye,
    EyeOff,
    GraduationCap,
    KeyRound,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Save,
    ShieldCheck,
    UserRound,
    Users,
} from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{
    person: {
        id: number;
        dni: string;
        first_name: string;
        last_name: string;
        full_name: string;
        birth_date: string | null;
        email: string | null;
        phone: string | null;
        address: string | null;

        profiles: {
            student: boolean;
            teacher: boolean;
            guardian: boolean;
        };

        user: {
            id: number;
            email: string;
            is_active: boolean;
            roles: string[];
        } | null;
    };
}>();

const showPassword = ref(false);

const roleLabels: Record<string, string> = {
    admin: 'Administrador',
    gestion: 'Gestión',
    director: 'Director',
    preceptor: 'Preceptor',
    docente: 'Docente',
    alumno: 'Estudiante',
    responsable: 'Responsable',
};

const accessForm = useForm({
    student: props.person.profiles.student,
    teacher: props.person.profiles.teacher,
    guardian: props.person.profiles.guardian,

    is_active: props.person.user?.is_active ?? false,

    email:
        props.person.user?.email ??
        props.person.email ??
        '',

    password: '',
});

const saveInstitutionalAccess = () => {
    accessForm.put(
        route(
            'people.institutional-access.update',
            props.person.id,
        ),
        {
            preserveScroll: true,

            onSuccess: () => {
                accessForm.password = '';
            },
        },
    );
};
</script>

<template>
    <Head :title="person.full_name" />

    <AuthenticatedLayout header="Persona">
        <div class="mx-auto max-w-6xl space-y-6">
            <!-- acciones -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <Link
                    :href="route('people.index')"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900"
                >
                    <ArrowLeft class="h-4 w-4" />

                    Volver a personas
                </Link>

                <Link
                    :href="route('people.edit', person.id)"
                    class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    style="color: #334155"
                >
                    <Pencil class="mr-2 h-4 w-4" />

                    Editar datos
                </Link>
            </div>

            <!-- encabezado -->
            <section
                class="overflow-hidden rounded-3xl bg-[#071a35] p-7 text-white shadow-sm sm:p-9"
            >
                <div
                    class="flex flex-col gap-6 sm:flex-row sm:items-center"
                >
                    <div
                        class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-cyan-400 text-2xl font-bold text-slate-950"
                    >
                        {{ person.first_name.charAt(0).toUpperCase()
                        }}{{ person.last_name.charAt(0).toUpperCase() }}
                    </div>

                    <div class="flex-1">
                        <p class="text-sm font-semibold text-cyan-300">
                            DNI {{ person.dni }}
                        </p>

                        <h2
                            class="mt-2 text-3xl font-bold tracking-tight"
                        >
                            {{ person.last_name }},
                            {{ person.first_name }}
                        </h2>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-if="person.profiles.student"
                                class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium"
                            >
                                Estudiante
                            </span>

                            <span
                                v-if="person.profiles.teacher"
                                class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium"
                            >
                                Docente
                            </span>

                            <span
                                v-if="person.profiles.guardian"
                                class="rounded-full bg-white/10 px-3 py-1 text-xs font-medium"
                            >
                                Responsable
                            </span>

                            <span
                                v-if="
                                    !person.profiles.student &&
                                    !person.profiles.teacher &&
                                    !person.profiles.guardian
                                "
                                class="rounded-full bg-white/10 px-3 py-1 text-xs text-slate-300"
                            >
                                Sin perfil institucional
                            </span>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4"
                    >
                        <p class="text-xs text-slate-400">
                            Acceso
                        </p>

                        <div
                            class="mt-2 flex items-center gap-2"
                        >
                            <span
                                :class="[
                                    'h-2.5 w-2.5 rounded-full',
                                    person.user?.is_active
                                        ? 'bg-emerald-400'
                                        : 'bg-slate-500',
                                ]"
                            />

                            <span class="text-sm font-semibold">
                                {{
                                    person.user?.is_active
                                        ? 'Habilitado'
                                        : person.user
                                          ? 'Deshabilitado'
                                          : 'Sin usuario'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- datos + resumen acceso -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- datos personales -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h3 class="font-bold text-slate-950">
                        Datos personales
                    </h3>

                    <div class="mt-6 space-y-5">
                        <div class="flex gap-3">
                            <Mail
                                class="mt-0.5 h-5 w-5 text-slate-400"
                            />

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Email
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-800"
                                >
                                    {{
                                        person.email ||
                                        'No informado'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <Phone
                                class="mt-0.5 h-5 w-5 text-slate-400"
                            />

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Teléfono
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-800"
                                >
                                    {{
                                        person.phone ||
                                        'No informado'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <MapPin
                                class="mt-0.5 h-5 w-5 text-slate-400"
                            />

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Domicilio
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-800"
                                >
                                    {{
                                        person.address ||
                                        'No informado'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <UserRound
                                class="mt-0.5 h-5 w-5 text-slate-400"
                            />

                            <div>
                                <p
                                    class="text-xs font-medium text-slate-400"
                                >
                                    Fecha de nacimiento
                                </p>

                                <p
                                    class="mt-1 text-sm text-slate-800"
                                >
                                    {{
                                        person.birth_date ||
                                        'No informada'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- roles actuales -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div
                        class="flex items-center justify-between gap-4"
                    >
                        <div>
                            <h3 class="font-bold text-slate-950">
                                Cuenta de acceso
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Usuario y roles asignados actualmente.
                            </p>
                        </div>

                        <ShieldCheck
                            class="h-6 w-6 text-cyan-600"
                        />
                    </div>

                    <div
                        v-if="person.user"
                        class="mt-6"
                    >
                        <div
                            class="rounded-xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-medium text-slate-400"
                            >
                                Email de acceso
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-slate-800"
                            >
                                {{ person.user.email }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <p
                                class="text-xs font-medium text-slate-400"
                            >
                                Roles
                            </p>

                            <div
                                v-if="person.user.roles.length"
                                class="mt-2 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="role in person.user.roles"
                                    :key="role"
                                    class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700"
                                >
                                    {{
                                        roleLabels[role] ??
                                        role
                                    }}
                                </span>
                            </div>

                            <p
                                v-else
                                class="mt-2 text-sm text-slate-400"
                            >
                                Sin roles asignados.
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-5"
                    >
                        <p class="text-sm font-semibold text-slate-700">
                            Sin cuenta de acceso
                        </p>

                        <p
                            class="mt-2 text-sm leading-6 text-slate-500"
                        >
                            Podés crear una cuenta desde la configuración
                            institucional que aparece debajo.
                        </p>
                    </div>
                </section>
            </div>

            <!-- CONFIGURACIÓN INSTITUCIONAL -->
            <form
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                @submit.prevent="saveInstitutionalAccess"
            >
                <div
                    class="border-b border-slate-200 px-6 py-5 sm:px-8"
                >
                    <p
                        class="text-sm font-semibold text-cyan-700"
                    >
                        Configuración institucional
                    </p>

                    <h3
                        class="mt-1 text-xl font-bold text-slate-950"
                    >
                        Perfiles y acceso
                    </h3>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                    >
                        Definí qué función cumple esta persona dentro de
                        la institución y si puede ingresar a la
                        plataforma.
                    </p>
                </div>

                <!-- perfiles -->
                <div class="p-6 sm:p-8">
                    <div>
                        <h4 class="font-semibold text-slate-900">
                            Perfiles institucionales
                        </h4>

                        <p
                            class="mt-1 text-sm text-slate-500"
                        >
                            Una misma persona puede tener más de uno.
                        </p>
                    </div>

                    <div
                        class="mt-5 grid gap-4 md:grid-cols-3"
                    >
                        <!-- estudiante -->
                        <label
                            :class="[
                                'relative cursor-pointer rounded-2xl border p-5 transition',
                                accessForm.student
                                    ? 'border-cyan-400 bg-cyan-50'
                                    : 'border-slate-200 bg-white hover:border-slate-300',
                            ]"
                        >
                            <input
                                v-model="accessForm.student"
                                type="checkbox"
                                class="absolute right-4 top-4 h-4 w-4 accent-cyan-600"
                            />

                            <div
                                :class="[
                                    'flex h-11 w-11 items-center justify-center rounded-xl',
                                    accessForm.student
                                        ? 'bg-cyan-600 text-white'
                                        : 'bg-slate-100 text-slate-500',
                                ]"
                            >
                                <BookOpen class="h-5 w-5" />
                            </div>

                            <p
                                class="mt-4 font-semibold text-slate-900"
                            >
                                Estudiante
                            </p>

                            <p
                                class="mt-1 text-sm leading-5 text-slate-500"
                            >
                                Alumno matriculado o vinculado a la
                                actividad académica.
                            </p>

                            <p
                                v-if="accessForm.student"
                                class="mt-3 text-xs font-semibold text-cyan-700"
                            >
                                Asignará rol: Estudiante
                            </p>
                        </label>

                        <!-- docente -->
                        <label
                            :class="[
                                'relative cursor-pointer rounded-2xl border p-5 transition',
                                accessForm.teacher
                                    ? 'border-cyan-400 bg-cyan-50'
                                    : 'border-slate-200 bg-white hover:border-slate-300',
                            ]"
                        >
                            <input
                                v-model="accessForm.teacher"
                                type="checkbox"
                                class="absolute right-4 top-4 h-4 w-4 accent-cyan-600"
                            />

                            <div
                                :class="[
                                    'flex h-11 w-11 items-center justify-center rounded-xl',
                                    accessForm.teacher
                                        ? 'bg-cyan-600 text-white'
                                        : 'bg-slate-100 text-slate-500',
                                ]"
                            >
                                <GraduationCap class="h-5 w-5" />
                            </div>

                            <p
                                class="mt-4 font-semibold text-slate-900"
                            >
                                Docente
                            </p>

                            <p
                                class="mt-1 text-sm leading-5 text-slate-500"
                            >
                                Puede participar como docente dentro de
                                la institución.
                            </p>

                            <p
                                v-if="accessForm.teacher"
                                class="mt-3 text-xs font-semibold text-cyan-700"
                            >
                                Asignará rol: Docente
                            </p>
                        </label>

                        <!-- responsable -->
                        <label
                            :class="[
                                'relative cursor-pointer rounded-2xl border p-5 transition',
                                accessForm.guardian
                                    ? 'border-cyan-400 bg-cyan-50'
                                    : 'border-slate-200 bg-white hover:border-slate-300',
                            ]"
                        >
                            <input
                                v-model="accessForm.guardian"
                                type="checkbox"
                                class="absolute right-4 top-4 h-4 w-4 accent-cyan-600"
                            />

                            <div
                                :class="[
                                    'flex h-11 w-11 items-center justify-center rounded-xl',
                                    accessForm.guardian
                                        ? 'bg-cyan-600 text-white'
                                        : 'bg-slate-100 text-slate-500',
                                ]"
                            >
                                <Users class="h-5 w-5" />
                            </div>

                            <p
                                class="mt-4 font-semibold text-slate-900"
                            >
                                Responsable
                            </p>

                            <p
                                class="mt-1 text-sm leading-5 text-slate-500"
                            >
                                Familiar, tutor o responsable de uno o
                                más estudiantes.
                            </p>

                            <p
                                v-if="accessForm.guardian"
                                class="mt-3 text-xs font-semibold text-cyan-700"
                            >
                                Asignará rol: Responsable
                            </p>
                        </label>
                    </div>

                    <div class="my-8 border-t border-slate-200" />

                    <!-- acceso -->
                    <div
                        class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div class="max-w-xl">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#071a35] text-cyan-300"
                                >
                                    <KeyRound class="h-5 w-5" />
                                </div>

                                <div>
                                    <h4
                                        class="font-semibold text-slate-900"
                                    >
                                        Acceso a la plataforma
                                    </h4>

                                    <p
                                        class="mt-1 text-sm text-slate-500"
                                    >
                                        Permite iniciar sesión utilizando
                                        el DNI de esta persona.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- toggle -->
                        <label
                            class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                        >
                            <input
                                v-model="accessForm.is_active"
                                type="checkbox"
                                class="h-5 w-5 accent-cyan-600"
                            />

                            <span
                                class="text-sm font-semibold text-slate-700"
                            >
                                {{
                                    accessForm.is_active
                                        ? 'Acceso habilitado'
                                        : 'Acceso deshabilitado'
                                }}
                            </span>
                        </label>
                    </div>

                    <!-- credenciales -->
                    <div
                        v-if="
                            accessForm.is_active ||
                            person.user
                        "
                        class="mt-6 grid gap-5 rounded-2xl border border-slate-200 bg-slate-50 p-5 md:grid-cols-2"
                    >
                        <div class="space-y-2">
                            <Label for="access_email">
                                Email de la cuenta
                            </Label>

                            <Input
                                id="access_email"
                                v-model="accessForm.email"
                                type="email"
                                autocomplete="off"
                                placeholder="usuario@isae.local"
                            />

                            <p
                                v-if="accessForm.errors.email"
                                class="text-sm text-red-600"
                            >
                                {{ accessForm.errors.email }}
                            </p>

                            <p
                                class="text-xs leading-5 text-slate-500"
                            >
                                El ingreso se realiza con DNI. El email se
                                conserva como dato técnico de la cuenta.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="access_password">
                                {{
                                    person.user
                                        ? 'Nueva contraseña'
                                        : 'Contraseña inicial'
                                }}
                            </Label>

                            <div class="relative">
                                <Input
                                    id="access_password"
                                    v-model="accessForm.password"
                                    :type="
                                        showPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    autocomplete="new-password"
                                    :placeholder="
                                        person.user
                                            ? 'Dejar vacío para conservarla'
                                            : 'Mínimo 8 caracteres'
                                    "
                                    class="pr-11"
                                />

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                                    @click="
                                        showPassword =
                                            !showPassword
                                    "
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="h-5 w-5"
                                    />

                                    <Eye
                                        v-else
                                        class="h-5 w-5"
                                    />
                                </button>
                            </div>

                            <p
                                v-if="
                                    accessForm.errors.password
                                "
                                class="text-sm text-red-600"
                            >
                                {{
                                    accessForm.errors.password
                                }}
                            </p>

                            <p
                                v-if="person.user"
                                class="text-xs leading-5 text-slate-500"
                            >
                                Si no querés modificar la contraseña,
                                dejá este campo vacío.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- footer -->
                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8"
                >
                    <p
                        class="max-w-xl text-xs leading-5 text-slate-500"
                    >
                        Los roles Estudiante, Docente y Responsable se
                        mantienen sincronizados con los perfiles
                        institucionales.
                    </p>

                    <button
                        type="submit"
                        :disabled="accessForm.processing"
                        class="inline-flex h-10 items-center justify-center rounded-xl px-5 text-sm font-semibold shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60"
                        style="
                            background-color: #071a35;
                            color: #ffffff;
                            border: 1px solid #071a35;
                        "
                    >
                        <Save class="mr-2 h-4 w-4" />

                        {{
                            accessForm.processing
                                ? 'Guardando...'
                                : 'Guardar configuración'
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>