-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — 10 comidas de prueba (Registro alimentario, Fase E)
-- ══════════════════════════════════════════════════════════════

INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-20', 'desayuno', 'Tostadas integrales con palta y huevo, café con leche descremada', (SELECT id FROM tm_usuarios WHERE dni='40111004');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-20', 'almuerzo', 'Pechuga de pollo grillada con ensalada de hojas verdes y tomate', (SELECT id FROM tm_usuarios WHERE dni='40111004');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-20', 'merienda', 'Yogur descremado con granola casera', (SELECT id FROM tm_usuarios WHERE dni='40111004');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-20', 'cena', 'Salmón al horno con vegetales grillados', (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-21', 'desayuno', 'Licuado de banana con avena y leche de almendras', (SELECT id FROM tm_usuarios WHERE dni='40111008');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-21', 'colacion', 'Puñado de almendras y una manzana', (SELECT id FROM tm_usuarios WHERE dni='40111008');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-21', 'almuerzo', 'Guiso de lentejas con verduras, porción moderada', (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-21', 'cena', 'Tortilla de vegetales con ensalada', (SELECT id FROM tm_usuarios WHERE dni='40111008');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111009'), '2026-07-22', 'desayuno', 'Mate cocido con dos tostadas de pan integral y queso untable', (SELECT id FROM tm_usuarios WHERE dni='40111009');
INSERT INTO tm_comidas (paciente_id, fecha, tipo_comida, descripcion, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111010'), '2026-07-22', 'merienda', 'Infusión con dos galletas de arroz y mermelada light', (SELECT id FROM tm_usuarios WHERE dni='40111010');
