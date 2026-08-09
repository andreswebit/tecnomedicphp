-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Datos de prueba: 1 profesional por área
-- Contraseña para TODOS: Tecno2026!
-- (hash bcrypt válido para password_verify() de PHP)
-- Ejecutar DESPUÉS de schema_fase_a.sql y schema_fase_a2_personas.sql
-- ══════════════════════════════════════════════════════════════

SET @hash = '$2b$10$BQdMw9ypE1rqjohBWeuuC./kLEDnlVBdEAifbVFygSemIaRX3vhWO';

-- Audiología
INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
VALUES ('audiologia.prof@tecnomedic.com.ar','30111222',@hash,'profesional','Marcela','Gómez','3794111222',1,NOW());
INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula)
SELECT id, 'audiologia', 'MP-1001' FROM tm_usuarios WHERE dni='30111222';

-- Medicina hiperbárica
INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
VALUES ('hiperbarica.prof@tecnomedic.com.ar','30111223',@hash,'profesional','Fernando','Ríos','3794111223',1,NOW());
INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula)
SELECT id, 'hiperbarica', 'MP-1002' FROM tm_usuarios WHERE dni='30111223';

-- Nutrición
INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
VALUES ('nutricion.prof@tecnomedic.com.ar','30111224',@hash,'profesional','Laura','Ibáñez','3794111224',1,NOW());
INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula)
SELECT id, 'nutricion', 'MP-1003' FROM tm_usuarios WHERE dni='30111224';

-- Ortopedia y rehabilitación
INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
VALUES ('ortopedia.prof@tecnomedic.com.ar','30111225',@hash,'profesional','Diego','Acosta','3794111225',1,NOW());
INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula)
SELECT id, 'ortopedia', 'MP-1004' FROM tm_usuarios WHERE dni='30111225';

-- Equipamiento médico y quirúrgico
INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion)
VALUES ('equipamiento.prof@tecnomedic.com.ar','30111226',@hash,'profesional','Silvina','Duarte','3794111226',1,NOW());
INSERT INTO tm_perfiles_profesional (usuario_id, area, matricula)
SELECT id, 'equipamiento', 'MP-1005' FROM tm_usuarios WHERE dni='30111226';
