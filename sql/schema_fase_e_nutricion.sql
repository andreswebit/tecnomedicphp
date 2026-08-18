-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase E: Registro alimentario (Nutrición)
-- Ejecutar después de Fase A (tm_usuarios) y Fase D (mismo patrón de permisos)
-- ══════════════════════════════════════════════════════════════

-- Objetivo nutricional general (una fila por paciente, se actualiza)
CREATE TABLE IF NOT EXISTS tm_objetivo_nutricional (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL UNIQUE,
    objetivo         TEXT DEFAULT NULL,
    actualizado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por  INT DEFAULT NULL,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES tm_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mediciones antropométricas (peso, altura, IMC) en el tiempo
CREATE TABLE IF NOT EXISTS tm_mediciones (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    fecha            DATE NOT NULL,
    peso             DECIMAL(5,2) DEFAULT NULL,   -- kg
    altura           DECIMAL(5,2) DEFAULT NULL,   -- cm
    imc              DECIMAL(4,2) DEFAULT NULL,   -- calculado en PHP al guardar
    observaciones    VARCHAR(255) DEFAULT NULL,
    registrado_por   INT NOT NULL,
    creado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registro de comidas (diario alimentario)
CREATE TABLE IF NOT EXISTS tm_comidas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    fecha            DATE NOT NULL,
    tipo_comida      ENUM('desayuno','almuerzo','merienda','cena','colacion') NOT NULL,
    descripcion      TEXT NOT NULL,
    registrado_por   INT NOT NULL,
    creado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
