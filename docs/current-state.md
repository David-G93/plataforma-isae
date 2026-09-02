# Estado actual — Plataforma ISAE

## Fase actual

Foundation completada.

## Stack

- Laravel 13
- PHP 8.4
- MySQL
- React
- TypeScript
- Inertia.js
- Tailwind CSS
- componentes estilo shadcn
- Pest

## Implementado

### Base técnica

- Aplicación Laravel única.
- React + Inertia funcionando.
- Autenticación inicial del starter instalada.
- Build frontend funcionando.
- Suite de tests funcionando.
- Git y repositorio remoto configurados.

### Arquitectura

Definidos conceptualmente:

- Person
- User
- StudentProfile
- TeacherProfile
- GuardianProfile
- StudentEnrollment
- AcademicYear
- SchoolPeriod
- Level
- Grade
- Course
- Division
- Modality
- Subject
- SubjectGroup
- StudyPlan
- StudyPlanSubject
- Teaching

### Dominio de calificaciones

Implementado y testeado:

- validación de notas de 1 a 10
- notas parciales decimales exactas
- promedio de período elevado al siguiente 0.25
- definitiva con tres períodos
- truncado de definitiva a dos decimales
- niveles LI / LB / LS / LD
- aritmética sin float binario

## Verificación

Última verificación conocida:

- php artisan test: 69 tests pasando, 166 assertions
- npm run build: correcto

## Próximo módulo

Identidad y Acceso.

Primera etapa:

- Person
- User
- roles
- permisos
- perfiles institucionales
- relación responsables/alumnos
- login por DNI
- activación/desactivación de acceso
- administrador
- autorización mediante Policies/Gates
- auditoría base
