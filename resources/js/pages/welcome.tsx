import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Database, Lock, MonitorSmartphone, ShieldCheck } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';

const pillars = [
    {
        title: 'Aplicacion unica',
        description: 'Laravel, React e Inertia viven en un solo repositorio y una sola app.',
        icon: MonitorSmartphone,
    },
    {
        title: 'Base MySQL local',
        description: 'Conexion lista contra MySQL Community 8.4 con base y usuario propios.',
        icon: Database,
    },
    {
        title: 'Autenticacion base',
        description: 'Login, registro, verificacion y ajustes ya quedaron preparados.',
        icon: Lock,
    },
    {
        title: 'Punto de partida limpio',
        description: 'La base tecnica esta lista para empezar los modulos escolares despues.',
        icon: ShieldCheck,
    },
];

export default function Welcome() {
    return (
        <>
            <Head title="Plataforma ISAE" />

            <main className="min-h-screen bg-[linear-gradient(180deg,#f5f7fb_0%,#eef3ea_100%)] text-slate-950">
                <div className="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-10 lg:px-8">
                    <header className="flex flex-col gap-6 border-b border-slate-200/80 pb-8 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-3xl space-y-4">
                            <p className="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">
                                Plataforma ISAE
                            </p>
                            <h1 className="font-serif text-4xl leading-tight text-slate-950 lg:text-6xl">
                                Base tecnica lista para construir una plataforma escolar grande.
                            </h1>
                            <p className="max-w-2xl text-base leading-7 text-slate-700 lg:text-lg">
                                El proyecto ya esta montado sobre Laravel, React, TypeScript, Inertia y
                                MySQL, con autenticacion base y flujo local de desarrollo verificado.
                            </p>
                        </div>

                        <div className="flex flex-wrap gap-3">
                            <Button asChild className="bg-slate-950 text-white hover:bg-slate-800">
                                <Link href={login()}>
                                    Ingresar
                                    <ArrowRight className="ml-2 size-4" />
                                </Link>
                            </Button>
                            <Button asChild variant="outline" className="border-slate-300 bg-white/80">
                                <Link href={register()}>Crear cuenta</Link>
                            </Button>
                            <Button asChild variant="ghost" className="text-slate-700">
                                <Link href={dashboard()}>Ir al dashboard</Link>
                            </Button>
                        </div>
                    </header>

                    <section className="grid gap-4 py-10 md:grid-cols-2 xl:grid-cols-4">
                        {pillars.map(({ title, description, icon: Icon }) => (
                            <Card key={title} className="border-slate-200 bg-white/85 shadow-sm">
                                <CardHeader className="space-y-3">
                                    <div className="flex size-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                                        <Icon className="size-5" />
                                    </div>
                                    <div>
                                        <CardTitle className="text-lg">{title}</CardTitle>
                                        <CardDescription className="mt-2 text-sm leading-6 text-slate-600">
                                            {description}
                                        </CardDescription>
                                    </div>
                                </CardHeader>
                            </Card>
                        ))}
                    </section>

                    <section className="mt-auto grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <Card className="border-slate-200 bg-slate-950 text-slate-50 shadow-lg">
                            <CardHeader>
                                <CardTitle className="font-serif text-3xl">Que ya esta resuelto</CardTitle>
                                <CardDescription className="text-slate-300">
                                    La etapa de bootstrap quedo lista para avanzar con dominio y modulos.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-3 text-sm leading-6 text-slate-200">
                                <div>Laravel 13 con starter oficial de React, Inertia, Tailwind y shadcn/ui.</div>
                                <div>Base `plataforma_isae_dev` en MySQL local con charset `utf8mb4`.</div>
                                <div>Testing con Pest y comandos de desarrollo documentados para Windows.</div>
                            </CardContent>
                        </Card>

                        <Card className="border-slate-200 bg-white/90 shadow-sm">
                            <CardHeader>
                                <CardTitle className="text-xl">Siguiente paso natural</CardTitle>
                                <CardDescription className="text-slate-600">
                                    Empezar por modelado de personas, usuarios, roles y estructura academica.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="text-sm leading-6 text-slate-700">
                                La base quedo intencionalmente limpia: sin modulos escolares todavia, sin
                                sobrearquitectura y con espacio para crecer por dominios.
                            </CardContent>
                        </Card>
                    </section>
                </div>
            </main>
        </>
    );
}
