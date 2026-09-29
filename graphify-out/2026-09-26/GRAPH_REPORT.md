# Graph Report - tecnomedic_php  (2026-09-12)

## Corpus Check
- 111 files · ~4,354,738 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 274 nodes · 368 edges · 84 communities
- Extraction: 70% EXTRACTED · 30% INFERRED · 0% AMBIGUOUS · INFERRED: 109 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `fb4dc7d7`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- db
- procesar_bot
- db.php
- db_ficha.php
- auth.php
- email.php
- tienda.php
- composer.json
- TECNOMEDIC — Sistema Web (turnos + tienda + portal)
- Persistent Agent Memory
- db_presupuestos.php
- db_novedades.php
- db_recursos.php
- db_testimonios.php

## God Nodes (most connected - your core abstractions)
1. `db()` - 104 edges
2. `procesar_bot()` - 15 edges
3. `enviar_email()` - 10 edges
4. `TECNOMEDIC — Sistema Web (turnos + tienda + portal)` - 10 edges
5. `persona_upsert()` - 9 edges
6. `usuario_buscar_login()` - 8 edges
7. `_html_email()` - 8 edges
8. `Persistent Agent Memory` - 8 edges
9. `_bloque_turno()` - 6 edges
10. `_fh()` - 6 edges

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

## Communities (84 total, 0 thin omitted)

### Community 0 - "db"
Cohesion: 0.07
Nodes (52): portal_login(), config_get(), config_set(), contacto_crear(), contacto_marcar_atendido(), contactos_listar(), crear_turno(), db() (+44 more)

### Community 1 - "procesar_bot"
Cohesion: 0.27
Nodes (13): _buscar_turno_dni_bot(), _cancelar_turno_bot(), _fechas_con_slots(), _get_ocupados_todos(), _guardar_turno_bot(), _menu_fechas(), _menu_horarios(), _menu_obras() (+5 more)

### Community 2 - "db.php"
Cohesion: 0.14
Nodes (15): recordatorio_manana(), actualizar_estado(), buscar_turno_dni(), eliminar_turno(), get_ocupados(), get_ocupados_excluyendo(), get_sesion(), get_turno_by_id() (+7 more)

### Community 3 - "db_ficha.php"
Cohesion: 0.20
Nodes (10): estudio_crear(), estudio_eliminar(), estudio_get(), estudios_listar(), historia_clinica_get(), historia_clinica_guardar(), medicamento_crear(), medicamentos_listar() (+2 more)

### Community 4 - "auth.php"
Cohesion: 0.24
Nodes (11): esta_logueado(), iniciar_sesion_php(), portal_current_user(), portal_logueado(), portal_require_login(), portal_require_role(), portal_rol(), requiere_login() (+3 more)

### Community 5 - "email.php"
Cohesion: 0.22
Nodes (17): crear_mail(), _bloque_turno(), email_cancelacion(), email_confirmacion(), email_modificacion(), email_presupuesto_elaborado(), email_presupuesto_solicitud(), email_recordatorio() (+9 more)

### Community 6 - "tienda.php"
Cohesion: 0.28
Nodes (7): get_categoria_by_slug(), get_categorias(), get_familias(), get_familias_con_productos(), get_producto(), get_productos_destacados(), get_productos_por_familia()

### Community 7 - "composer.json"
Cohesion: 0.25
Nodes (7): config, optimize-autoloader, description, name, require, phpmailer/phpmailer, type

### Community 8 - "TECNOMEDIC — Sistema Web (turnos + tienda + portal)"
Cohesion: 0.13
Nodes (14): 1. Descripción general, 2. Acceso al sistema (login unificado), 3. Estructura de carpetas, 4. Entornos, 5. Roadmap del Portal ("Mi Portal"), 6. Datos de prueba y SQL (entorno local), 7. Backlog original — estado, 8. Pendiente / para completar (+6 more)

### Community 9 - "Persistent Agent Memory"
Cohesion: 0.22
Nodes (8): Before recommending from memory, How to save memories, Memory and other forms of persistence, MEMORY.md, Persistent Agent Memory, Types of memory, What NOT to save in memory, When to access memories

### Community 10 - "db_presupuestos.php"
Cohesion: 0.29
Nodes (7): presupuesto_adjuntar_pdf(), presupuesto_buscar_por_dni_cuit(), presupuesto_crear(), presupuesto_eliminar(), presupuesto_get(), presupuesto_marcar_enviado(), presupuestos_listar()

### Community 59 - "db_novedades.php"
Cohesion: 0.29
Nodes (5): novedad_crear(), novedad_editar(), novedad_eliminar(), novedad_get(), novedades_listar()

### Community 60 - "db_recursos.php"
Cohesion: 0.40
Nodes (5): recurso_crear(), recurso_eliminar(), recurso_get(), recursos_listar_publicos(), recursos_listar_todos()

### Community 61 - "db_testimonios.php"
Cohesion: 0.33
Nodes (5): testimonio_crear(), testimonio_editar(), testimonio_eliminar(), testimonio_get(), testimonios_listar()

## Knowledge Gaps
- **23 isolated node(s):** `name`, `description`, `type`, `phpmailer/phpmailer`, `optimize-autoloader` (+18 more)
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `db()` connect `db` to `procesar_bot`, `db.php`, `db_ficha.php`, `auth.php`, `tienda.php`, `db_presupuestos.php`, `db_novedades.php`, `db_recursos.php`, `db_testimonios.php`?**
  _High betweenness centrality (0.325) - this node is a cross-community bridge._
- **Why does `recordatorio_manana()` connect `db.php` to `email.php`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **Why does `get_turnos()` connect `db.php` to `db`?**
  _High betweenness centrality (0.093) - this node is a cross-community bridge._
- **Are the 91 inferred relationships involving `db()` (e.g. with `_buscar_turno_dni_bot()` and `_cancelar_turno_bot()`) actually correct?**
  _`db()` has 91 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `procesar_bot()` (e.g. with `get_sesion()` and `reset_sesion()`) actually correct?**
  _`procesar_bot()` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 2 inferred relationships involving `enviar_email()` (e.g. with `crear_mail()` and `env()`) actually correct?**
  _`enviar_email()` has 2 INFERRED edges - model-reasoned connections that need verification._
- **Are the 4 inferred relationships involving `persona_upsert()` (e.g. with `crear_turno()` and `modificar_turno()`) actually correct?**
  _`persona_upsert()` has 4 INFERRED edges - model-reasoned connections that need verification._