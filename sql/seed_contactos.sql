-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — 10 mensajes de contacto de prueba
-- Ejecutar después de schema_contactos.sql
-- ══════════════════════════════════════════════════════════════

INSERT INTO tm_contactos (nombre, email, telefono, motivo, mensaje, atendido, creado_en) VALUES
('Marcela Funes', 'marcela.funes@test.com', '3794112201', 'consulta_general', 'Hola, quería saber el horario de atención de audiología los sábados.', 0, NOW() - INTERVAL 1 DAY),
('Ricardo Aguirre', 'ricardo.aguirre@test.com', '3794112202', 'turnos', 'Necesito reprogramar mi turno de la semana que viene, no puedo asistir.', 1, NOW() - INTERVAL 5 DAY),
('Yolanda Cabrera', 'yolanda.cabrera@test.com', '3794112203', 'presupuesto', 'Quisiera saber si atienden con OSDE para cámara hiperbárica.', 0, NOW() - INTERVAL 2 DAY),
('Hugo Espínola', 'hugo.espinola@test.com', '3794112204', 'tienda', 'Consulto stock de bastones ortopédicos regulables.', 1, NOW() - INTERVAL 10 DAY),
('Norma Villalba', 'norma.villalba@test.com', '3794112205', 'consulta_general', '¿Atienden los feriados?', 0, NOW() - INTERVAL 3 HOUR),
('Ariel Duarte', 'ariel.duarte@test.com', '3794112206', 'otro', 'Quiero dejar una sugerencia sobre el estacionamiento del centro.', 1, NOW() - INTERVAL 15 DAY),
('Silvia Rolón', 'silvia.rolon@test.com', '3794112207', 'turnos', 'No me llegó el mail de confirmación de mi turno de nutrición.', 0, NOW() - INTERVAL 6 HOUR),
('Pablo Servín', 'pablo.servin@test.com', '3794112208', 'presupuesto', 'Necesito presupuesto para sesiones de fonoaudiología, mi hijo de 6 años.', 0, NOW() - INTERVAL 1 DAY),
('Claudia Ojeda', 'claudia.ojeda@test.com', '3794112209', 'consulta_general', 'Buenas tardes, ¿hacen estudios de audiometría sin turno previo?', 1, NOW() - INTERVAL 20 DAY),
('Fabián Barrios', 'fabian.barrios@test.com', '3794112210', 'tienda', 'Consulto por sillas de ruedas plegables, precio y financiación.', 0, NOW() - INTERVAL 4 DAY);
