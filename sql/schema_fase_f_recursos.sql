-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase F: Recursos / formularios descargables
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS tm_recursos (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    titulo            VARCHAR(200) NOT NULL,
    descripcion       VARCHAR(500) DEFAULT NULL,
    area              VARCHAR(50)  DEFAULT NULL,  -- audiologia|hiperbarica|nutricion|ortopedia|equipamiento|NULL=general
    archivo_nombre    VARCHAR(255) NOT NULL,        -- nombre original del archivo
    archivo_ruta      VARCHAR(500) NOT NULL,        -- ruta relativa en storage/recursos/
    tipo              VARCHAR(20)  DEFAULT NULL,     -- PDF, Imagen, etc.
    publico           TINYINT(1)   NOT NULL DEFAULT 0, -- 1 = visible en el sitio sin login
    subido_por        INT NOT NULL,
    creado_en         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subido_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
