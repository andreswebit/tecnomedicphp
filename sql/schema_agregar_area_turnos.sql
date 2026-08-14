-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Agregar especialidad/área a los turnos
-- Antes los turnos eran todos de un solo servicio (Cámara Hiperbárica).
-- Ahora cada turno indica para qué área es.
-- ══════════════════════════════════════════════════════════════

ALTER TABLE tm_turnos
    ADD COLUMN area VARCHAR(50) DEFAULT 'hiperbarica' AFTER obra_social;

-- Los turnos ya existentes quedan marcados como 'hiperbarica' (que era el
-- único servicio hasta ahora). Si tenías turnos de otras áreas cargados
-- manualmente, corregilos a mano con un UPDATE puntual.
