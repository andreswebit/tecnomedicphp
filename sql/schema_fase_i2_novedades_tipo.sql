-- ════════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase I.2: tipo de publicación en tm_novedades
--
-- Antes:  tipo ENUM('noticia','novedad')
-- Ahora:  tipo ENUM('tarjeta','noticia')
--   tarjeta → slide del carrusel del index (section id="novedades")
--   noticia → card de la sección id="articulos" del index
--
-- Se ejecuta en local (XAMPP) y en Ferozo (phpMyAdmin).
-- Es seguro correrlo más de una vez.
-- ════════════════════════════════════════════════════════════════

-- 1) Ampliar el ENUM para que acepte los tres valores durante la conversión
ALTER TABLE tm_novedades
    MODIFY tipo ENUM('novedad','tarjeta','noticia') NOT NULL DEFAULT 'tarjeta';

-- 2) Convertir los registros viejos
UPDATE tm_novedades SET tipo = 'tarjeta' WHERE tipo = 'novedad';

-- 3) Dejar solo los valores vigentes
ALTER TABLE tm_novedades
    MODIFY tipo ENUM('tarjeta','noticia') NOT NULL DEFAULT 'tarjeta';
