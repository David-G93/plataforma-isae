<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    BarChart3,
    BookOpen,
    Eye,
    EyeOff,
    GraduationCap,
    LockKeyhole,
    ShieldCheck,
    User,
    Users,
} from '@lucide/vue';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    dni: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const audiences = [
    {
        title: 'Docentes',
        description: 'Gestioná tus clases, contenidos y evaluaciones.',
        icon: GraduationCap,
    },
    {
        title: 'Estudiantes',
        description: 'Accedé a cursos, actividades e información académica.',
        icon: BookOpen,
    },
    {
        title: 'Familias',
        description: 'Acompañá el recorrido educativo de tus estudiantes.',
        icon: Users,
    },
    {
        title: 'Gestión',
        description: 'Información institucional clara y centralizada.',
        icon: BarChart3,
    },
];
</script>

<template>
    <GuestLayout>
        <Head title="Ingresar" />

        <main class="login-shell">
            <!-- FORMULARIO -->
            <!-- Mobile: primero / Desktop: derecha -->
            <section class="login-panel">
                <div class="login-background-circle login-circle-one" />
                <div class="login-background-circle login-circle-two" />

                <div class="login-container">
                    <Link
                        href="/"
                        class="mobile-back mb-5 inline-flex text-sm font-medium text-slate-500 hover:text-slate-900"
                    >
                        ← Volver al inicio
                    </Link>

                    <div
                        class="rounded-[26px] border border-slate-200 bg-white px-6 py-7 shadow-[0_25px_70px_-30px_rgba(15,23,42,.35)] sm:px-8"
                    >
                        <!-- Marca -->
                        <div class="text-center">
                            <Link
                                href="/"
                                class="mx-auto flex w-fit flex-col items-center"
                            >
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-600 shadow-lg shadow-cyan-200/60"
                                >
                                    <BookOpen class="h-8 w-8 text-white" />
                                </div>

                                <p
                                    class="mt-3 text-2xl font-extrabold tracking-wide text-[#071a35]"
                                >
                                    ISAE
                                </p>

                                <p class="text-xs text-slate-500">
                                    Plataforma Educativa
                                </p>
                            </Link>

                            <h1
                                class="mt-6 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                Bienvenido
                            </h1>

                            <p class="mt-2 text-sm text-slate-500">
                                Iniciá sesión con tu DNI y contraseña
                            </p>
                        </div>

                        <div
                            v-if="status"
                            class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                        >
                            {{ status }}
                        </div>

                        <form
                            class="mt-7 space-y-4"
                            @submit.prevent="submit"
                        >
                            <!-- DNI -->
                            <div class="space-y-2">
                                <Label
                                    for="dni"
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    DNI
                                </Label>

                                <div class="relative">
                                    <User
                                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                    />

                                    <Input
                                        id="dni"
                                        v-model="form.dni"
                                        type="text"
                                        inputmode="numeric"
                                        autocomplete="username"
                                        placeholder="Ingresá tu número de DNI"
                                        required
                                        autofocus
                                        class="h-11 rounded-xl border-slate-200 pl-12 text-sm"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.dni"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.dni }}
                                </p>
                            </div>

                            <!-- Contraseña -->
                            <div class="space-y-2">
                                <Label
                                    for="password"
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Contraseña
                                </Label>

                                <div class="relative">
                                    <LockKeyhole
                                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                    />

                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        placeholder="Ingresá tu contraseña"
                                        required
                                        class="h-11 rounded-xl border-slate-200 px-12 text-sm"
                                    />

                                    <button
                                        type="button"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
                                        @click="showPassword = !showPassword"
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
                                    v-if="form.errors.password"
                                    class="text-sm text-red-600"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Opciones -->
                            <div
                                class="flex flex-wrap items-center justify-between gap-3 pt-1"
                            >
                                <label
                                    class="flex cursor-pointer items-center gap-2 text-sm text-slate-600"
                                >
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 accent-cyan-500"
                                    />

                                    Recordar mi sesión
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm font-semibold text-cyan-600 hover:text-cyan-800"
                                >
                                    Olvidé mi contraseña
                                </Link>
                            </div>

                            <!-- Botón -->
                            <Button
                                type="submit"
                                class="group h-11 w-full rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-400 font-semibold text-[#041529] shadow-lg shadow-cyan-500/20 hover:from-cyan-400 hover:to-cyan-300"
                                :disabled="form.processing"
                            >
                                {{
                                    form.processing
                                        ? 'Ingresando...'
                                        : 'Ingresar'
                                }}

                                <ArrowRight
                                    v-if="!form.processing"
                                    class="ml-2 h-4 w-4 transition group-hover:translate-x-1"
                                />
                            </Button>
                        </form>
                    </div>

                    <div
                        class="mt-5 flex items-start justify-center gap-2 text-center"
                    >
                        <ShieldCheck
                            class="mt-0.5 h-4 w-4 shrink-0 text-slate-500"
                        />

                        <p class="max-w-xs text-xs leading-5 text-slate-500">
                            Acceso exclusivo para miembros habilitados de la
                            comunidad educativa ISAE.
                        </p>
                    </div>
                </div>
            </section>

            <!-- INFORMACIÓN -->
            <!-- Mobile: debajo / Desktop: izquierda -->
            <section class="info-panel">
                <div
                    class="absolute -left-48 top-1/2 h-[480px] w-[480px] -translate-y-1/2 rounded-full border border-cyan-300/10"
                />

                <div
                    class="absolute -left-32 top-1/2 h-[360px] w-[360px] -translate-y-1/2 rounded-full border border-cyan-300/10"
                />

                <div
                    class="absolute -right-40 -top-28 h-[420px] w-[420px] rounded-full bg-cyan-400/10 blur-[110px]"
                />

                <div class="relative flex h-full w-full max-w-2xl flex-col">
                    <!-- Marca -->
                    <Link
                        href="/"
                        class="inline-flex w-fit items-center gap-3"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl border border-cyan-300/30 bg-cyan-400/10"
                        >
                            <BookOpen class="h-7 w-7 text-cyan-300" />
                        </div>

                        <div>
                            <p class="text-2xl font-bold tracking-wide">
                                ISAE
                            </p>

                            <p class="text-xs text-slate-400">
                                Plataforma Educativa
                            </p>
                        </div>
                    </Link>

                    <div class="flex flex-1 items-center py-10">
                        <div>
                            <span
                                class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-400/10 px-3 py-1.5 text-xs font-semibold text-cyan-300"
                            >
                                Plataforma ISAE
                            </span>

                            <h1
                                class="mt-5 max-w-xl text-4xl font-bold leading-[1.08] tracking-tight lg:text-5xl"
                            >
                                Conectamos a toda
                                <span class="text-cyan-300">
                                    la comunidad educativa
                                </span>
                                para aprender, enseñar y crecer juntos.
                            </h1>

                            <p
                                class="mt-5 max-w-lg text-base leading-7 text-slate-300"
                            >
                                Una plataforma integral para docentes,
                                estudiantes, familias y gestión institucional.
                            </p>

                            <div class="mt-8 grid gap-4 xl:grid-cols-2">
                                <div
                                    v-for="audience in audiences"
                                    :key="audience.title"
                                    class="flex items-start gap-3"
                                >
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-cyan-400/30 bg-cyan-400/5 text-cyan-300"
                                    >
                                        <component
                                            :is="audience.icon"
                                            class="h-5 w-5"
                                        />
                                    </div>

                                    <div>
                                        <p class="font-semibold text-white">
                                            {{ audience.title }}
                                        </p>

                                        <p
                                            class="mt-1 text-sm leading-5 text-slate-400"
                                        >
                                            {{ audience.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500">
                        © {{ new Date().getFullYear() }} ISAE · Plataforma
                        Educativa Institucional
                    </p>
                </div>
            </section>
        </main>
    </GuestLayout>
</template>

<style scoped>
/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
| El formulario aparece primero.
| La sección institucional aparece debajo.
*/
.login-shell {
    display: flex;
    min-height: 100vh;
    flex-direction: column;
}

.login-panel {
    position: relative;
    order: 1;
    display: flex;
    min-height: 100vh;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f6f9fc;
    padding: 2rem 1.25rem;
}

.info-panel {
    position: relative;
    order: 2;
    display: flex;
    min-height: 100vh;
    overflow: hidden;
    background:
        radial-gradient(
            circle at top left,
            rgba(34, 211, 238, 0.13),
            transparent 35%
        ),
        radial-gradient(
            circle at bottom right,
            rgba(59, 130, 246, 0.12),
            transparent 38%
        ),
        #05182f;
    padding: 2.5rem 1.75rem;
    color: white;
}

.login-container {
    position: relative;
    width: 100%;
    max-width: 440px;
}

.login-background-circle {
    pointer-events: none;
    position: absolute;
    border: 1px solid #cffafe;
    border-radius: 9999px;
}

.login-circle-one {
    top: -7rem;
    right: -7rem;
    width: 18rem;
    height: 18rem;
}

.login-circle-two {
    bottom: -8rem;
    left: -6rem;
    width: 18rem;
    height: 18rem;
}

/*
|--------------------------------------------------------------------------
| ESCRITORIO
|--------------------------------------------------------------------------
| A partir de 900px son DOS COLUMNAS REALES.
|--------------------------------------------------------------------------
*/
@media (min-width: 900px) {
    .login-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        min-height: 100vh;
    }

    .info-panel {
        order: 1;
        min-height: 100vh;
        padding: 2.5rem 3rem;
    }

    .login-panel {
        order: 2;
        min-height: 100vh;
        padding: 2rem 3rem;
    }

    .mobile-back {
        display: none;
    }
}

/*
|--------------------------------------------------------------------------
| PANTALLAS GRANDES
|--------------------------------------------------------------------------
*/
@media (min-width: 1400px) {
    .info-panel {
        padding-left: 4rem;
        padding-right: 4rem;
    }

    .login-panel {
        padding-left: 4rem;
        padding-right: 4rem;
    }
}
</style>