-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — 10 tratamientos de prueba (Ficha médica, Fase D)
-- Requiere que ya existan los pacientes (DNI 40111001+) y
-- profesionales (DNI 30111222+) de seed_pacientes.sql / seed_profesionales.sql
-- ══════════════════════════════════════════════════════════════

INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111001'), (SELECT id FROM tm_usuarios WHERE dni='30111222'), 'hiperbarica', '2026-07-10', 'Primera sesión de cámara hiperbárica. Tolerancia buena, sin efectos adversos.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111001'), (SELECT id FROM tm_usuarios WHERE dni='30111222'), 'hiperbarica', '2026-07-17', 'Segunda sesión. Mejoría visible en la herida del pie.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111002'), (SELECT id FROM tm_usuarios WHERE dni='30111223'), 'audiologia', '2026-07-05', 'Audiometría tonal. Hipoacusia leve bilateral, se indica control en 6 meses.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111003'), (SELECT id FROM tm_usuarios WHERE dni='30111225'), 'ortopedia', '2026-06-28', 'Evaluación postural inicial. Se indican plantillas correctivas.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), (SELECT id FROM tm_usuarios WHERE dni='30111224'), 'nutricion', '2026-07-01', 'Primera consulta nutricional. Se arma plan alimentario inicial.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), (SELECT id FROM tm_usuarios WHERE dni='30111224'), 'nutricion', '2026-07-15', 'Control de seguimiento. Baja de 1.2kg, buena adherencia al plan.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111005'), (SELECT id FROM tm_usuarios WHERE dni='30111226'), 'equipamiento', '2026-06-20', 'Entrega y ajuste de silla de ruedas. Se explica mantenimiento básico.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111006'), (SELECT id FROM tm_usuarios WHERE dni='30111222'), 'hiperbarica', '2026-07-08', 'Sesión de control, evolución favorable.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111007'), (SELECT id FROM tm_usuarios WHERE dni='30111225'), 'ortopedia', '2026-07-12', 'Sesión de rehabilitación post-quirúrgica de rodilla, ejercicios de movilidad.';
INSERT INTO tm_tratamientos (paciente_id, profesional_id, area, fecha, descripcion)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), (SELECT id FROM tm_usuarios WHERE dni='30111224'), 'nutricion', '2026-07-14', 'Consulta de seguimiento, ajuste de plan por intolerancia detectada.';
