-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase A (extensión): Padrón único + catálogo de obras sociales
-- Ejecutar DESPUÉS de schema_fase_a.sql
-- ══════════════════════════════════════════════════════════════

-- Catálogo de obras sociales, común a portal y (a futuro) al formulario de turnos
CREATE TABLE IF NOT EXISTS tm_obras_sociales (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL UNIQUE,
    activa  TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO tm_obras_sociales (nombre) VALUES
('Particular / Sin cobertura'),
('PAMI'),
('IOSCOR'),
('OSDE'),
('Swiss Medical'),
('Galeno'),
('Medifé'),
('Sancor Salud'),
('Unión Personal'),
('OSDEPYM'),
('Jerárquicos Salud'),
('Apross'),
('Otra');

-- Padrón único: una fila por DNI, cruzando lo que exista en tm_usuarios y tm_turnos.
-- No reemplaza esas tablas: es un registro liviano que se actualiza (upsert) cada vez
-- que alguien se registra en el portal o pide un turno, para poder responder rápido
-- "¿este DNI ya está en algún lado?" sin tener que tocar el resto del sistema.
CREATE TABLE IF NOT EXISTS tm_personas (
    dni              VARCHAR(20) PRIMARY KEY,
    nombre           VARCHAR(100) DEFAULT NULL,
    apellido         VARCHAR(100) DEFAULT NULL,
    telefono         VARCHAR(30)  DEFAULT NULL,
    email            VARCHAR(150) DEFAULT NULL,
    obra_social_id   INT DEFAULT NULL,
    actualizado      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (obra_social_id) REFERENCES tm_obras_sociales(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- tm_perfiles_paciente pasa a usar el catálogo en vez de texto libre
ALTER TABLE tm_perfiles_paciente
    ADD COLUMN obra_social_id INT DEFAULT NULL AFTER obra_social,
    ADD FOREIGN KEY (obra_social_id) REFERENCES tm_obras_sociales(id) ON DELETE SET NULL;
-- (dejamos la columna vieja "obra_social" de texto por compatibilidad; se puede
--  borrar más adelante con: ALTER TABLE tm_perfiles_paciente DROP COLUMN obra_social;)
