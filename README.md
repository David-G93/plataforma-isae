# Plataforma ISAE

Base técnica de `Plataforma ISAE` construida con Laravel, React, TypeScript, Inertia y MySQL.

## Requisitos locales

- PHP 8.4
- Composer 2
- Node.js 24 LTS
- npm 11
- MySQL Community 8.4

Los comandos de Composer y npm seleccionan el PHP oficial ubicado en
`%LOCALAPPDATA%\Programs\PHP\php.exe`. Para indicar otra ubicacion, defini
`ISAE_PHP_BINARY` con la ruta absoluta al ejecutable.

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

### Git Bash

Si usas Git Bash y el comando `composer` no existe, usa el lanzador incluido:

```bash
bash run.sh db:start
bash run.sh dev
```

## Comandos útiles

```powershell
composer run db:status
composer run db:stop
composer run migrate
composer run test:feature
npm run build
```

Más detalles en [docs/local-development.md](docs/local-development.md).

Si una terminal ya abierta sigue resolviendo el `php` anterior, abrí una nueva sesión de PowerShell o CMD para que tome el PHP oficial configurado para este proyecto.
