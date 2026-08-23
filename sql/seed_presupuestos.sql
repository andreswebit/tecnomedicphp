-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — 10 solicitudes de presupuesto de prueba
-- Ejecutar después de schema_fase_h_presupuestos.sql
-- Quedan todas en estado 'Pendiente' (el estado real de arranque):
-- así podés practicar el flujo completo subiendo un PDF y
-- enviándolo desde admin/presupuestos.php.
-- Algunos DNI/CUIT coinciden con los pacientes de prueba
-- (40111001-40111010) para poder probar después la consulta por
-- WhatsApp/bot cruzando por ese dato.
-- ══════════════════════════════════════════════════════════════

INSERT INTO tm_presupuestos (nombre, apellido, dni_cuit, telefono, email, area, descripcion, estado, creado_en) VALUES
('Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NOW() - INTERVAL 1 DAY),
('María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NOW() - INTERVAL 3 DAY),
('Carlos', 'López', '40111003', '3794000003', 'carlos.lopez@mail.com', 'ortopedia', 'Presupuesto de plantillas ortopédicas a medida.', 'Pendiente', NOW() - INTERVAL 6 HOUR),
('Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NOW() - INTERVAL 8 DAY),
('Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NOW() - INTERVAL 2 DAY),
('Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NOW() - INTERVAL 12 HOUR),
('Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NOW() - INTERVAL 4 DAY),
('Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NOW() - INTERVAL 5 DAY),
('Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NOW() - INTERVAL 30 MINUTE),
('Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NOW() - INTERVAL 15 DAY);
