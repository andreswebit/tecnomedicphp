# Graph Report - .  (2026-08-13)

## Corpus Check
- Large corpus: 106 files · ~1,385,511 words. Semantic extraction will be expensive (many Claude tokens). Consider running on a subfolder.

## Summary
- 177 nodes · 249 edges · 59 communities
- Extraction: 73% EXTRACTED · 27% INFERRED · 0% AMBIGUOUS · INFERRED: 66 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Community 0
- Community 1
- Community 2
- Community 3
- Community 4
- Community 5
- Community 6
- Community 7
- Community 8
- Community 9
- Community 10

## God Nodes (most connected - your core abstractions)
1. `db()` - 63 edges
2. `procesar_bot()` - 15 edges
3. `enviar_email()` - 8 edges
4. `usuario_buscar_login()` - 7 edges
5. `_html_email()` - 6 edges
6. `_bloque_turno()` - 6 edges
7. `_fh()` - 6 edges
8. `email_solicitud()` - 6 edges
9. `email_recordatorio()` - 6 edges
10. `crear_mail()` - 5 edges

## Surprising Connections (you probably didn't know these)
- `recordatorio_manana()` --calls--> `email_recordatorio()`  [INFERRED]
  admin/recordatorio.php → includes/email.php
- `_wa()` --calls--> `enviar_whatsapp()`  [INFERRED]
  bot/webhook.php → includes/whatsapp.php
- `_get_ocupados_todos()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php
- `_buscar_turno_dni_bot()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php
- `_guardar_turno_bot()` --calls--> `db()`  [INFERRED]
  bot/webhook.php → includes/db.php

## Import Cycles
- None detected.

## Communities (59 total, 0 thin omitted)

### Community 0 - "Community 0"
Cohesion: 0.14
Nodes (30): crear_turno(), db(), asignaciones_todas(), asignar_paciente_profesional(), desasignar(), obras_sociales_todas(), pacientes_de_profesional(), pacientes_todos_activos() (+22 more)

### Community 1 - "Community 1"
Cohesion: 0.27
Nodes (13): _buscar_turno_dni_bot(), _cancelar_turno_bot(), _fechas_con_slots(), _get_ocupados_todos(), _guardar_turno_bot(), _menu_fechas(), _menu_horarios(), _menu_obras() (+5 more)

### Community 2 - "Community 2"
Cohesion: 0.19
Nodes (12): actualizar_estado(), buscar_turno_dni(), eliminar_turno(), get_ocupados(), get_ocupados_excluyendo(), get_sesion(), get_turno_by_id(), get_turnos_por_fecha() (+4 more)

### Community 3 - "Community 3"
Cohesion: 0.21
Nodes (11): portal_rol(), estudio_crear(), estudio_eliminar(), estudio_get(), estudios_listar(), ficha_puede_editar(), ficha_puede_ver(), historia_clinica_get() (+3 more)

### Community 4 - "Community 4"
Cohesion: 0.27
Nodes (9): esta_logueado(), iniciar_sesion_php(), portal_current_user(), portal_login(), portal_logueado(), portal_require_login(), portal_require_role(), requiere_login() (+1 more)

### Community 5 - "Community 5"
Cohesion: 0.56
Nodes (10): _bloque_turno(), email_cancelacion(), email_confirmacion(), email_modificacion(), email_recordatorio(), email_solicitud(), enviar_email(), _fh() (+2 more)

### Community 6 - "Community 6"
Cohesion: 0.28
Nodes (7): get_categoria_by_slug(), get_categorias(), get_familias(), get_familias_con_productos(), get_producto(), get_productos_destacados(), get_productos_por_familia()

### Community 7 - "Community 7"
Cohesion: 0.25
Nodes (7): config, optimize-autoloader, description, name, require, phpmailer/phpmailer, type

### Community 8 - "Community 8"
Cohesion: 0.29
Nodes (5): crear_mail(), env(), PHPMailer, PHPMailer\PHPMailer\Exception, PHPMailer\PHPMailer\PHPMailer

### Community 9 - "Community 9"
Cohesion: 0.40
Nodes (4): recordatorio_manana(), get_turnos(), enviar_whatsapp(), formatear_wa()

### Community 10 - "Community 10"
Cohesion: 0.50
Nodes (3): contacto_crear(), contacto_marcar_atendido(), contactos_listar()

## Knowledge Gaps
- **5 isolated node(s):** `name`, `description`, `type`, `phpmailer/phpmailer`, `optimize-autoloader`
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `db()` connect `Community 0` to `Community 1`, `Community 2`, `Community 3`, `Community 6`, `Community 9`, `Community 10`?**
  _High betweenness centrality (0.335) - this node is a cross-community bridge._
- **Why does `recordatorio_manana()` connect `Community 9` to `Community 5`?**
  _High betweenness centrality (0.144) - this node is a cross-community bridge._
- **Why does `get_turnos()` connect `Community 9` to `Community 0`, `Community 2`?**
  _High betweenness centrality (0.131) - this node is a cross-community bridge._
- **Are the 50 inferred relationships involving `db()` (e.g. with `_buscar_turno_dni_bot()` and `_cancelar_turno_bot()`) actually correct?**
  _`db()` has 50 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `procesar_bot()` (e.g. with `get_sesion()` and `reset_sesion()`) actually correct?**
  _`procesar_bot()` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `enviar_email()` (e.g. with `crear_mail()` and `env()`) actually correct?**
  _`enviar_email()` has 2 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `usuario_buscar_login()` (e.g. with `portal_login()` and `db()`) actually correct?**
  _`usuario_buscar_login()` has 2 INFERRED edges - model-reasoned connections that need verification._