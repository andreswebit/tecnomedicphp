-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Formulario de contacto
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS tm_contactos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    telefono    VARCHAR(30)  DEFAULT NULL,
    motivo      VARCHAR(50)  DEFAULT NULL,
    mensaje     TEXT NOT NULL,
    atendido    TINYINT(1)   NOT NULL DEFAULT 0,
    creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
