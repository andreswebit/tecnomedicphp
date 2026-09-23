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

-- Ficha profesional: información contextual, una fila actual por paciente.
CREATE TABLE IF NOT EXISTS tm_fichas_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL UNIQUE,
    sexo_biologico ENUM('Masc','Fem','Otro') DEFAULT NULL,
    ocupacion VARCHAR(180) DEFAULT NULL,
    patologias TEXT DEFAULT NULL,
    medicacion TEXT DEFAULT NULL,
    suplementacion TEXT DEFAULT NULL,
    actualizado_por INT DEFAULT NULL,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES tm_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Controles históricos profesionales. Los valores se guardan sin interpretación automática.
CREATE TABLE IF NOT EXISTS tm_antropometrias_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, fecha DATE NOT NULL,
    peso DECIMAL(6,2) DEFAULT NULL, altura DECIMAL(6,2) DEFAULT NULL, imc DECIMAL(5,2) DEFAULT NULL,
    cintura DECIMAL(6,2) DEFAULT NULL, cadera DECIMAL(6,2) DEFAULT NULL,
    brazo_relajado DECIMAL(6,2) DEFAULT NULL, brazo_contraido DECIMAL(6,2) DEFAULT NULL, muslo_medial DECIMAL(6,2) DEFAULT NULL,
    registrado_por INT NOT NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_composiciones_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, fecha DATE NOT NULL,
    pliegue_tricipital DECIMAL(6,2) DEFAULT NULL, pliegue_bicipital DECIMAL(6,2) DEFAULT NULL, pliegue_subescapular DECIMAL(6,2) DEFAULT NULL,
    pliegue_suprailíaco DECIMAL(6,2) DEFAULT NULL, pliegue_abdominal DECIMAL(6,2) DEFAULT NULL, pliegue_muslo_anterior DECIMAL(6,2) DEFAULT NULL, pliegue_pierna_medial DECIMAL(6,2) DEFAULT NULL,
    porcentaje_grasa DECIMAL(6,2) DEFAULT NULL, masa_grasa DECIMAL(6,2) DEFAULT NULL, porcentaje_muscular DECIMAL(6,2) DEFAULT NULL,
    masa_muscular DECIMAL(6,2) DEFAULT NULL, porcentaje_agua DECIMAL(6,2) DEFAULT NULL, grasa_visceral DECIMAL(6,2) DEFAULT NULL, imc DECIMAL(5,2) DEFAULT NULL,
    registrado_por INT NOT NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_laboratorios_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, fecha DATE NOT NULL,
    glucosa DECIMAL(8,2) DEFAULT NULL, hba1c DECIMAL(6,2) DEFAULT NULL, insulina DECIMAL(8,2) DEFAULT NULL,
    colesterol_total DECIMAL(8,2) DEFAULT NULL, colesterol_ldl DECIMAL(8,2) DEFAULT NULL, colesterol_hdl DECIMAL(8,2) DEFAULT NULL, trigliceridos DECIMAL(8,2) DEFAULT NULL,
    got DECIMAL(8,2) DEFAULT NULL, gpt DECIMAL(8,2) DEFAULT NULL, creatinina DECIMAL(8,2) DEFAULT NULL, urea DECIMAL(8,2) DEFAULT NULL, acido_urico DECIMAL(8,2) DEFAULT NULL,
    hemoglobina DECIMAL(8,2) DEFAULT NULL, hematocrito DECIMAL(6,2) DEFAULT NULL, ferritina DECIMAL(8,2) DEFAULT NULL, hierro DECIMAL(8,2) DEFAULT NULL,
    tsh DECIMAL(8,2) DEFAULT NULL, t3_libre DECIMAL(8,2) DEFAULT NULL, t4_libre DECIMAL(8,2) DEFAULT NULL, vitamina_d DECIMAL(8,2) DEFAULT NULL, vitamina_b12 DECIMAL(8,2) DEFAULT NULL, acido_folico DECIMAL(8,2) DEFAULT NULL,
    registrado_por INT NOT NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tm_habitos_nutricionales (
    id INT AUTO_INCREMENT PRIMARY KEY, paciente_id INT NOT NULL, fecha DATE NOT NULL,
    presion_sistolica DECIMAL(7,2) DEFAULT NULL, presion_diastolica DECIMAL(7,2) DEFAULT NULL, horas_sueno DECIMAL(5,2) DEFAULT NULL,
    calidad_sueno ENUM('Mala','Regular','Buena','Excelente') DEFAULT NULL, bristol TINYINT DEFAULT NULL, frecuencia_evacuacion DECIMAL(5,2) DEFAULT NULL,
    estres TINYINT DEFAULT NULL, actividad_fisica ENUM('Sedentario','Ligero','Moderado','Intenso') DEFAULT NULL, agua DECIMAL(5,2) DEFAULT NULL,
    registrado_por INT NOT NULL, creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES tm_usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
