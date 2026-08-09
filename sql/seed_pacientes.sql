-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Datos de prueba: 10 pacientes
-- Contraseña para TODOS: Paciente2026!
-- (hash bcrypt válido para password_verify() de PHP)
-- Quedan activo=1 (aprobados) directamente, para no tener que
-- aprobar uno por uno mientras probás. Ejecutar DESPUÉS de
-- schema_fase_a.sql, schema_fase_a2_personas.sql y seed_profesionales.sql
-- ══════════════════════════════════════════════════════════════

SET @hash = '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe';

INSERT INTO tm_usuarios (email,dni,password_hash,rol,nombre,apellido,telefono,activo,fecha_aprobacion) VALUES
('paciente1@test.com','40111001',@hash,'paciente','Juan','Pérez','3794111001',1,NOW()),
('paciente2@test.com','40111002',@hash,'paciente','María','González','3794111002',1,NOW()),
('paciente3@test.com','40111003',@hash,'paciente','Carlos','Fernández','3794111003',1,NOW()),
('paciente4@test.com','40111004',@hash,'paciente','Ana','López','3794111004',1,NOW()),
('paciente5@test.com','40111005',@hash,'paciente','Roberto','Martínez','3794111005',1,NOW()),
('paciente6@test.com','40111006',@hash,'paciente','Lucía','Sánchez','3794111006',1,NOW()),
('paciente7@test.com','40111007',@hash,'paciente','Pablo','Romero','3794111007',1,NOW()),
('paciente8@test.com','40111008',@hash,'paciente','Sofía','Torres','3794111008',1,NOW()),
('paciente9@test.com','40111009',@hash,'paciente','Miguel','Flores','3794111009',1,NOW()),
('paciente10@test.com','40111010',@hash,'paciente','Valentina','Benítez','3794111010',1,NOW());

-- Perfil de paciente con obra social y fecha de nacimiento
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='PAMI'), '1955-03-12' FROM tm_usuarios u WHERE u.dni='40111001';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='OSDE'), '1988-07-22' FROM tm_usuarios u WHERE u.dni='40111002';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='IOSCOR'), '1992-01-05' FROM tm_usuarios u WHERE u.dni='40111003';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='Swiss Medical'), '1975-11-30' FROM tm_usuarios u WHERE u.dni='40111004';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='Particular / Sin cobertura'), '2001-05-18' FROM tm_usuarios u WHERE u.dni='40111005';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='Galeno'), '1968-09-09' FROM tm_usuarios u WHERE u.dni='40111006';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='Medifé'), '1983-02-14' FROM tm_usuarios u WHERE u.dni='40111007';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='Sancor Salud'), '1995-12-01' FROM tm_usuarios u WHERE u.dni='40111008';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='PAMI'), '1950-06-25' FROM tm_usuarios u WHERE u.dni='40111009';
INSERT INTO tm_perfiles_paciente (usuario_id, obra_social_id, fecha_nacimiento)
SELECT u.id, (SELECT id FROM tm_obras_sociales WHERE nombre='OSDE'), '1999-04-03' FROM tm_usuarios u WHERE u.dni='40111010';

-- Sincroniza el padrón único (tm_personas) con estos 10 pacientes
INSERT INTO tm_personas (dni, nombre, apellido, telefono, email, obra_social_id)
SELECT u.dni, u.nombre, u.apellido, u.telefono, u.email, pp.obra_social_id
FROM tm_usuarios u JOIN tm_perfiles_paciente pp ON pp.usuario_id = u.id
WHERE u.dni IN ('40111001','40111002','40111003','40111004','40111005',
                '40111006','40111007','40111008','40111009','40111010')
ON DUPLICATE KEY UPDATE
    nombre=VALUES(nombre), apellido=VALUES(apellido),
    telefono=VALUES(telefono), email=VALUES(email), obra_social_id=VALUES(obra_social_id);
