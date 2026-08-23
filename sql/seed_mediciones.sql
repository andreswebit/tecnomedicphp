-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — 10 mediciones de prueba (Registro alimentario, Fase E)
-- El IMC se calcula solo (peso / (altura en metros)^2)
-- ══════════════════════════════════════════════════════════════

INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-01', 78.5, 165, ROUND(78.5/POWER(1.65,2),2), 'Primera consulta', (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-07-15', 77.3, 165, ROUND(77.3/POWER(1.65,2),2), 'Control quincenal, evolución favorable', (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111004'), '2026-08-01', 76.1, 165, ROUND(76.1/POWER(1.65,2),2), NULL, (SELECT id FROM tm_usuarios WHERE dni='40111004');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-14', 65.0, 158, ROUND(65.0/POWER(1.58,2),2), 'Primera consulta nutricional', (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111008'), '2026-07-28', 64.2, 158, ROUND(64.2/POWER(1.58,2),2), NULL, (SELECT id FROM tm_usuarios WHERE dni='30111224');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111001'), '2026-07-10', 82.0, 172, ROUND(82.0/POWER(1.72,2),2), 'Registrado junto a la sesión de hiperbárica', (SELECT id FROM tm_usuarios WHERE dni='30111222');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111002'), '2026-07-05', 68.4, 160, ROUND(68.4/POWER(1.60,2),2), NULL, (SELECT id FROM tm_usuarios WHERE dni='40111002');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111003'), '2026-06-28', 90.2, 178, ROUND(90.2/POWER(1.78,2),2), 'Derivado por ortopedia', (SELECT id FROM tm_usuarios WHERE dni='30111225');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111009'), '2026-07-20', 55.8, 162, ROUND(55.8/POWER(1.62,2),2), 'Autorregistro desde el Portal', (SELECT id FROM tm_usuarios WHERE dni='40111009');
INSERT INTO tm_mediciones (paciente_id, fecha, peso, altura, imc, observaciones, registrado_por)
SELECT (SELECT id FROM tm_usuarios WHERE dni='40111010'), '2026-07-22', 71.5, 169, ROUND(71.5/POWER(1.69,2),2), NULL, (SELECT id FROM tm_usuarios WHERE dni='40111010');
