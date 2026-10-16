-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Migración: Plan nutricional (Fase E)
-- Agrega estructuras para guardar planes alimentarios semanales y mensuales
-- por paciente.
-- ══════════════════════════════════════════════════════════════

-- Plantilla / plan semanal (una fila por paciente)
CREATE TABLE IF NOT EXISTS tm_plan_nutricional_semanal (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL UNIQUE,
    plan_json        JSON NOT NULL,
    actualizado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por  INT DEFAULT NULL,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES tm_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plan mensual (una fila por paciente por mes)
CREATE TABLE IF NOT EXISTS tm_plan_nutricional_mensual (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id      INT NOT NULL,
    mes              DATE NOT NULL, -- guardamos YYYY-MM-01
    plan_json        JSON NOT NULL,
    actualizado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por  INT DEFAULT NULL,
    FOREIGN KEY (paciente_id) REFERENCES tm_usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (actualizado_por) REFERENCES tm_usuarios(id) ON DELETE SET NULL,
    UNIQUE (paciente_id, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
