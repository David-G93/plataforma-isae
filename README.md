# Plataforma ISAE

Base técnica de `Plataforma ISAE` construida con Laravel, React, TypeScript, Inertia y MySQL.

## Requisitos locales

- PHP 8.4
- Composer 2
- Node.js 24 LTS
- npm 11
- MySQL Community 8.4

## Arranque rápido

1. Iniciar la base local:

```powershell
composer run db:start
```

2. Iniciar Laravel + Vite:

```powershell
composer run dev
```

3. Abrir la aplicación:

```text
http://127.0.0.1:8000
```

## Comandos útiles

```powershell
composer run db:status
composer run db:stop
php artisan migrate
php artisan test
npm run build
```

Más detalles en [docs/local-development.md](docs/local-development.md).
