# Arquitectura de Plataforma ISAE

## Limites de la aplicacion

Plataforma ISAE es una unica aplicacion Laravel con Inertia y React. Laravel mantiene dominio, autorizacion, persistencia y rutas; React implementa las pantallas dentro del mismo repositorio. No existe una API ni frontend separado.

El codigo de negocio se organiza por dominio en `app/Domain`. Las reglas puras no dependen de Eloquent, HTTP ni de la base de datos. Modelos, controladores y acciones solo se incorporaran cuando un caso de uso los necesite.

## Identidad institucional

| Entidad | Responsabilidad |
| --- | --- |
| Person | Persona institucional y sus datos de identidad/contacto. Puede existir sin acceso al sistema. |
| User | Cuenta de acceso, credenciales y sesion. No representa por si sola a la persona institucional. |
| StudentProfile | Perfil institucional que identifica a una Person como alumno. |
| TeacherProfile | Perfil institucional que identifica a una Person como docente. |
| GuardianProfile | Perfil institucional que identifica a una Person como responsable. |
| StudentEnrollment | Matricula anual: vincula a un alumno con su contexto academico en un ciclo determinado. |

Una `Person` y un `User` son entidades diferentes. Una persona puede no tener cuenta, y una cuenta se vinculara a una persona cuando corresponda. Una persona puede tener mas de un perfil institucional. Los roles y permisos pertenecen a la cuenta `User`, que puede tener multiples roles; no deben inferirse solo por la existencia de un perfil. La relacion entre responsables y alumnos es muchos a muchos: un responsable puede relacionarse con varios alumnos y un alumno con varios responsables.

## Modelo academico conceptual

| Entidad | Representa |
| --- | --- |
| AcademicYear | Ciclo lectivo institucional, con sus fechas y estado. |
| SchoolPeriod | Periodo evaluativo dentro de un AcademicYear. |
| Level | Nivel educativo, por ejemplo inicial, primario o secundario. |
| Grade | Grado o ano dentro de un Level. |
| Course | Oferta o trayecto academico que agrupa la estructura curricular aplicable. |
| Division | Comision concreta de un Course/Grade para un AcademicYear. |
| Modality | Modalidad educativa o de cursada que puede modificar el contexto academico. |
| Subject | Materia o unidad curricular del catalogo institucional. No equivale a una fila de boletin. |
| SubjectGroup | Agrupacion pedagogica o administrativa de materias. |
| StudyPlan | Plan de estudios aplicable a un contexto academico. |
| StudyPlanSubject | Inclusion de una Subject en un StudyPlan, con su orden y condiciones. |
| Teaching | Dictado real de una materia para ciclo, division y contexto aplicable; contempla modalidad y docentes. |
| StudentEnrollment | Matricula anual del alumno en el contexto de AcademicYear, Course, Division y modalidad. |

El alumno no pertenece permanentemente a una `Division`: esa pertenencia vive en `StudentEnrollment` y cambia con cada matricula anual. La modalidad real del alumno tambien queda ligada a ese contexto anual. `Teaching` modela el dictado real y admite multiples docentes por materia, asi como multiples materias por docente. Las futuras filas de boletin son un resultado institucional de reglas de evaluacion y no deben confundirse con `Subject`.

## Reglas de calificacion

Las notas se reciben como `string` decimal o `int`, igual que llegan desde un formulario HTTP. Esto evita usar `float` binario para valores como `6.60`. Internamente las reglas usan enteros representados como cadenas y operaciones decimales exactas. El promedio de periodo se eleva al siguiente multiplo de 0.25; la definitiva usa tres promedios y se trunca, sin redondear, a dos decimales. Los niveles LI, LB, LS y LD son una clasificacion de rendimiento y no representan trayectoria pedagogica.
