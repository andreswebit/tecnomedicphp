-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase A: Cuentas, roles y asignaciones
-- Ejecutar en phpMyAdmin (local y luego en Ferozo)
-- Prefijo tm_ para no chocar con las tablas wp_ de WordPress
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS tm_usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(150) NOT NULL UNIQUE,
    dni             VARCHAR(20)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('paciente','profesional','admin') NOT NULL DEFAULT 'paciente',
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    telefono        VARCHAR(30)  DEFAULT NULL,
    activo          TINYINT(1)   NOT NULL DEFAULT 0,  -- 0=pendiente aprobación, 1=aprobado
    fecha_alta      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion DATETIME    DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_perfiles_paciente (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id        INT NOT NULL UNIQUE,
    fecha_nacimiento  DATE DEFAULT NULL,
    obra_social       VARCHAR(100) DEFAULT NULL,
    observaciones     TEXT DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_perfiles_profesional (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id   INT NOT NULL UNIQUE,
    area         ENUM('audiologia','hiperbarica','nutricion','ortopedia','equipamiento') NOT NULL,
    matricula    VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_asignaciones (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id       INT NOT NULL,
    profesional_id    INT NOT NULL,
    area              VARCHAR(50) DEFAULT NULL,
    fecha_asignacion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activa            TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY paciente_profesional (paciente_id, profesional_id),
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (profesional_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: el primer usuario admin se crea con el script crear_admin_temporal.php
-- (no se inserta acá porque el password_hash debe generarse con password_hash() de PHP)
