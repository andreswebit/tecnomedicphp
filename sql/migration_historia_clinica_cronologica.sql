-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Historia clínica como historial cronológico (FINAL)
-- Estado verificado con SHOW INDEX (usuario): PRIMARY (id), actualizado_por.
-- NO existe UNIQUE/INDEX paciente_id en esta base.
-- La columna fecha YA existe (error #1060 confirmado). Se omite ADD COLUMN.
-- ══════════════════════════════════════════════════════════════

-- Si aún persiste un índice paciente_id en otra instancia, eliminar manualmente:
-- ALTER TABLE tm_historia_clinica DROP INDEX paciente_id;

-- Índice para ordenar el historial cronológicamente (DESC por fecha, luego id DESC)
CREATE INDEX IF NOT EXISTS `idx_historia_pacienteFecha` ON `tm_historia_clinica` (`paciente_id`, `fecha` DESC, `id` DESC);
