<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
}>();
</script>

<template>
    <Head title="Plataforma ISAE" />

    <main class="min-h-screen overflow-x-hidden bg-white text-slate-950">
        <!-- HERO -->
<section
    class="relative bg-[#06152d] text-white"
>
            <!-- decoraciones -->
            <div
                class="pointer-events-none absolute inset-0 opacity-40"
                style="
                    background:
                        radial-gradient(circle at 12% 50%, rgba(34, 211, 238, 0.16), transparent 26%),
                        radial-gradient(circle at 92% 40%, rgba(14, 165, 233, 0.13), transparent 28%);
                "
            />

            <div
                class="absolute -left-40 bottom-16 h-80 w-80 rounded-full border border-cyan-400/10"
            />

            <div
                class="absolute -left-28 bottom-5 h-80 w-80 rounded-full border border-cyan-400/10"
            />

            <div
                class="absolute right-[-130px] top-32 h-[420px] w-[420px] rounded-full border border-cyan-400/10"
            />

            <!-- NAV -->
            <header
                class="relative z-20 border-b border-white/10"
            >
                <div
                    class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8"
                >
                    <Link
                        href="/"
                        class="flex items-center gap-3"
                    >
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-300/20 bg-cyan-400/10 text-lg font-bold text-cyan-300"
                        >
                            I
                        </div>

                        <div class="leading-tight">
                            <p class="text-xl font-bold tracking-wide">
                                ISAE
                            </p>

                            <p class="text-xs text-slate-400">
                                Plataforma Educativa
                            </p>
                        </div>
                    </Link>

                    <nav
                        class="hidden items-center gap-9 text-sm font-medium text-slate-300 md:flex"
                    >
                        <a
                            href="#inicio"
                            class="border-b-2 border-cyan-400 py-7 text-white"
                        >
                            Inicio
                        </a>

                        <a
                            href="#funcionalidades"
                            class="transition hover:text-white"
                        >
                            Funcionalidades
                        </a>

                        <a
                            href="#comunidad"
                            class="transition hover:text-white"
                        >
                            Comunidad
                        </a>
                    </nav>

                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-xl bg-cyan-400 px-5 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300"
                    >
                        Ir al panel
                    </Link>

<Link
    v-else-if="canLogin"
    :href="route('login')"
    class="rounded-xl bg-cyan-500 px-5 py-2.5 text-sm font-semibold !text-white shadow-lg shadow-cyan-950/30 transition hover:bg-cyan-400"
>
    Ingresar
</Link>
                </div>
            </header>

            <!-- HERO CONTENT -->
            <div
                id="inicio"
                class="relative z-10 mx-auto grid max-w-7xl gap-14 px-6 pb-28 pt-16 lg:grid-cols-[0.88fr_1.12fr] lg:items-center lg:px-8 lg:pb-32 lg:pt-20"
            >
                <!-- texto -->
                <div>
                    <p
                        class="mb-5 text-sm font-semibold uppercase tracking-[0.18em] text-cyan-300"
                    >
                        Plataforma institucional ISAE
                    </p>

                    <h1
                        class="max-w-2xl text-5xl font-bold leading-[1.08] tracking-tight sm:text-6xl"
                    >
                        La plataforma escolar que
                        <span class="text-cyan-300">
                            conecta
                        </span>
                        a toda tu comunidad educativa.
                    </h1>

                    <p
                        class="mt-7 max-w-xl text-lg leading-8 text-slate-300"
                    >
                        Centralizamos la información académica y la gestión
                        institucional para docentes, estudiantes, familias y
                        equipos de conducción.
                    </p>

                    <div class="mt-9 flex flex-wrap gap-4">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-3 rounded-xl bg-cyan-400 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-cyan-300"
                        >
                            Ir a mi panel
                            <span>→</span>
                        </Link>

<Link
    v-else
    :href="route('login')"
    class="inline-flex items-center gap-3 rounded-xl bg-cyan-500 px-6 py-3.5 font-semibold !text-white transition hover:bg-cyan-400"
>
    Acceder a la plataforma
    <span>→</span>
</Link>

                        <a
                            href="#funcionalidades"
                            class="rounded-xl border border-white/20 bg-white/5 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10"
                        >
                            Conocer más
                        </a>
                    </div>

                    <div
                        class="mt-10 grid max-w-xl gap-5 text-sm sm:grid-cols-3"
                    >
                        <div class="flex gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/5"
                            >
                                ✓
                            </div>

                            <div>
                                <p class="font-semibold">
                                    Seguro
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Información protegida
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/5"
                            >
                                ☁
                            </div>

                            <div>
                                <p class="font-semibold">
                                    Disponible
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Desde cualquier lugar
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <div
                                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/5"
                            >
                                ?
                            </div>

                            <div>
                                <p class="font-semibold">
                                    Simple
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Fácil de usar
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DASHBOARD PREVIEW -->
                <div class="relative">
                    <div
                        class="absolute -inset-10 rounded-full bg-cyan-400/10 blur-3xl"
                    />

                    <div
                        class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0b1729] shadow-2xl shadow-black/40"
                    >
                        <!-- browser bar -->
                        <div
                            class="flex h-11 items-center border-b border-white/10 bg-[#172033] px-4"
                        >
                            <div class="flex gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-400" />
                                <span class="h-3 w-3 rounded-full bg-amber-300" />
                                <span class="h-3 w-3 rounded-full bg-emerald-400" />
                            </div>

                            <div
                                class="mx-auto rounded-md bg-white/5 px-12 py-1 text-[10px] text-slate-400"
                            >
                                plataforma.isae.edu.ar
                            </div>
                        </div>

                        <div class="flex min-h-[420px]">
                            <!-- fake sidebar -->
                            <aside
                                class="hidden w-[155px] shrink-0 bg-[#071a35] p-4 text-xs sm:block"
                            >
                                <div
                                    class="mb-6 flex items-center gap-2 font-semibold"
                                >
                                    <div
                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-400/15 text-cyan-300"
                                    >
                                        I
                                    </div>

                                    ISAE
                                </div>

                                <div class="space-y-1.5">
                                    <div
                                        class="rounded-lg bg-cyan-400/15 px-3 py-2.5 text-cyan-200"
                                    >
                                        Inicio
                                    </div>

                                    <div class="px-3 py-2.5 text-slate-400">
                                        Novedades
                                    </div>

                                    <div class="px-3 py-2.5 text-slate-400">
                                        Cursos
                                    </div>

                                    <div class="px-3 py-2.5 text-slate-400">
                                        Evaluaciones
                                    </div>

                                    <div class="px-3 py-2.5 text-slate-400">
                                        Asistencia
                                    </div>

                                    <div class="px-3 py-2.5 text-slate-400">
                                        Boletines
                                    </div>
                                </div>
                            </aside>

                            <!-- fake dashboard -->
                            <div class="flex-1 bg-slate-50 p-5 text-slate-900">
                                <div
                                    class="mb-5 flex items-center justify-between"
                                >
                                    <div>
                                        <h2 class="font-bold sm:text-lg">
                                            ¡Bienvenido, Docente!
                                        </h2>

                                        <p
                                            class="mt-1 text-[11px] text-slate-500"
                                        >
                                            Tenés actividades pendientes y
                                            novedades importantes.
                                        </p>
                                    </div>

                                    <div
                                        class="hidden items-center gap-2 sm:flex"
                                    >
                                        <div
                                            class="h-8 w-8 rounded-full bg-cyan-500"
                                        />

                                        <div class="text-[10px]">
                                            <p class="font-semibold">
                                                María González
                                            </p>

                                            <p class="text-slate-400">
                                                Docente
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-3 xl:grid-cols-4"
                                >
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                                    >
                                        <p class="text-[10px] text-slate-500">
                                            Mis cursos
                                        </p>

                                        <p class="mt-2 text-xl font-bold">
                                            6
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                                    >
                                        <p class="text-[10px] text-slate-500">
                                            Estudiantes
                                        </p>

                                        <p class="mt-2 text-xl font-bold">
                                            128
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                                    >
                                        <p class="text-[10px] text-slate-500">
                                            Pendientes
                                        </p>

                                        <p class="mt-2 text-xl font-bold">
                                            3
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm"
                                    >
                                        <p class="text-[10px] text-slate-500">
                                            Mensajes
                                        </p>

                                        <p class="mt-2 text-xl font-bold">
                                            5
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 grid gap-3 xl:grid-cols-[1.3fr_.7fr]"
                                >
                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                                    >
                                        <p class="text-xs font-semibold">
                                            Actividad reciente
                                        </p>

                                        <div class="mt-4 space-y-4">
                                            <div class="flex gap-3">
                                                <div
                                                    class="mt-1 h-7 w-7 rounded-full bg-cyan-100"
                                                />

                                                <div>
                                                    <p
                                                        class="text-[11px] font-medium"
                                                    >
                                                        Nueva calificación
                                                        registrada
                                                    </p>

                                                    <p
                                                        class="mt-1 text-[9px] text-slate-400"
                                                    >
                                                        Matemática · Hace 2
                                                        horas
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex gap-3">
                                                <div
                                                    class="mt-1 h-7 w-7 rounded-full bg-blue-100"
                                                />

                                                <div>
                                                    <p
                                                        class="text-[11px] font-medium"
                                                    >
                                                        Asistencia completada
                                                    </p>

                                                    <p
                                                        class="mt-1 text-[9px] text-slate-400"
                                                    >
                                                        3° Año A · Hoy
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex gap-3">
                                                <div
                                                    class="mt-1 h-7 w-7 rounded-full bg-violet-100"
                                                />

                                                <div>
                                                    <p
                                                        class="text-[11px] font-medium"
                                                    >
                                                        Nueva comunicación
                                                    </p>

                                                    <p
                                                        class="mt-1 text-[9px] text-slate-400"
                                                    >
                                                        Dirección · Hace 4
                                                        horas
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                                    >
                                        <p class="text-xs font-semibold">
                                            Próximos eventos
                                        </p>

                                        <p
                                            class="mt-5 text-center text-[10px] text-slate-500"
                                        >
                                            Septiembre
                                        </p>

                                        <div
                                            class="mt-3 grid grid-cols-7 gap-1 text-center text-[8px] text-slate-400"
                                        >
                                            <span>L</span>
                                            <span>M</span>
                                            <span>M</span>
                                            <span>J</span>
                                            <span>V</span>
                                            <span>S</span>
                                            <span>D</span>

                                            <span>1</span>
                                            <span>2</span>
                                            <span>3</span>
                                            <span>4</span>
                                            <span
                                                class="rounded-full bg-cyan-400 py-0.5 font-semibold text-slate-950"
                                            >
                                                5
                                            </span>
                                            <span>6</span>
                                            <span>7</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- floating band -->
            <div
                class="relative z-20 mx-auto -mb-16 max-w-6xl px-6"
            >
                <div
                    class="grid overflow-hidden rounded-2xl border border-white/10 bg-[#0b2240] shadow-2xl shadow-black/30 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="px-7 py-6">
                        <p class="text-2xl font-bold text-white">
                            Simple
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Una interfaz clara
                        </p>
                    </div>

                    <div
                        class="border-white/10 px-7 py-6 sm:border-l"
                    >
                        <p class="text-2xl font-bold text-white">
                            Centralizada
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Todo en un solo lugar
                        </p>
                    </div>

                    <div
                        class="border-white/10 px-7 py-6 lg:border-l"
                    >
                        <p class="text-2xl font-bold text-white">
                            Conectada
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Toda la comunidad
                        </p>
                    </div>

                    <div
                        class="border-white/10 px-7 py-6 sm:border-l"
                    >
                        <p class="text-2xl font-bold text-white">
                            Segura
                        </p>

                        <p class="mt-1 text-sm text-slate-400">
                            Acceso institucional
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- COMUNIDAD -->
        <section
            id="funcionalidades"
            class="px-6 pb-24 pt-32 lg:px-8"
        >
            <div class="mx-auto max-w-7xl">
                <div class="mb-12 max-w-2xl">
                    <p
                        class="text-sm font-bold uppercase tracking-widest text-cyan-600"
                    >
                        Comunidad educativa
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Una experiencia pensada para cada usuario
                    </h2>
                </div>

                <div
                    id="comunidad"
                    class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-xl text-blue-700"
                        >
                            🎓
                        </div>

                        <h3 class="mt-5 text-xl font-bold">
                            Docentes
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Cursos, asistencia, evaluaciones y seguimiento
                            académico desde un mismo espacio.
                        </p>
                    </article>

                    <article
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 text-xl text-cyan-700"
                        >
                            👤
                        </div>

                        <h3 class="mt-5 text-xl font-bold">
                            Estudiantes
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Notas, boletines, información académica y novedades
                            siempre disponibles.
                        </p>
                    </article>

                    <article
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-xl text-teal-700"
                        >
                            👥
                        </div>

                        <h3 class="mt-5 text-xl font-bold">
                            Familias
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Seguimiento académico, asistencia y comunicación
                            institucional de sus estudiantes.
                        </p>
                    </article>

                    <article
                        class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-xl text-sky-700"
                        >
                            ▥
                        </div>

                        <h3 class="mt-5 text-xl font-bold">
                            Gestión
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            Administración académica e institucional con acceso
                            organizado y centralizado.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-slate-50 px-6 py-20 lg:px-8">
            <div
                class="mx-auto grid max-w-7xl overflow-hidden rounded-3xl bg-[#071a35] lg:grid-cols-2"
            >
                <div class="p-10 text-white sm:p-14">
                    <p
                        class="text-sm font-semibold uppercase tracking-wider text-cyan-300"
                    >
                        Comunicación que acerca
                    </p>

                    <h2
                        class="mt-4 text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        Todo lo que ISAE necesita, en un solo lugar.
                    </h2>

                    <p class="mt-5 max-w-lg leading-7 text-slate-300">
                        Una plataforma creada para acompañar los procesos
                        académicos y administrativos de toda la institución.
                    </p>

<Link
    v-if="canLogin"
    :href="route('login')"
    class="mt-8 inline-flex rounded-xl bg-cyan-500 px-6 py-3 font-semibold !text-white transition hover:bg-cyan-400"
>
    Ingresar ahora
</Link>
                </div>

                <div
                    class="relative min-h-[300px] bg-gradient-to-br from-cyan-300 to-blue-600"
                >
                    <div
                        class="absolute inset-6 rounded-2xl border border-white/20 bg-white/15 p-7 backdrop-blur"
                    >
                        <p class="text-sm font-semibold text-white">
                            Mensaje de Dirección
                        </p>

                        <div
                            class="mt-5 rounded-2xl bg-white p-5 text-slate-800 shadow-xl"
                        >
                            <p class="text-sm font-semibold">
                                Nueva comunicación institucional
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Las familias y estudiantes pueden mantenerse al
                                día con novedades importantes desde la misma
                                plataforma.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-slate-200 bg-white px-6 py-8">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between"
            >
                <p>
                    © {{ new Date().getFullYear() }} ISAE
                </p>

                <p>
                    Plataforma Educativa Institucional
                </p>
            </div>
        </footer>
    </main>
</template>