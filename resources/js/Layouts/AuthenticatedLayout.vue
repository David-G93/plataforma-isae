<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Bell,
    BookOpen,
    CalendarDays,
    ChevronDown,
    ClipboardCheck,
    Home,
    LogOut,
    Menu,
    MessageSquare,
    Settings,
    Users,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';

defineProps<{
    header?: string;
}>();

const page = usePage();

const sidebarOpen = ref(false);
const userMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user as any);

const fullName = computed(() => {
    return user.value?.person?.full_name ?? user.value?.name ?? 'Usuario';
});

const roles = computed<string[]>(() => {
    return user.value?.roles ?? [];
});

const primaryRole = computed(() => {
    const roleLabels: Record<string, string> = {
        admin: 'Administrador',
        gestion: 'Gestión',
        director: 'Director',
        preceptor: 'Preceptor',
        docente: 'Docente',
        alumno: 'Estudiante',
        responsable: 'Responsable',
    };

    return roleLabels[roles.value[0]] ?? 'Usuario';
});

const initials = computed(() => {
    const firstName = user.value?.person?.first_name ?? '';
    const lastName = user.value?.person?.last_name ?? '';

    if (firstName || lastName) {
        return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
    }

    return fullName.value
        .split(' ')
        .slice(0, 2)
        .map((part: string) => part.charAt(0))
        .join('')
        .toUpperCase();
});

const menuItems = [
    {
        label: 'Inicio',
        href: route('dashboard'),
        icon: Home,
    },
{
    label: 'Personas',
    href: route('people.index'),
    icon: Users,
},
    {
        label: 'Académico',
        href: '#',
        icon: BookOpen,
    },
    {
        label: 'Asistencia',
        href: '#',
        icon: ClipboardCheck,
    },
    {
        label: 'Calendario',
        href: '#',
        icon: CalendarDays,
    },
    {
        label: 'Comunicaciones',
        href: '#',
        icon: MessageSquare,
    },
];

const logout = () => {
    router.post(route('logout'));
};

const isCurrent = (href: string) => {
    if (href === '#') {
        return false;
    }

    return window.location.pathname === new URL(href, window.location.origin).pathname;
};
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- overlay mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#071a35] text-white transition-transform duration-300 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-20 items-center justify-between border-b border-white/10 px-6">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-cyan-300/20 bg-cyan-400/10 font-bold text-cyan-300"
                    >
                        I
                    </div>

                    <div>
                        <p class="text-lg font-bold tracking-wide">
                            ISAE
                        </p>

                        <p class="text-xs text-slate-400">
                            Plataforma Educativa
                        </p>
                    </div>
                </Link>

                <button
                    type="button"
                    class="text-slate-400 lg:hidden"
                    @click="sidebarOpen = false"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <p
                    class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500"
                >
                    Principal
                </p>

                <div class="space-y-1">
                    <component
                        :is="item.href === '#' ? 'div' : Link"
                        v-for="item in menuItems"
                        :key="item.label"
                        :href="item.href === '#' ? undefined : item.href"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition',
                            isCurrent(item.href)
                                ? 'bg-cyan-400/15 text-cyan-200'
                                : item.href === '#'
                                  ? 'cursor-default text-slate-500'
                                  : 'text-slate-300 hover:bg-white/5 hover:text-white',
                        ]"
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5 shrink-0"
                        />

                        {{ item.label }}

                        <span
                            v-if="item.href === '#'"
                            class="ml-auto rounded-full bg-white/5 px-2 py-0.5 text-[10px] text-slate-500"
                        >
                            Próximamente
                        </span>
                    </component>
                </div>
            </nav>

            <div class="border-t border-white/10 p-4">
                <div class="rounded-2xl bg-white/5 p-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-cyan-400 font-bold text-slate-950"
                        >
                            {{ initials }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">
                                {{ fullName }}
                            </p>

                            <p class="text-xs text-slate-400">
                                {{ primaryRole }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- contenido -->
        <div class="lg:pl-72">
            <!-- topbar -->
            <header
                class="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <Menu class="h-5 w-5" />
                    </button>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">
                            Plataforma ISAE
                        </p>

                        <h1 class="text-lg font-bold text-slate-950">
                            {{ header ?? 'Inicio' }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-950"
                    >
                        <Bell class="h-5 w-5" />

                        <span
                            class="absolute right-2 top-2 h-2 w-2 rounded-full bg-cyan-500"
                        />
                    </button>

                    <div class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-3 rounded-xl px-2 py-1.5 transition hover:bg-slate-100"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-[#071a35] text-sm font-bold text-cyan-300"
                            >
                                {{ initials }}
                            </div>

                            <div class="hidden text-left sm:block">
                                <p class="max-w-44 truncate text-sm font-semibold text-slate-900">
                                    {{ fullName }}
                                </p>

                                <p class="text-xs text-slate-500">
                                    {{ primaryRole }}
                                </p>
                            </div>

                            <ChevronDown class="hidden h-4 w-4 text-slate-400 sm:block" />
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                        >
                            <div class="border-b border-slate-100 px-3 py-3">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ fullName }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    DNI {{ user?.person?.dni ?? '—' }}
                                </p>
                            </div>

                            <Link
                                :href="route('profile.edit')"
                                class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-950"
                            >
                                <Settings class="h-4 w-4" />
                                Mi perfil
                            </Link>

                            <button
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-red-600 hover:bg-red-50"
                                @click="logout"
                            >
                                <LogOut class="h-4 w-4" />
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>