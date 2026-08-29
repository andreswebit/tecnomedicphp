-- ════════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase I: Gestión de Contenido Dinámico
-- ════════════════════════════════════════════════════════════════

-- Novedades/Noticias
CREATE TABLE IF NOT EXISTS tm_novedades (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    titulo        VARCHAR(200) NOT NULL,
    contenido     TEXT NOT NULL,
    imagen        VARCHAR(255) DEFAULT NULL,
    video_url     VARCHAR(500) DEFAULT NULL,
    categoria     VARCHAR(50) DEFAULT NULL,
    tipo          ENUM('noticia','novedad') DEFAULT 'novedad',
    orden         INT DEFAULT 0,
    activo        TINYINT(1) DEFAULT 1,
    creado_en     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Staff Médico Destacado
CREATE TABLE IF NOT EXISTS tm_staff (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    apellido      VARCHAR(100) NOT NULL,
    titulo        VARCHAR(150) DEFAULT NULL,
    especialidad  VARCHAR(100) DEFAULT NULL,
    descripcion   TEXT DEFAULT NULL,
    foto          VARCHAR(255) DEFAULT NULL,
    instagram     VARCHAR(100) DEFAULT NULL,
    orden         INT DEFAULT 0,
    activo        TINYINT(1) DEFAULT 1,
    creado_en     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonios de Pacientes
CREATE TABLE IF NOT EXISTS tm_testimonios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    rol           VARCHAR(100) DEFAULT NULL,
    texto         TEXT NOT NULL,
    resultado     VARCHAR(255) DEFAULT NULL,
    media_tipo    ENUM('imagen','video','ninguno') DEFAULT 'ninguno',
    media_url     VARCHAR(500) DEFAULT NULL,
    video_thumb   VARCHAR(255) DEFAULT NULL,
    orden         INT DEFAULT 0,
    activo        TINYINT(1) DEFAULT 1,
    creado_en     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Configuración General
CREATE TABLE IF NOT EXISTS tm_config (
    clave         VARCHAR(100) PRIMARY KEY,
    valor         TEXT DEFAULT NULL,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Semilla de configuración inicial
INSERT INTO tm_config (clave, valor) VALUES
('hero_video_url', 'https://www.youtube.com/embed/IqcZl86vtXU')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);