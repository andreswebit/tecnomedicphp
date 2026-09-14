# TECNOMEDIC — Sistema Web (turnos + tienda + portal)

Sistema web para **TecnoMedic**, centro médico multiespecialidad en Corrientes,
Argentina (Audiología, Medicina Hiperbárica, Nutrición, Ortopedia y
Rehabilitación, Equipamiento Médico y Quirúrgico) — C. Pellegrini 799,
tel. (3794) 34-9278.

Incluye: sitio público, reserva de turnos online (con selección de
especialidad), bot de WhatsApp, tienda de productos, formulario de
contacto, y "Mi Portal" — un sistema unificado de login con tablero según
rol, que incluye ficha médica y registro alimentario.

> Este README se reescribió completo el 13/08/2026 para reflejar el
> estado real del repositorio (reemplaza cualquier versión anterior).

---

## 1. Descripción general

El proyecto empezó como una app Python/Flask sobre Google Sheets (desplegada
en Render) y fue migrado completamente a **PHP + MySQL**, corriendo en
hosting compartido de **Ferozo**. Todas las tablas propias usan el prefijo
`tm_` porque la base de datos (`c1452348_wordpre`) es **compartida con un
WordPress** de otro cliente (tablas `wp_*`).

### Stack técnico
- **Backend:** PHP puro + `mysqli` (sin framework), `mysqli_report` activado
  para que los errores de SQL no fallen en silencio
- **Base de datos:** MySQL, base `c1452348_wordpre`, tablas `tm_*`
- **Config/secretos:** archivo `.env` (propio, sin Composer) vía
  `includes/env_loader.php` — nunca se sube a git
- **Email:** dos motores intercambiables por `MAIL_DRIVER`:
  - `smtp` → PHPMailer + SMTP directo del buzón `noreply@tecnomedic.com.ar`
    en Ferozo (`c1452348.ferozo.com`, puerto 465, SSL implícito)
  - `log` → no envía nada, guarda el mail como `.html` en
    `storage/mails_log/` (para desarrollo local, ya que Ferozo bloquea SMTP
    saliente desde IPs externas)
- **WhatsApp:** Twilio (sandbox `whatsapp:+14155238886`), vía `CURLOPT_USERPWD`
- **Dependencias PHP:** Composer, solo para `phpmailer/phpmailer`
  (`composer.json`/`composer.lock` se versionan; `vendor/` no — hay que
  correr `composer install` local y subir esa carpeta a mano por FTP a Ferozo)
- **Hosting:** Ferozo shared hosting (`c145.ferozo.com`, usuario `c1452348`)
- **Dominios:** `tecnomedic.com.ar` (parte operativa: turnos, admin, portal)
  + `test2.tecnomedic.com.ar` (home) — separados porque el plan de Ferozo
  solo permite un certificado HTTPS por dominio
- **DNS:** Hostmar (ns3/ns4.hostmar.com)
- **Dev local:** XAMPP en Windows, proyecto en
  `D:\Users\andresperal\Documents\Proyectos Python\tecnomedic_php`,
  `DocumentRoot` de Apache apuntando directo ahí
- **Editor:** VS Code
- **Repo:** https://github.com/andreswebit/tecnomedicphp.git (público)

### Identidad visual
- Tipografías: Poppins (títulos) + Montserrat (cuerpo) — home/portal;
  Playfair Display + DM Sans — panel de turnos y login
- Paleta: lima `#98c544`, teal `#1aa5a5`, navy `#0d1b2a`, texto `#454545`,
  fondo claro `#edf1f0`

---

## 2. Acceso al sistema (login unificado)

Desde el Home, botón **"Mi Portal"** → `/login.php` (acepta email o DNI +
contraseña). Según el rol, redirige a:

| Rol | Destino |
|---|---|
| `paciente` | `portal/paciente/dashboard.php` — datos propios, turnos, ficha médica (solo lectura), registro alimentario propio |
| `profesional` | `portal/profesional/dashboard.php` — solo sus pacientes **asignados**; ficha médica y registro alimentario editables |
| `admin` | `admin/tablero.php` — hub central con accesos a Turnos / Pacientes / Profesionales / Usuarios / Consultar DNI / Mensajes / Tienda / Presupuestos (placeholder) |

El panel de turnos histórico (antes con usuario/clave fijo en
`admin/login.php`) **ya está unificado** bajo este mismo login — las
páginas `admin/index.php`, `actualizar.php`, `modificar.php`,
`eliminar.php` usan `portal_require_role(['admin'])`. `admin/login.php`
quedó como simple redirect a `/login.php` para no romper accesos guardados.

**Alta de más administradores:** desde `admin/usuarios.php` (rol `admin`
puede crear/editar/eliminar cualquier usuario). Ya no depende de
`crear_admin_temporal.php`.

---

## 3. Estructura de carpetas

```
tecnomedic_php/
├── index.php                          → Home (sitio público)
├── turnos.php, guardar.php, confirmacion.php   → reserva de turnos (con selector de especialidad)
├── contacto.php                        → formulario de contacto público
├── login.php, register.php, logout.php, pendiente.php   → acceso unificado al Portal
├── composer.json, composer.lock        → dependencia PHPMailer
├── .env.example                        → plantilla de config (sin secretos, sí se sube)
├── .env                                → config real (nunca se sube, gitignored)
├── admin/
│   ├── login.php, logout.php           → redirects al login unificado (compatibilidad)
│   ├── index.php, actualizar.php, modificar.php, eliminar.php, recordatorio.php   → panel de turnos
│   ├── tablero.php                     → hub central post-login para admin
│   ├── pacientes.php                   → aprobar cuentas + ver ficha médica de cada paciente
│   ├── profesionales.php               → alta de profesionales + asignación paciente↔profesional
│   ├── usuarios.php                    → crear/editar/eliminar cualquier usuario (solo admin)
│   ├── contactos.php                   → mensajes recibidos por el formulario de contacto
│   ├── consultar_dni.php               → cruce del padrón (tm_personas) por DNI
│   ├── portal_usuarios.php             → redirect (se dividió en pacientes.php + profesionales.php)
│   └── tienda_section.php
├── api/horarios.php                    → disponibilidad de turnos (por fecha Y área/especialidad)
├── areas/                              → landing por especialidad
├── bot/webhook.php + .htaccess         → webhook de WhatsApp (Twilio)
├── includes/
│   ├── db.php                          → conexión + CRUD turnos/bot
│   ├── db_portal.php                   → CRUD usuarios/perfiles/asignaciones/obras sociales/padrón
│   ├── db_ficha.php                    → CRUD historia clínica/tratamientos/estudios + control de permisos (ficha_puede_ver/editar)
│   ├── db_nutricion.php                → CRUD objetivo nutricional/mediciones/comidas (reutiliza permisos de db_ficha.php)
│   ├── db_contacto.php                 → CRUD mensajes de contacto
│   ├── auth.php                        → sesión unificada (login Portal + compatibilidad legado)
│   ├── email.php, config_mail.php      → envío de mail (SMTP o log)
│   ├── env_loader.php                  → parser liviano de .env
│   ├── portal_header.php / portal_footer.php   → layout Portal + modal de ficha médica
│   └── areas_data.php, whatsapp.php, tienda.php
├── portal/
│   ├── css/portal.css
│   ├── paciente/dashboard.php, perfil.php
│   ├── profesional/dashboard.php
│   ├── ficha/                          → Fase D: ver.php + handlers (se abre como modal pop-up)
│   └── nutricion/                      → Fase E: registro.php + handlers (página propia, no modal)
├── storage/
│   ├── mails_log/                      → mails en modo "log" (gitignored)
│   └── estudios/{paciente_id}/         → archivos subidos, protegidos por .htaccess
├── static/                             → CSS/imágenes/videos del sitio público (incluye tecnomedic.css: login + panel de turnos)
├── tienda/                             → catálogo de productos (legado)
└── sql/                                → esquemas y datos de prueba (ver sección 6)
```

---

## 4. Entornos

| Entorno     | URL                                  | Base de datos                  |
|-------------|---------------------------------------|---------------------------------|
| Local       | `http://localhost/tecnomedic_php`     | `tecnomedic_local` (XAMPP root, sin password) |
| Producción  | `https://tecnomedic.com.ar` (operativa) + `https://test2.tecnomedic.com.ar` (home) | `c1452348_wordpre` (Ferozo) |

`.env` en local usa `MAIL_DRIVER=log`; en Ferozo, `MAIL_DRIVER=smtp`.

---

## 5. Roadmap del Portal ("Mi Portal") — actualizado 13/09/2026

| Fase | Contenido | Estado |
|------|-----------|--------|
| **A** | Login unificado, roles, aprobación, asignaciones, obras sociales, padrón (`tm_personas`) | ✅ Hecho |
| **B** | Portal paciente: datos + turnos | ✅ Hecho |
| **C** | Portal profesional: solo asignados | ✅ Hecho |
| **D** | Ficha médica (historia clínica **cronológica** con columna `fecha`, tratamientos, estudios, modal pop-up) | ✅ Hecho — historia clínica ahora es historial (cada modificación inserta fila con `CURDATE()`; `tm_historia_clinica.fecha` creado vía SQL) |
| **E** | Registro alimentario (nutrición) | ✅ Hecho |
| **F** | Recursos / formularios descargables | ⏳ Pendiente |
| **G** | Pulido general (UX, responsive, validaciones) | ⏳ Pendiente |
| **H** | Módulo Presupuestos (panel + PDF + bot) | ⏳ Pendiente |

### Permisos por rol (Fase D — Ficha médica)
| | Paciente | Profesional | Admin |
|---|---|---|---|
| Ve su propia ficha médica | ✅ | — | — |
| Ve ficha de sus pacientes asignados | — | ✅ | ✅ (todos) |
| Edita historia clínica / tratamientos / estudios | ❌ (solo lectura) | ✅ (solo asignados) | ✅ (todos) |

### Permisos por rol (Fase E — Registro alimentario)
| | Paciente | Profesional | Admin |
|---|---|---|---|
| Ve su propio registro | ✅ | — | — |
| Ve registro de sus pacientes asignados | — | ✅ | ✅ (todos) |
| Carga sus propias comidas/mediciones | ✅ | ✅ | ✅ |
| Define/edita objetivo nutricional | ❌ | ✅ (solo asignados) | ✅ (todos) |
| Borra comidas/mediciones | ❌ | ✅ (solo asignados) | ✅ (todos) |

Ambos reutilizan el mismo control de permisos (`ficha_puede_ver()` /
`ficha_puede_editar()` en `db_ficha.php`) — no hay un sistema de permisos
distinto por feature.

---

## 6. Datos de prueba y SQL (entorno local)

Correr en orden en phpMyAdmin (local y, la primera vez, también en Ferozo):

1. `schema_fase_a.sql` — usuarios, roles, asignaciones
2. `schema_fase_a2_personas.sql` — obras sociales + padrón (tm_personas)
3. `schema_fase_d_ficha_medica.sql` — historia clínica, tratamientos, estudios
4. `schema_fase_e_nutricion.sql` — objetivo nutricional, mediciones, comidas
5. `schema_agregar_area_turnos.sql` — columna de especialidad en tm_turnos
6. `schema_contactos.sql` — mensajes de contacto
7. `seed_profesionales.sql` + `seed_pacientes.sql` — datos de prueba (opcional, solo local)

**Usuarios de prueba:**
- Profesionales (uno por área) — DNI `30111222` a `30111226` — contraseña `Tecno2026!`
- Pacientes (10) — DNI `40111001` a `40111010` — contraseña `Paciente2026!`, ya aprobados

El primer admin del sistema se crea una única vez con
`crear_admin_temporal.php` (borrar del servidor después de usarlo — está
gitignoreado). Admins adicionales: desde `admin/usuarios.php`.

---

## 7. Backlog original — estado

1. Botón historial por paciente → ✅ resuelto (ficha médica, Fase D)
2. Más usuarios con privilegios admin → ✅ resuelto (login unificado + `admin/usuarios.php`)
3. Scroll de página no llega al final → ✅ resuelto (era el sidebar del panel de turnos: le faltaba `overflow-y:auto` y había un bloque CSS viejo conflictivo que ocultaba "Cerrar sesión" con `display:none` en pantallas angostas)
4. Pestaña de Presupuestos → ⏳ pendiente (Fase H)
5. Tienda: precio fijo vs. "a presupuestar" + auto-actualización de precios → ⏳ pendiente
6. WhatsApp: evaluar opciones de API → ⏳ pendiente
7. Horarios que se repetían/duplicaban → ✅ resuelto
8. No enviaba email → ✅ resuelto (SMTP directo de Ferozo)
9. No avisaba al cancelar/modificar → ✅ resuelto
10. Scroll no se desplaza en admin/ → ✅ resuelto (mismo bug que #3)
11. Formulario de contacto → ✅ resuelto (`contacto.php` + `admin/contactos.php`)
12. Opción "Contactar a un humano" en el bot (WhatsApp directo a Julieta) → ⏳ pendiente
13. Opción "Presupuestos" en el bot → ⏳ pendiente (depende de Fase H)
14. Mostrar horarios disponibles al modificar un turno → ✅ resuelto

**Bonus resuelto en el camino (no estaba en la lista original):**
- Fechas guardándose como `0000-00-00` (columna `DATE` real vs. texto `DD/MM/YYYY` — se resuelve con `STR_TO_DATE`/`DATE_FORMAT` en cada consulta)
- Horas mostrándose como `19:00:00.00000` en vez de `19:00`
- Secretos hardcodeados en `db.php` → migrados a `.env`
- Especialidad/área agregada a los turnos (web + admin), con cupos de horario independientes por área
- Link roto a `nutreando.com` en el Home (quedó de cuando se usó como referencia de diseño) → reemplazado por el login propio
- Archivos huérfanos/inseguros eliminados: `admin/config_mail.php` (duplicado roto), `admin/test_mail.php` (sin autenticación), `static/portal.css` (copia vieja)
- `pendiente.php` tenía rutas rotas que tiraban error fatal a cualquier paciente recién registrado

---

### Funcionalidades confirmadas (ya operativas — no pendientes)
- ✅ Botón **Editar** ficha médica → abre modal global `admin/pacientes.php` (`openEdit`) con datos pre-cargados
- ✅ Tooltip eliminado en botón **Nuevo paciente** (`btn-no-tooltip`)
- ✅ Historia clínica: **Modificar** → **Guardar** (botón verde, único), fondo editable `#fff`, toast `✅ Historia clínica guardada`, scroll pestañas `ficha-tabs`
- ✅ Edición inline datos personales (`toggleEdicionDatos`) con formulario oculto/visible y cancelación
- ✅ Modal ficha médica (`abrirFicha`) carga `ver.php` vía `fetch`; botón **Editar** delega a `abrirEdicionPaciente()`; puente funciona con `portal_header.php`
- ✅ Botón **Modificar** historia alterna modos (`abrirEdicionHistoria`/`toggleEdicionHistoria`) y guarda vía `guardarHistoria()` (fetch `guardar_historia.php`)
- ✅ Ficha médica no aparece vacía tras migración DB (`fecha` columna creada, índice aplicado, `INSERT` con `CURDATE()`)
- ✅ `db_ficha.php`: `historia_clinica_get()` ahora devuelve `array` ordenado `fecha DESC`; `historia_clinica_guardar()` inserta fila nueva (cronológico)

---

## 8. Pendiente / para completar

- **Bot de WhatsApp:** todavía no pregunta la especialidad al reservar (el sitio web sí), no tiene opción de "contactar humano", ni de consultar presupuestos.
- **Fase F, G, H** del roadmap del Portal.
- **`static/img/favicon.ico`** falta (404 menor, cosmético).
- **Backups:** con datos clínicos reales entrando al sistema (Fases D y E), confirmar que Ferozo haga backups automáticos de la base y de `storage/estudios/`, o armar uno propio (cron + mysqldump).
- **Límite de espacio para `storage/estudios/`:** hoy no hay límite, monitorear si crece mucho.
- **`admin/recordatorio.php`** (cron diario): confirmar que el cron job en Ferozo siga con el token y la URL correctos tras los cambios de `.env`/rutas.

---

## 9. Convenciones de trabajo

- No se modifican archivos existentes si se puede evitar: funcionalidad
  nueva se agrega vía includes separados (`db_ficha.php`, `db_nutricion.php`,
  `db_contacto.php`, etc.) para no romper lo que ya está en producción.
- Antes de asumir el estado de un archivo, clonar el repo real
  (`git clone https://github.com/andreswebit/tecnomedicphp.git`) en vez de
  confiar en versiones pasadas por chat — hay historial de archivos que se
  pisaron por asumir que eran nuevos cuando ya existían.
- Ver `CLAUDE.md` en la raíz del repo para contexto denso pensado
  específicamente para sesiones de IA (arquitectura de auth, gotchas de
  `fecha`/`hora` como columnas `DATE`/`TIME` reales, etc.).
- Se prueba siempre primero en XAMPP local antes de subir a Ferozo — y
  recordar correr el SQL nuevo en **ambas** bases (local y Ferozo), no solo
  una.
