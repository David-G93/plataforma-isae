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

En Git Bash, si `composer` no esta disponible, ejecuta los mismos comandos con:

```bash
bash run.sh db:start
bash run.sh dev
```

3. Si necesitás recompilar assets:

```powershell
npm run build
```

4. Si necesitás ejecutar migraciones o tests:

```powershell
composer run migrate
composer run test:feature
```

## Scripts locales

- `composer run db:start`: inicia MySQL local en segundo plano si no está corriendo.
- `composer run db:status`: comprueba conectividad y devuelve versión/puerto.
- `composer run db:stop`: apaga MySQL local de este proyecto.

## Nota técnica

Los comandos habituales del proyecto seleccionan el PHP oficial de Windows en
`%LOCALAPPDATA%\Programs\PHP\php.exe`, incluso si una terminal todavía apunta
a otra instalación. Para usar una ubicación distinta, definí `ISAE_PHP_BINARY`
con la ruta absoluta al ejecutable.
