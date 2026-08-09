# TECNOMEDIC — Guía de instalación en Ferozo
## Subdominio: test.tecnomedic.com.ar

---

## PASO 1 — Crear las tablas en phpMyAdmin

1. Ferozo → **phpMyAdmin** → seleccioná la base `c1452348_wordpre`
2. Pestaña **SQL** → pegá el contenido de `instalar.sql` → **Ejecutar**
3. Verificá que se crearon `tm_turnos` y `tm_sesiones_bot` (aparecen en la barra lateral)

> Las tablas usan prefijo `tm_` para NO interferir con las tablas `wp_` de WordPress.

---

## PASO 2 — Configurar credenciales

Abrí `includes/db.php` y completá:

```php
define('ADMIN_PASS', 'tu-contraseña-admin');   // ← cambiá esto
define('BREVO_API_KEY', 'xkeysib-...');         // ← tu clave Brevo
define('TWILIO_SID',    'ACxxxxxxx');           // ← tu SID Twilio
define('TWILIO_TOKEN',  'xxxxxxxx');            // ← tu token Twilio
```

Los datos de MySQL ya están pre-cargados con tus credenciales de Ferozo.

---

## PASO 3 — Subir archivos por FileManager

### Estructura en Ferozo:
```
public_html/
└── test/               ← carpeta del subdominio
    ├── .htaccess
    ├── index.php
    ├── turnos.php
    ├── guardar.php
    ├── confirmacion.php
    ├── instalar.sql     (podés borrarlo después de ejecutarlo)
    ├── includes/
    │   ├── db.php
    │   ├── email.php
    │   ├── whatsapp.php
    │   └── auth.php
    ├── admin/
    │   ├── index.php
    │   ├── login.php
    │   ├── logout.php
    │   ├── actualizar.php
    │   ├── modificar.php
    │   ├── eliminar.php
    │   └── recordatorio.php
    ├── api/
    │   └── horarios.php
    ├── bot/
    │   └── webhook.php
    └── static/
        ├── css/
        │   ├── base.css
        │   ├── index.css
        │   ├── form.css
        │   └── admin.css
        └── img/
            └── tecno-logo.jpeg   ← copiá tu logo acá
```

### Cómo subir por FileManager:
1. Ferozo → **FileManager** → navegá a `public_html/test/`
2. Subí los archivos manteniendo la misma estructura de carpetas
3. Para las carpetas: creá la carpeta primero, entrá, y subí los archivos adentro

---

## PASO 4 — Copiar el logo

El logo debe estar en: `public_html/test/static/img/tecno-logo.jpeg`

Si ya lo tenés en el servidor (en el sitio WordPress), podés copiarlo desde FileManager
sin descargarlo: clic derecho → Copiar → pegalo en la nueva ruta.

---

## PASO 5 — Verificar el subdominio

1. Ferozo → **Subdominios** → verificá que `test` apunte a `public_html/test`
2. Abrí `https://test.tecnomedic.com.ar` → debería verse la página principal
3. Probá `https://test.tecnomedic.com.ar/turnos.php` → formulario de turnos
4. Probá `https://test.tecnomedic.com.ar/admin/` → login del panel

---

## PASO 6 — Configurar cron para recordatorios

1. Ferozo → **Cron Jobs** → **Agregar cron**
2. Frecuencia: **Diariamente a las 09:00**
3. Comando:
```
/usr/bin/curl -s "https://test.tecnomedic.com.ar/admin/recordatorio.php?token=TM_CRON_2025" > /dev/null
```
4. Guardá

> Podés cambiar `TM_CRON_2025` por cualquier token secreto. Actualizalo también en `admin/recordatorio.php`.

---

## PASO 7 — Configurar webhook de WhatsApp (Twilio)

En la consola de Twilio → WhatsApp Sandbox → **Webhook URL**:
```
https://test.tecnomedic.com.ar/bot/webhook.php
```
Método: **HTTP POST**

---

## URLs del sistema

| Sección | URL |
|---|---|
| Inicio | `https://test.tecnomedic.com.ar/` |
| Turnos | `https://test.tecnomedic.com.ar/turnos.php` |
| Admin  | `https://test.tecnomedic.com.ar/admin/` |
| Bot WA | `https://test.tecnomedic.com.ar/bot/webhook.php` |
| API horarios | `https://test.tecnomedic.com.ar/api/horarios.php?fecha=2025-07-15` |

---

## Notas importantes

- **WordPress NO se toca**: las tablas de Tecnomedic usan prefijo `tm_`, completamente separadas de `wp_`
- **Sin Render, sin GitHub**: todo vive en Ferozo
- **Sin cold starts**: servidor PHP siempre activo
- **Cron nativo**: Ferozo incluye cron jobs en el panel, sin cron-job.org externo
- **PHP 8.2**: el `.htaccess` lo fuerza; compatible con toda la sintaxis usada


<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

# TECNOMEDIC — Sistema Web (turnos + tienda + portal)

Sistema web para **TecnoMedic**, centro médico multiespecialidad en Corrientes,
Argentina (Audiología, Medicina Hiperbárica, Nutrición, Ortopedia y
Rehabilitación, Equipamiento Médico y Quirúrgico) — C. Pellegrini 799,
tel. (3794) 34-9278.

Incluye: sitio público, reserva de turnos online, bot de WhatsApp, tienda de
productos, panel de administración de turnos, y un portal de pacientes /
profesionales ("Mi Portal") con ficha médica.

> Este README refleja el estado real del repositorio a partir de una
> auditoría completa hecha el 09/08/2026 (ver sección 7).

---

## 1. Descripción general

El proyecto empezó como una app Python/Flask sobre Google Sheets (desplegada
en Render) y fue migrado completamente a **PHP + MySQL**, corriendo en
hosting compartido de **Ferozo**. Todas las tablas propias usan el prefijo
`tm_` porque la base de datos (`c1452348_wordpre`) es **compartida con un
WordPress** de otro cliente (tablas `wp_*`).

### Stack técnico
- **Backend:** PHP puro + `mysqli` (sin framework)
- **Base de datos:** MySQL, base `c1452348_wordpre`, tablas `tm_*`
- **Config/secretos:** archivo `.env` (propio, sin Composer) vía
  `includes/env_loader.php` — nunca se sube a git
- **Email:** dos motores intercambiables por `MAIL_DRIVER`:
  - `smtp` → PHPMailer + SMTP directo del buzón `noreply@tecnomedic.com.ar`
    en Ferozo (`c1452348.ferozo.com`, puerto 465, SSL implícito)
  - `log` → no envía nada, guarda el mail como `.html` en
    `storage/mails_log/` (para desarrollo local, ya que Ferozo bloquea SMTP
    saliente desde IPs externas)
  - (código legado de Brevo API queda comentado en `includes/email.php` por
    si se quiere volver a esa opción)
- **WhatsApp:** Twilio (sandbox `whatsapp:+14155238886`), vía
  `CURLOPT_USERPWD`
- **Dependencias PHP:** Composer, solo para `phpmailer/phpmailer`
  (`composer.json`/`composer.lock` sí se versionan; `vendor/` no — hay que
  correr `composer install` y subir esa carpeta a mano por FTP en Ferozo)
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
  Playfair Display + DM Sans — panel admin de turnos
- Paleta: lima `#98c544`, teal `#1aa5a5`, navy `#0d1b2a`, texto `#454545`,
  fondo claro `#edf1f0`

---

## 2. Estructura de carpetas

```
tecnomedic_php/
├── index.php, turnos.php, confirmacion.php, guardar.php   → sitio público / reserva de turnos
├── login.php, register.php, logout.php, pendiente.php     → acceso al Portal
├── composer.json, composer.lock                            → dependencia PHPMailer
├── .env.example                                             → plantilla de config (SIN secretos, sí se sube)
├── .env                                                     → config real (NUNCA se sube, gitignored)
├── admin/
│   ├── login.php, logout.php, index.php                    → panel de turnos (histórico, 1 usuario/clave fijo)
│   ├── actualizar.php, modificar.php, eliminar.php, recordatorio.php
│   ├── portal_usuarios.php, consultar_dni.php               → panel del Portal (aprobar pacientes, asignar, etc.)
│   └── tienda_section.php
├── api/horarios.php                                         → endpoint de disponibilidad de turnos
├── areas/                                                   → landing por especialidad
├── bot/webhook.php + .htaccess                              → webhook de WhatsApp (Twilio)
├── includes/
│   ├── db.php                                                → conexión + CRUD turnos/bot
│   ├── db_portal.php                                         → CRUD usuarios/perfiles/asignaciones/padrón
│   ├── db_ficha.php                                          → CRUD historia clínica/tratamientos/estudios
│   ├── auth.php                                              → sesión y permisos (panel turnos + Portal)
│   ├── email.php, config_mail.php                            → envío de mail (SMTP o log)
│   ├── env_loader.php                                        → parser liviano de .env
│   ├── portal_header.php / portal_footer.php                 → layout común del Portal + modal de ficha médica
│   └── areas_data.php, whatsapp.php, tienda.php
├── portal/
│   ├── css/portal.css
│   ├── paciente/dashboard.php, perfil.php
│   ├── profesional/dashboard.php
│   └── ficha/                                                → Fase D: ver.php + handlers (historia, tratamientos, estudios)
├── storage/
│   ├── mails_log/                                            → mails en modo "log" (gitignored)
│   └── estudios/{paciente_id}/                               → archivos subidos, protegidos por .htaccess
├── static/                                                   → CSS/imágenes/videos del sitio público
├── tienda/                                                   → catálogo de productos (legado)
└── sql/                                                       → ⚠️ ver nota en sección 7 (no está commiteada)
```

---

## 3. Entornos

| Entorno     | URL                                  | Base de datos                  |
|-------------|---------------------------------------|---------------------------------|
| Local       | `http://localhost/tecnomedic_php`     | `tecnomedic_local` (XAMPP root, sin password) |
| Producción  | `https://tecnomedic.com.ar` (operativa) + `https://test2.tecnomedic.com.ar` (home) | `c1452348_wordpre` (Ferozo) |

`.env` en local usa `MAIL_DRIVER=log`; en Ferozo, `MAIL_DRIVER=smtp`.

---

## 4. Roadmap del Portal ("Mi Portal")

| Fase | Contenido | Estado |
|------|-----------|--------|
| **A** | Cuentas y roles (paciente/profesional/admin), login por email o DNI, aprobación de pacientes por admin, asignación paciente↔profesional, catálogo de obras sociales, padrón único por DNI (`tm_personas`) | ✅ Hecho |
| **B** | Portal paciente MVP: dashboard con datos propios y turnos, edición de perfil | ✅ Hecho |
| **C** | Portal profesional MVP: ver solo los pacientes asignados | ✅ Hecho |
| **D** | **Ficha médica**: historia clínica, tratamientos realizados, carga/descarga de estudios (PDF/imagen) en un modal pop-up, con permisos por rol | ✅ Hecho |
| **E** | Registro alimentario (nutrición) | ⏳ Pendiente |
| **F** | Recursos / formularios descargables | ⏳ Pendiente |
| **G** | Pulido general (UX, validaciones, responsive) | ⏳ Pendiente |
| **H** | Módulo de Presupuestos (panel tipo turnos + PDF adjunto + integración con el bot) | ⏳ Pendiente |

### Permisos por rol (Fase D)
| | Paciente | Profesional | Admin |
|---|---|---|---|
| Ve su propia ficha médica | ✅ | — | — |
| Ve ficha de sus pacientes asignados | — | ✅ | ✅ (todos) |
| Edita historia clínica / tratamientos / estudios | ❌ (solo lectura) | ✅ (solo asignados) | ✅ (todos) |

---

## 5. Backlog original — estado

1. Botón historial por paciente → ✅ resuelto (es la ficha médica, Fase D)
2. Más usuarios con privilegios admin → ✅ resuelto vía Portal (roles + múltiples admins); el panel viejo de turnos sigue con 1 usuario/clave fijo, sin cambios ahí
3. Scroll de página no llega al final → ⏳ pendiente
4. Pestaña de Presupuestos → ⏳ pendiente (Fase H)
5. Tienda: productos con precio fijo vs. "a presupuestar", catálogo con actualización automática de precios → ⏳ pendiente
6. WhatsApp: evaluar opciones de API → ⏳ pendiente
7. Horarios que se repetían/duplicaban → ✅ resuelto
8. No enviaba email → ✅ resuelto (SMTP directo de Ferozo)
9. No avisaba al cancelar/modificar → ✅ resuelto
10. Scroll no se desplaza en admin/, no se ve la página completa → ⏳ pendiente (posible duplicado del #3, a confirmar)
11. Formulario de contacto → ⏳ pendiente
12. Opción "Contactar a un humano" en el bot (WhatsApp directo a Julieta) → ⏳ pendiente
13. Opción "Presupuestos" en el bot (pide CUIT/CUIL, devuelve PDF o avisa que aún no está listo) → ⏳ pendiente
14. Mostrar horarios disponibles al modificar un turno → ✅ resuelto

**Bonus resuelto en el camino (no estaba en la lista original):**
- Fechas guardándose como `0000-00-00` (columna `DATE` real vs. texto `DD/MM/YYYY`)
- Horas mostrándose como `19:00:00.00000` en vez de `19:00`
- Secretos hardcodeados en `db.php` → migrados a `.env`

---

## 6. Datos de prueba (entorno local)

- **Profesionales** (uno por área) — DNI `30111222` a `30111226` — contraseña `Tecno2026!`
- **Pacientes** (10) — DNI `40111001` a `40111010` — contraseña `Paciente2026!`, ya aprobados

El primer usuario **admin** del Portal se crea una única vez con
`crear_admin_temporal.php` (borrar el archivo del servidor después de
usarlo — está gitignoreado para que no se suba por accidente).

---

## 7. Auditoría de estructura (09/08/2026)

Se revisó el repo completo en GitHub: todos los `require`/`require_once`
(rutas relativas con `__DIR__`) y todas las rutas armadas con `b('/...')`,
verificando que apunten a archivos que realmente existen.

### 🔴 Bug encontrado y corregido
- **`pendiente.php`** tenía las rutas rotas `__DIR__ . '/portal/portal_header.php'`
  y `.../portal_footer.php'` (esa carpeta/archivo no existe — los archivos
  reales están en `includes/`). Esto significa que **cualquier paciente que
  se registrara veía una pantalla de error fatal** en vez del mensaje de
  "cuenta pendiente de aprobación". Ya corregido — falta subir el archivo a
  Ferozo.

### 🟡 Archivos para borrar (huérfanos o inseguros, no se usan en ningún lado)
- **`admin/config_mail.php`** — copia duplicada de `includes/config_mail.php`,
  además rota (hace `require __DIR__.'/db.php'`, que no existe en `admin/`).
  Nada lo referencia. Seguro borrarlo.
- **`admin/test_mail.php`** — script de prueba de envío de mail **sin
  ningún control de autenticación** (no llama a `requiere_login()` ni
  `portal_require_login()`). Cualquiera que conozca la URL puede
  dispararlo. Borrar del servidor, o si se lo quiere mantener, agregarle
  `requiere_login()` al principio.
- **`static/portal.css`** — copia vieja y desactualizada de
  `portal/css/portal.css` (le falta todo el CSS del modal de ficha médica
  de la Fase D). Nada la referencia. Seguro borrarla.

### 🟢 Verificado sin problemas
- Todos los demás `require_once` con `__DIR__` resuelven correctamente.
- Todas las rutas `b('/...')` usadas en el código apuntan a archivos que
  existen.
- No quedan URLs `localhost` hardcodeadas fuera del valor por defecto en
  `env()` (que es justamente el fallback correcto para desarrollo local).
- Sesiones del panel de turnos (`$_SESSION['tm_logged']`) y del Portal
  (`$_SESSION['portal_*']`) conviven sin pisarse en `includes/auth.php`.

### ⚪ Para completar
- **La carpeta `sql/`** (esquemas de Fase A, Fase D, seeds de prueba) **no
  está commiteada al repo** — hoy solo existe como archivos sueltos que se
  fueron pasando por chat. Recomendado: agregarla al repo para que cualquiera
  que clone el proyecto pueda reconstruir la base de datos sin depender del
  historial de conversación.
- Falta `static/img/favicon.ico` (404 menor, cosmético, no urgente).

---

## 8. Sugerencias

- **Seguridad:** el panel de turnos histórico (`admin/login.php`) sigue
  usando un usuario/clave único compartido. Si varias personas del equipo
  necesitan acceso con trazabilidad (saber quién hizo qué cambio), conviene
  migrarlo al mismo sistema de roles del Portal más adelante.
- **Backups:** con datos clínicos reales entrando al sistema (Fase D), vale
  la pena confirmar que Ferozo esté haciendo backups automáticos de la base
  y de `storage/estudios/`, o armar un backup propio (cron + mysqldump).
- **Límite de tamaño/cantidad de estudios:** hoy no hay límite de espacio
  en disco para `storage/estudios/` — si el uso crece, conviene monitorear
  el espacio del hosting.
- **`admin/recordatorio.php`** (cron diario) — confirmar que el cron job en
  Ferozo siga apuntando a la URL correcta después de todos estos cambios de
  `.env`/rutas.
- **Orden sugerido para lo que sigue:** cerrar los bugs chicos pendientes
  (#3/#10 scroll, #11 contacto) antes de arrancar con algo grande como
  Fase H (Presupuestos), ya que esta última toca varias partes a la vez
  (formulario, PDF, storage, bot).