# Graph Report - tecnomedic_php  (2026-10-29)

## Corpus Check
- 162 files · ~4,435,692 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 459 nodes · 705 edges · 128 communities (126 shown, 2 thin omitted)
- Extraction: 79% EXTRACTED · 21% INFERRED · 0% AMBIGUOUS · INFERRED: 150 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `4dd67046`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- db_portal.php
- mensajes-webmail.js
- db.php
- db
- auth.php
- email.php
- helpers.php
- composer.json
- TECNOMEDIC — Sistema Web (turnos + tienda + portal)
- Persistent Agent Memory
- db_presupuestos.php
- persona_upsert
- tienda-ortopedia.js
- mail_accounts.php
- db_novedades.php
- db_recursos.php
- db_nutricion.php
- db_testimonios.php
- testimonios.php
- email_reader.php
- db_contacto.php
- get_emails.php
- tmp_js_check.js
- send_email.php
- guardar_plan_nutricional_profesional.php
- wa_hub_guardar_entrante
- db_config.php

## God Nodes (most connected - your core abstractions)
1. `db()` - 120 edges
2. `procesar_bot()` - 15 edges
3. `novedad_subir_video()` - 14 edges
4. `env()` - 14 edges
5. `novedad_subir_imagen()` - 13 edges
6. `get_mail_account_config()` - 13 edges
7. `imap_open_ferozo()` - 13 edges
8. `imap_mailbox_prefix_ferozo()` - 12 edges
9. `enviar_email()` - 11 edges
10. `TECNOMEDIC — Sistema Web (turnos + tienda + portal)` - 10 edges

## Surprising Connections (you probably didn't know these)
- `recordatorio_manana()` --calls--> `email_recordatorio()`  [INFERRED]
  admin/recordatorio.php → includes/email.php
- `_wa()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php
- `_get_ocupados_todos()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php
- `_buscar_turno_dni_bot()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php
- `_guardar_turno_bot()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php

## Import Cycles
- None detected.

## Communities (128 total, 2 thin omitted)

### Community 0 - "db_portal.php"
Cohesion: 0.08
Nodes (24): asignaciones_todas(), asignar_paciente_profesional(), desasignar(), obras_sociales_todas(), pacientes_de_profesional(), pacientes_todos_activos(), perfil_paciente(), perfil_paciente_actualizar() (+16 more)

### Community 1 - "mensajes-webmail.js"
Cohesion: 0.20
Nodes (25): addFiles(), bindCompose(), bindToolbar(), buildPayload(), cargarCuentasAdmin(), clean(), clearPreview(), fmtDate() (+17 more)

### Community 2 - "db.php"
Cohesion: 0.09
Nodes (29): recordatorio_manana(), nc_normalizar(), _buscar_turno_dni_bot(), _cancelar_turno_bot(), _fechas_con_slots(), _get_ocupados_todos(), _guardar_turno_bot(), _menu_fechas() (+21 more)

### Community 3 - "db"
Cohesion: 0.20
Nodes (18): db(), estudio_crear(), estudio_eliminar(), estudio_get(), estudios_listar(), historia_clinica_actualizar(), historia_clinica_eliminar(), historia_clinica_get() (+10 more)

### Community 4 - "auth.php"
Cohesion: 0.24
Nodes (13): esta_logueado(), iniciar_sesion_php(), portal_current_user(), portal_dashboard_url(), portal_login(), portal_logueado(), portal_require_login(), portal_require_role() (+5 more)

### Community 5 - "email.php"
Cohesion: 0.28
Nodes (15): crear_mail(), _bloque_turno(), email_cancelacion(), email_confirmacion(), email_modificacion(), email_presupuesto_elaborado(), email_presupuesto_solicitud(), email_recordatorio() (+7 more)

### Community 6 - "helpers.php"
Cohesion: 0.25
Nodes (5): b(), tienda_attrs_carrito(), tienda_e(), tienda_img(), tienda_url_producto()

### Community 7 - "composer.json"
Cohesion: 0.25
Nodes (7): config, optimize-autoloader, description, name, require, phpmailer/phpmailer, type

### Community 8 - "TECNOMEDIC — Sistema Web (turnos + tienda + portal)"
Cohesion: 0.12
Nodes (15): 1. Descripción general, 2. Acceso al sistema (login unificado), 3. Estructura de carpetas, 4. Entornos, 5. Roadmap del Portal ("Mi Portal") — actualizado 13/09/2026, 6. Datos de prueba y SQL (entorno local), 7. Backlog original — estado, 8. Pendiente / para completar (+7 more)

### Community 9 - "Persistent Agent Memory"
Cohesion: 0.22
Nodes (8): Before recommending from memory, How to save memories, Memory and other forms of persistence, MEMORY.md, Persistent Agent Memory, Types of memory, What NOT to save in memory, When to access memories

### Community 10 - "db_presupuestos.php"
Cohesion: 0.29
Nodes (7): presupuesto_adjuntar_pdf(), presupuesto_buscar_por_dni_cuit(), presupuesto_crear(), presupuesto_eliminar(), presupuesto_get(), presupuesto_marcar_enviado(), presupuestos_listar()

### Community 55 - "persona_upsert"
Cohesion: 0.22
Nodes (10): crear_turno(), modificar_turno(), persona_upsert(), staff_crear(), staff_editar(), usuario_buscar_login(), usuario_crear_admin(), usuario_crear_paciente() (+2 more)

### Community 56 - "tienda-ortopedia.js"
Cohesion: 0.32
Nodes (3): agregar(), guardar(), pintar()

### Community 57 - "mail_accounts.php"
Cohesion: 0.33
Nodes (3): account_table_exists(), get_account_by_key(), mysqli

### Community 59 - "db_novedades.php"
Cohesion: 0.10
Nodes (41): tm_seccion_de_tipo(), novedad_archivo_en_uso(), novedad_archivo_valido(), novedad_borrar_archivo(), novedad_buscar_duplicado(), novedad_carpeta(), novedad_cerrar_conversion(), novedad_crear() (+33 more)

### Community 60 - "db_recursos.php"
Cohesion: 0.40
Nodes (5): recurso_crear(), recurso_eliminar(), recurso_get(), recursos_listar_publicos(), recursos_listar_todos()

### Community 61 - "db_nutricion.php"
Cohesion: 0.11
Nodes (20): comida_crear(), comida_eliminar(), comida_get(), comidas_listar(), medicion_crear(), medicion_eliminar(), medicion_get(), mediciones_listar() (+12 more)

### Community 64 - "db_testimonios.php"
Cohesion: 0.33
Nodes (5): testimonio_crear(), testimonio_editar(), testimonio_eliminar(), testimonio_get(), testimonios_listar()

### Community 84 - "email_reader.php"
Cohesion: 0.26
Nodes (21): account_table_exists(), decode_imap_body(), get_mail_account_config(), imap_append_ferozo(), imap_create_folder_ferozo(), imap_crlf_ferozo(), imap_delete_folder_ferozo(), imap_delete_message_ferozo() (+13 more)

### Community 85 - "db_contacto.php"
Cohesion: 0.50
Nodes (3): contacto_crear(), contacto_marcar_atendido(), contactos_listar()

### Community 86 - "get_emails.php"
Cohesion: 0.70
Nodes (4): account_table_exists(), _emails_list(), is_active_mail_account(), mysqli

### Community 88 - "tmp_js_check.js"
Cohesion: 0.47
Nodes (3): escapeHtml(), refrescarEmailsLista(), renderEmailsList()

### Community 92 - "send_email.php"
Cohesion: 0.60
Nodes (3): account_table_exists(), is_active_mail_account(), mysqli

### Community 102 - "guardar_plan_nutricional_profesional.php"
Cohesion: 0.60
Nodes (3): plan_sanitizar_dia(), plan_sanitizar_mes(), plan_sanitizar_semana()

### Community 103 - "wa_hub_guardar_entrante"
Cohesion: 0.83
Nodes (3): wa_hub_guardar_entrante(), wa_hub_log(), wa_hub_tablas()

## Knowledge Gaps
- **23 isolated node(s):** `name`, `description`, `type`, `phpmailer/phpmailer`, `optimize-autoloader` (+18 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **2 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `db()` connect `db` to `db_portal.php`, `db_testimonios.php`, `db.php`, `auth.php`, `email.php`, `wa_hub_guardar_entrante`, `db_config.php`, `db_presupuestos.php`, `email_reader.php`, `db_contacto.php`, `persona_upsert`, `db_novedades.php`, `db_recursos.php`, `db_nutricion.php`?**
  _High betweenness centrality (0.249) - this node is a cross-community bridge._
- **Why does `enviar_email()` connect `email.php` to `db`, `email_reader.php`?**
  _High betweenness centrality (0.039) - this node is a cross-community bridge._
- **Why does `get_mail_account_config()` connect `email_reader.php` to `db`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 107 inferred relationships involving `db()` (e.g. with `_buscar_turno_dni_bot()` and `_cancelar_turno_bot()`) actually correct?**
  _`db()` has 107 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `procesar_bot()` (e.g. with `get_sesion()` and `reset_sesion()`) actually correct?**
  _`procesar_bot()` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 5 inferred relationships involving `novedad_subir_video()` (e.g. with `tm_media_error()` and `tm_ratio_es_9_16()`) actually correct?**
  _`novedad_subir_video()` has 5 INFERRED edges - model-reasoned connections that need verification._
- **Are the 13 inferred relationships involving `env()` (e.g. with `crear_mail()` and `enviar_email()`) actually correct?**
  _`env()` has 13 INFERRED edges - model-reasoned connections that need verification._