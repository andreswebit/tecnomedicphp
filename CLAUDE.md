# CLAUDE.md — Contexto del proyecto TECNOMEDIC

Este archivo existe para que una sesión nueva de Claude entienda el proyecto
sin tener que leer todo el código ni el historial de conversación. Es denso
a propósito. Actualizalo cuando cambie algo estructural importante.

## Qué es esto

Sistema web para **TecnoMedic**, centro médico multiespecialidad en
Corrientes, Argentina (Audiología, Medicina Hiperbárica, Nutrición,
Ortopedia y Rehabilitación, Equipamiento Médico y Quirúrgico).
Incluye: sitio público + reserva de turnos, bot de WhatsApp, tienda,
panel admin de turnos, y "Mi Portal" (pacientes/profesionales/admin) con
ficha médica.

**Repo:** https://github.com/andreswebit/tecnomedicphp.git (público)

## Stack — leer antes de sugerir nada "estándar"

- **PHP puro + mysqli.** Sin framework. No sugerir Laravel/Symfony/Eloquent.
- **MySQL/MariaDB**, base `c1452348_wordpre` **compartida con WordPress**
  (tablas `wp_*` de otro cliente). Por eso TODA tabla propia usa prefijo
  `tm_`. Nunca crear una tabla sin ese prefijo.
- **Hosting: Ferozo** (shared hosting, `c145.ferozo.com`, usuario
  `c1452348`). Sin acceso SSH/composer garantizado — asumir que no se puede
  correr `composer install` en el servidor; hay que instalarlo local y
  subir `vendor/` por FTP.
- **Dos dominios:** `tecnomedic.com.ar` (parte operativa: turnos, admin,
  portal) y `test2.tecnomedic.com.ar` (home). Separados porque el plan de
  Ferozo solo permite 1 certificado HTTPS por dominio. El `index.php` del
  home vive en el mismo repo pero puede estar desplegado como copia
  separada — cuidado con links que dependan de `BASE_URL`/`b()` para cruzar
  de un dominio al otro: usar URL absoluta hardcodeada cuando el link tiene
  que ir de un dominio al otro (ver `index.php`, botón "Mi Portal").
- **Dev local:** XAMPP en Windows,
  `D:\Users\andresperal\Documents\Proyectos Python\tecnomedic_php`,
  `Apache DocumentRoot` apunta directo ahí (no hace falta symlink/alias).
- **Config:** todo en `.env` (parser propio en `includes/env_loader.php`,
  sin librerías). `.env` NUNCA se commitea. `.env.example` sí. Cada entorno
  (local/Ferozo) tiene su propio `.env` con sus propios valores.

## Arquitectura de autenticación — LEER ANTES DE TOCAR AUTH

`includes/auth.php` contiene **dos sistemas históricamente separados que
ahora comparten el mismo login** (unificados recientemente):

- **Sesión Portal** (`$_SESSION['portal_uid']`, `portal_rol()`,
  `portal_require_role(['admin'])`, etc.) — basada en `tm_usuarios`
  (roles: `paciente`, `profesional`, `admin`). Login único en `/login.php`
  (raíz), acepta email o DNI + password.
- **Sesión legacy** (`$_SESSION['tm_logged']`, `esta_logueado()`,
  `requiere_login()`, `iniciar_sesion_php()`) — el panel de turnos viejo
  usaba usuario/clave fijo (`ADMIN_USER`/`ADMIN_PASS` del `.env`). **Ya
  migrado**: `admin/index.php`, `actualizar.php`, `modificar.php`,
  `eliminar.php` ahora usan `portal_require_role(['admin'])`, NO
  `requiere_login()`. `admin/login.php` quedó como simple redirect a
  `/login.php`. Las funciones legacy siguen existiendo por compatibilidad
  (`admin/recordatorio.php` las usa como fallback de acceso manual junto
  al cron token) pero no se usan para gatear el panel de turnos.

**Regla:** cualquier página nueva del panel admin de turnos usa
`portal_require_role(['admin'])`, no `requiere_login()`. El login único
para todo el sistema es `/login.php` → redirige a `admin/tablero.php` si
el rol es admin.

## Mapa de archivos clave

```
includes/
  db.php            → conexión (mysqli_report activado) + CRUD turnos/bot. Lee .env.
  db_portal.php      → CRUD usuarios/perfiles/asignaciones/obras sociales/padrón (tm_personas)
  db_ficha.php        → CRUD historia clínica/tratamientos/estudios (Fase D) + control de permisos
  auth.php            → sesión unificada (ver arriba)
  email.php            → enviar_email(): MAIL_DRIVER=smtp (PHPMailer+Ferozo) o =log (guarda .html local)
  config_mail.php       → setup de PHPMailer (requiere vendor/autoload.php)
  env_loader.php         → parser de .env sin dependencias
  portal_header.php       → layout Portal + modal de ficha médica (JS: abrirFicha(), cerrarFicha())
  portal_footer.php

admin/
  tablero.php    → hub central post-login para admin (tiles: Turnos/Pacientes/DNI/Presupuestos/Tienda)
  index.php       → panel de turnos (diseño propio, Playfair+DM Sans, NO usa portal_header.php)
  actualizar.php, modificar.php, eliminar.php   → acciones sobre turnos
  portal_usuarios.php  → aprobar pacientes, alta profesional/admin, asignaciones
  consultar_dni.php     → cruce de padrón (tm_personas) por DNI

portal/
  paciente/dashboard.php, perfil.php
  profesional/dashboard.php   → lista de pacientes ASIGNADOS solamente
  ficha/ver.php + handlers    → Fase D, se abre como modal via fetch()

sql/  → ⚠️ históricamente NO se mantuvo commiteada de forma consistente.
         Verificar en cada sesión si están: schema_fase_a.sql,
         schema_fase_a2_personas.sql, schema_fase_d_ficha_medica.sql,
         schema_agregar_area_turnos.sql, seeds. Si falta alguna, hay que
         reconstruirla a partir de includes/db*.php (las funciones asumen
         que existe cierta estructura de tablas).
```

## Modelo de datos — lo no obvio

- **`tm_turnos.fecha` es `DATE` real** (no texto). Todo el resto del código
  (formularios, emails, bot, admin) trabaja con `DD/MM/YYYY` como string.
  La conversión pasa SIEMPRE por SQL: `STR_TO_DATE(?, '%d/%m/%Y')` al
  escribir, `DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha` al leer. **Nunca
  hacer `SELECT *` de `tm_turnos` y pasarle el campo `fecha` crudo a nada**
  — siempre por las funciones de `db.php` que ya hacen esta conversión.
  Mismo patrón para `hora` (columna `TIME`, formatear con `TIME_FORMAT`).
- **`tm_turnos.area`** = especialidad (`audiologia|hiperbarica|nutricion|
  ortopedia|equipamiento`). La disponibilidad de horarios
  (`get_ocupados()`, `api/horarios.php`) se cuenta **por área**, no
  globalmente — dos especialidades distintas no compiten por el mismo
  cupo aunque compartan el array `$HORARIOS`.
- **`tm_personas`** es un padrón liviano por DNI, se actualiza solo
  (`persona_upsert()`) cuando alguien reserva un turno o se registra en el
  Portal. No reemplaza `tm_usuarios` ni `tm_turnos`, solo permite
  responder rápido "¿este DNI ya está en algún lado?" desde
  `admin/consultar_dni.php`.
- **`tm_asignaciones`** (paciente↔profesional, many-to-many con `area`) es
  la base del control de permisos: un profesional solo ve/edita la ficha
  de pacientes con asignación activa. `ficha_puede_ver()`/
  `ficha_puede_editar()` en `db_ficha.php` son la fuente de verdad para
  esto — no reimplementar el chequeo en otro lado.

## Convenciones de trabajo (aprendidas a las trompadas)

- **Nunca hacer `require_once __DIR__ . '/../vendor/autoload.php'` sin
  verificar que `vendor/` existe en el server** — Ferozo no corre
  `composer install`, hay que subir la carpeta por FTP después de generarla
  local.
- **Windows esconde extensiones** — si algo con `.env` no carga, primero
  verificar con `dir /a .env*` en cmd que no se llame `.env.txt`.
- **mysqli falla en silencio sin `mysqli_report(MYSQLI_REPORT_ERROR |
  MYSQLI_REPORT_STRICT)`** — ya está activado en `db()`. No sacarlo.
- **Nunca asumir que un archivo nuevo con un nombre "genérico" (`auth.php`,
  `db_portal.php`, `email.php`) no existe ya** — este proyecto tiene
  historia de que Claude pisó código existente por asumir que era nuevo.
  Ante la duda, pedir o `view` el archivo real del repo primero.
- **Antes de dar por buena una migración de rutas/funciones, buscar TODOS
  los call sites** (`grep -rn`), no solo el archivo que se está editando —
  varias veces una función se actualizó en un lado y quedó una llamada
  vieja sin el parámetro nuevo en otro (ver historial de `openEdit()`).
- **El repo es público y clonable** (`git clone
  https://github.com/andreswebit/tecnomedicphp.git`) — usarlo como fuente
  de verdad antes de asumir el estado de un archivo. `.env`/`.env.production`
  nunca están ahí (gitignored), pedirlos aparte si hacen falta.

## Estado de fases (Portal "Mi Portal")

| Fase | Contenido | Estado |
|---|---|---|
| A | Cuentas/roles, login unificado, aprobación, asignaciones, obras sociales, padrón | ✅ |
| B | Dashboard paciente (datos + turnos) | ✅ |
| C | Dashboard profesional (solo pacientes asignados) | ✅ |
| D | Ficha médica (historia clínica, tratamientos, estudios con archivo) | ✅ |
| E | Registro alimentario (nutrición) | ⏳ |
| F | Recursos/formularios descargables | ⏳ |
| G | Pulido general | ⏳ |
| H | Módulo de Presupuestos (panel + PDF + bot) | ⏳ |

**Login unificado:** admin → `admin/tablero.php` (hub) → Turnos/Pacientes/
DNI/Presupuestos(placeholder)/Tienda. Profesional → su dashboard. Paciente
→ su dashboard.

**Especialidad en turnos:** agregada a nivel BD (`tm_turnos.area`),
formulario público, admin (listado + modal de edición), email de
solicitud. **Falta**: integrarla al bot de WhatsApp (item 13 del backlog).

## Backlog pendiente (numeración original, ver README.md para detalle)

- #3/#10 — Scroll no llega al final en algunas páginas
- #4 — Pestaña de Presupuestos (Fase H)
- #5 — Tienda: productos con precio fijo vs. "a presupuestar" + auto-precios
- #6 — Evaluar alternativas/mejoras a la API de WhatsApp (hoy Twilio sandbox)
- #11 — Formulario de contacto
- #12 — Opción "contactar humano" en el bot → WhatsApp directo a Julieta
- #13 — Opción "Presupuestos" en el bot (pide CUIT/CUIL, responde PDF o "aún no disponible")

## Cómo probar en local

1. XAMPP corriendo (Apache + MySQL en verde en el panel).
2. `.env` local con `MAIL_DRIVER=log`, `DB_*` apuntando a `tecnomedic_local`.
3. Correr los `.sql` de `sql/` en orden (schema_fase_a → a2 → d →
   agregar_area → seeds) — ver nota de arriba si la carpeta no está.
4. Usuarios de prueba: profesionales DNI `30111222`-`30111226` /
   `Tecno2026!`; pacientes DNI `40111001`-`40111010` / `Paciente2026!`.
5. Emails de prueba quedan en `storage/mails_log/*.html`, no salen de
   verdad (por diseño, en local).

## Para Ferozo (producción)

- `.env` con `MAIL_DRIVER=smtp`, credenciales SMTP reales del buzón
  `noreply@tecnomedic.com.ar` (puerto 465, `c1452348.ferozo.com`).
- `vendor/` (PHPMailer) subido a mano por FTP — no vía git.
- Confirmar que `admin/recordatorio.php` (cron diario) siga con el token
  correcto configurado en el cron job de Ferozo.
