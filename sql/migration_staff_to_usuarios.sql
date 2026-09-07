-- ════════════════════════════════════════════════════════════════
-- TECNOMEDIC — Migrar tm_staff → tm_usuarios
-- Ejecutar DESPUÉS de:
--   - schema_fase_a.sql
--   - schema_fase_i_contenido.sql
--   - seeds de profesionales
-- Idempotente: se puede correr varias veces sin error.
-- ════════════════════════════════════════════════════════════════

-- 1) Agregar columnas del staff a tm_usuarios (solo si no existen)
-- MySQL no tiene "ADD COLUMN IF NOT EXISTS" hasta 8.0, así que
-- usamos INFORMATION_SCHEMA para que sea idempotente en 5.7/8.0.

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'titulo') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN titulo VARCHAR(150) DEFAULT NULL AFTER apellido',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'especialidad') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN especialidad VARCHAR(100) DEFAULT NULL AFTER titulo',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'descripcion') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN descripcion TEXT DEFAULT NULL AFTER especialidad',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'foto') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER descripcion',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'instagram') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN instagram VARCHAR(100) DEFAULT NULL AFTER foto',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'tm_usuarios'
        AND COLUMN_NAME = 'orden') = 0,
    'ALTER TABLE tm_usuarios ADD COLUMN orden INT DEFAULT 0 AFTER instagram',
    'SELECT 1'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Migrar datos de tm_staff → tm_usuarios
--    Solo si tm_staff todavía existe (idempotente después de eliminarla).
SET @staff_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'tm_staff');

SET @sql = IF(@staff_exists > 0,
    "UPDATE tm_usuarios u
     INNER JOIN tm_staff s ON u.nombre = s.nombre AND u.apellido = s.apellido
     SET u.titulo = s.titulo,
         u.especialidad = s.especialidad,
         u.descripcion = s.descripcion,
         u.foto = s.foto,
         u.instagram = s.instagram,
         u.orden = s.orden",
    "SELECT 1"
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Eliminar tm_staff (solo si existe)
SET @sql = IF(@staff_exists > 0,
    'DROP TABLE IF EXISTS tm_staff',
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4) Verificación
SELECT 'Profesionales con datos de staff' AS origen, COUNT(*) AS cantidad
FROM tm_usuarios
WHERE rol = 'profesional'
  AND (titulo IS NOT NULL OR instagram IS NOT NULL OR foto IS NOT NULL);
