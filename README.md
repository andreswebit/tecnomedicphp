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
