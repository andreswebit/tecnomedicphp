-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase D: Ficha médica (historia clínica, tratamientos, estudios)
-- Ejecutar después de todo lo de Fase A+B (usuarios, asignaciones, etc.)
-- ══════════════════════════════════════════════════════════════

-- Resumen general del paciente (antecedentes, diagnóstico). Una fila por paciente.
CREATE TABLE IF NOT EXISTS tm_historia_clinica (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL UNIQUE,
    antecedentes     TEXT DEFAULT NULL,
    diagnostico      TEXT DEFAULT NULL,
    observaciones    TEXT DEFAULT NULL,
    actualizado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por  INT DEFAULT NULL,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES tm_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tratamientos / sesiones realizadas, uno por fila (historial cronológico)
CREATE TABLE IF NOT EXISTS tm_tratamientos (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id    INT NOT NULL,
    profesional_id INT NOT NULL,
    area           VARCHAR(50) DEFAULT NULL,
    fecha          DATE NOT NULL,
    descripcion    TEXT NOT NULL,
    creado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (profesional_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Estudios / archivos adjuntos (PDFs, imágenes de estudios, etc.)
CREATE TABLE IF NOT EXISTS tm_estudios (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    subido_por       INT NOT NULL,
    nombre_original  VARCHAR(255) NOT NULL,
    ruta_archivo     VARCHAR(500) NOT NULL,
    tipo             VARCHAR(20) DEFAULT NULL,
    notas            VARCHAR(255) DEFAULT NULL,
    fecha_estudio    DATE DEFAULT NULL,
    subido_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (subido_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Medicamentos recetados (propuesta compacta - Fase D+)
CREATE TABLE IF NOT EXISTS tm_medicamentos_recetados (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id         INT NOT NULL,
    nombre_comercial    VARCHAR(255) NOT NULL COMMENT 'Nombre de la marca/fórmula',
    principio_activo    VARCHAR(255) DEFAULT NULL COMMENT 'Droga / principio activo',
    dosis               VARCHAR(100) DEFAULT NULL,
    frecuencia          VARCHAR(100) DEFAULT NULL COMMENT 'Cómo y cuándo tomarlo',
    via_administracion  VARCHAR(100) DEFAULT NULL COMMENT 'Oral, subcutánea, IV, etc.',
    estado              ENUM('activo','suspendido','finalizado') NOT NULL DEFAULT 'activo',
    fecha_receta        DATE DEFAULT NULL,
    creado_en           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
