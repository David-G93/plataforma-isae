# Desarrollo local

## Aplicación

- Ruta del proyecto: `C:\Users\david\OneDrive\Documentos\plataforma-isae`
- URL local: `http://127.0.0.1:8000`
- Stack: Laravel 13 + React 19 + TypeScript + Inertia 3 + Tailwind 4 + shadcn/ui

## Base de datos local

Para evitar conflictos con `XAMPP` y con archivos sincronizados por `OneDrive`, MySQL quedó instalado fuera del proyecto:

- Binarios: `%LOCALAPPDATA%\Programs\MySQL\mysql-8.4.11`
- Datos: `%LOCALAPPDATA%\MySQL\plataforma-isae`
- Host: `127.0.0.1`
- Puerto: `3307`
- Base: `plataforma_isae_dev`

## Flujo recomendado

1. Levantar MySQL:

```powershell
composer run db:start
```

2. Iniciar el entorno web:

```powershell
composer run dev
```

3. Si necesitás recompilar assets:

```powershell
npm run build
```

4. Si necesitás ejecutar migraciones o tests:

```powershell
php artisan migrate
php artisan test
```

## Scripts locales

- `composer run db:start`: inicia MySQL local en segundo plano si no está corriendo.
- `composer run db:status`: comprueba conectividad y devuelve versión/puerto.
- `composer run db:stop`: apaga MySQL local de este proyecto.

## Nota técnica

El proyecto quedó preparado para usar un PHP oficial de Windows instalado en `%LOCALAPPDATA%\Programs\PHP`, con `composer` y `laravel` envueltos sobre ese runtime. Las instalaciones previas de `Herd Lite` siguen presentes en la máquina, pero dejaron de ser la referencia principal para este proyecto.

Si una terminal ya estaba abierta antes de este cambio, puede seguir resolviendo el `php` viejo hasta abrir una nueva sesión.
