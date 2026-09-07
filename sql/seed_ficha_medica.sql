-- ============================================================
-- TECNOMEDIC - Datos de prueba: Ficha médica completa
-- (historia clínica, medicamentos recetados, estudios)
-- Ejecutar DESPUES de:
--   - schema_fase_a.sql
--   - schema_fase_a2_personas.sql
--   - schema_fase_d_ficha_medica.sql (incluye tm_medicamentos_recetados)
--   - seed_profesionales.sql
--   - seed_pacientes.sql
--   - seed_tratamientos.sql
-- ============================================================

-- ============================================================
-- HISTORIA CLINICA (usa ON DUPLICATE KEY - idempotente)
-- ============================================================

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Hipertension arterial controlada desde 2010. Ex tabaquista (dejo en 2018). Sin alergias conocidas.',
    'Sindrome metabolico en tratamiento',
    'Paciente en control por pie diabetico. Se evalua continuar con camara hiperbarica.',
    (SELECT id FROM tm_usuarios WHERE dni='30111222')
FROM tm_usuarios WHERE dni='40111001'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Sin antecedentes patologicos relevantes. Embarazo y parto sin complicaciones.',
    'Hipoacusia leve bilateral',
    'Trabaja en ambiente ruidoso. Se recomienda uso de proteccion auditiva laboral.',
    (SELECT id FROM tm_usuarios WHERE dni='30111223')
FROM tm_usuarios WHERE dni='40111002'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Escoliosis leve diagnosticada en adolescencia. Sedentarismo.',
    'Dolor lumbar mecanico cronico',
    'Se indican plantillas correctivas y plan de ejercicios de fortalecimiento lumbar.',
    (SELECT id FROM tm_usuarios WHERE dni='30111225')
FROM tm_usuarios WHERE dni='40111003'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Sobrepeso desde la juventud. Madre con diabetes tipo 2. Intolerancia a la lactosa.',
    'Obesidad grado I (IMC 32)',
    'Buena adherencia al plan alimentario inicial. Se ajusta por intolerancia a lacteos.',
    (SELECT id FROM tm_usuarios WHERE dni='30111224')
FROM tm_usuarios WHERE dni='40111004'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Accidente de transito 2019. Lesion medular incompleta. Vejiga neurogenica.',
    'Lesion medular incompleta - usuario de silla de ruedas',
    'Equipamiento entregado y ajustado. Capacitacion al paciente y familiar sobre uso y mantenimiento.',
    (SELECT id FROM tm_usuarios WHERE dni='30111226')
FROM tm_usuarios WHERE dni='40111005'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Diabetes mellitus tipo 2 diagnosticada en 2015. HTA en tratamiento.',
    'Pie diabetico en evolucion favorable',
    'Continuar con sesiones de camara hiperbarica. Control glucemico estable.',
    (SELECT id FROM tm_usuarios WHERE dni='30111222')
FROM tm_usuarios WHERE dni='40111006'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Cirugia de rodilla derecha por rotura de menisco en mayo 2026. Kinesiologia pre-quirurgica.',
    'Post-operatorio de meniscectomia',
    'Buena evolucion. Continuar con ejercicios de movilidad y fortalecimiento.',
    (SELECT id FROM tm_usuarios WHERE dni='30111225')
FROM tm_usuarios WHERE dni='40111007'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Hipotiroidismo en tratamiento con levotiroxina. Anemia ferropenica.',
    'Sobrepeso + anemia ferropenica',
    'Ajuste de plan por intolerancia a lacteos. Suplementacion con hierro en evaluacion.',
    (SELECT id FROM tm_usuarios WHERE dni='30111224')
FROM tm_usuarios WHERE dni='40111008'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'HTA de larga data. Cardiopatia isquemica. Stent colocado en 2019.',
    'Cardiopatia isquemica - seguimiento',
    'Paciente estable. Continuar con tratamiento farmacologico y controles periodicos.',
    (SELECT id FROM tm_usuarios WHERE dni='30111222')
FROM tm_usuarios WHERE dni='40111009'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);

INSERT INTO tm_historia_clinica (paciente_id, antecedentes, diagnostico, observaciones, actualizado_por)
SELECT id,
    'Sin antecedentes relevantes. Deportista amateur (running).',
    'Consulta preventiva - apto fisico',
    'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.',
    (SELECT id FROM tm_usuarios WHERE dni='30111224')
FROM tm_usuarios WHERE dni='40111010'
ON DUPLICATE KEY UPDATE
    antecedentes=VALUES(antecedentes),
    diagnostico=VALUES(diagnostico),
    observaciones=VALUES(observaciones),
    actualizado_por=VALUES(actualizado_por);


-- ============================================================
-- MEDICAMENTOS RECETADOS (idempotente - borra y reinserta)
-- ============================================================

-- Primero limpiamos medicamentos existentes para este seed
DELETE FROM tm_medicamentos_recetados WHERE paciente_id IN (
    SELECT id FROM tm_usuarios WHERE dni IN ('40111001','40111004','40111006','40111007','40111008','40111009')
);

-- Paciente 40111001 - Juan Perez (Sindrome metabolico)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111001'), 'Losartan', 'Losartan potasico 50mg', '50 mg', '1 vez por dia', 'Oral', 'activo', '2026-06-15'),
((SELECT id FROM tm_usuarios WHERE dni='40111001'), 'Metformina', 'Clorhidrato de metformina 850mg', '850 mg', '2 veces por dia', 'Oral', 'activo', '2026-06-15'),
((SELECT id FROM tm_usuarios WHERE dni='40111001'), 'Enalapril', 'Enalapril maleato 10mg', '10 mg', '1 vez por dia', 'Oral', 'suspendido', '2026-05-20'),
((SELECT id FROM tm_usuarios WHERE dni='40111001'), 'Ibuprofeno 600', 'Ibuprofeno 600mg', '400 mg', 'Cada 8 horas (PRN)', 'Oral', 'finalizado', '2026-04-10');

-- Paciente 40111004 - Ana Lopez (Obesidad)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111004'), 'Orlistat', 'Orlistat 120mg', '120 mg', '3 veces por dia con las comidas', 'Oral', 'activo', '2026-07-01'),
((SELECT id FROM tm_usuarios WHERE dni='40111004'), 'Complejo vitaminico B', 'Vitaminas B1, B6, B12', '1 comprimido', '1 vez por dia', 'Oral', 'activo', '2026-07-15');

-- Paciente 40111006 - Lucia Sanchez (Pie diabetico)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111006'), 'Insulina Lantus', 'Insulina glargina', '20 UI', '1 vez por dia (antes de dormir)', 'Subcutanea', 'activo', '2026-04-05'),
((SELECT id FROM tm_usuarios WHERE dni='40111006'), 'Metformina', 'Clorhidrato de metformina 500mg', '500 mg', '2 veces por dia', 'Oral', 'activo', '2026-04-05'),
((SELECT id FROM tm_usuarios WHERE dni='40111006'), 'Ciprofloxacina', 'Ciprofloxacina 500mg', '500 mg', '2 veces por dia (7 dias)', 'Oral', 'finalizado', '2026-06-20');

-- Paciente 40111007 - Pablo Romero (Post meniscectomia)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111007'), 'Ibuprofeno 600', 'Ibuprofeno 600mg', '600 mg', 'Cada 8 horas (con alimentos)', 'Oral', 'activo', '2026-05-15'),
((SELECT id FROM tm_usuarios WHERE dni='40111007'), 'Diclofenac gel', 'Diclofenac dietilamina 1%', 'Aplicar 3 cm', '3 veces por dia', 'Topica', 'finalizado', '2026-05-15');

-- Paciente 40111008 - Sofia Torres (Hipotiroidismo + anemia)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111008'), 'Levotiroxina', 'Levotiroxina sodica 50mcg', '50 mcg', '1 vez por dia (ayunas)', 'Oral', 'activo', '2025-09-10'),
((SELECT id FROM tm_usuarios WHERE dni='40111008'), 'Sulfato ferroso', 'Sulfato ferroso 200mg', '200 mg', '1 vez por dia (con vitamina C)', 'Oral', 'activo', '2026-07-14');

-- Paciente 40111009 - Miguel Flores (Cardiopatia isquemica)
INSERT INTO tm_medicamentos_recetados (paciente_id, nombre_comercial, principio_activo, dosis, frecuencia, via_administracion, estado, fecha_receta)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111009'), 'Aspirina Prevent', 'Acido acetilsalicilico 100mg', '100 mg', '1 vez por dia', 'Oral', 'activo', '2026-02-20'),
((SELECT id FROM tm_usuarios WHERE dni='40111009'), 'Atorvastatina', 'Atorvastatina 20mg', '20 mg', '1 vez por dia (antes de dormir)', 'Oral', 'activo', '2026-02-20'),
((SELECT id FROM tm_usuarios WHERE dni='40111009'), 'Carvedilol', 'Carvedilol 6.25mg', '6.25 mg', '2 veces por dia', 'Oral', 'activo', '2026-02-20');


-- ============================================================
-- ESTUDIOS (metadatos, sin archivo real)
-- Idempotente: borra y reinserta para poder re-ejecutar
-- ============================================================

DELETE FROM tm_estudios WHERE paciente_id IN (
    SELECT id FROM tm_usuarios WHERE dni IN ('40111001','40111002','40111003','40111006','40111007','40111008','40111009')
);

INSERT INTO tm_estudios (paciente_id, subido_por, nombre_original, ruta_archivo, tipo, notas, fecha_estudio, subido_en)
VALUES
((SELECT id FROM tm_usuarios WHERE dni='40111001'), (SELECT id FROM tm_usuarios WHERE dni='30111222'),
 'Laboratorio_clinico_jul2026.pdf', 'uploads/estudios/lab_40111001_202607.pdf', 'laboratorio',
 'Hemograma completo y glucemia dentro de parametros normales.', '2026-07-15', NOW() - INTERVAL 30 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111002'), (SELECT id FROM tm_usuarios WHERE dni='30111223'),
 'Audiometria_tonal_2026.pdf', 'uploads/estudios/audio_40111002_202607.pdf', 'audiometria',
 'Hipoacusia leve bilateral. Indica control en 6 meses.', '2026-07-05', NOW() - INTERVAL 50 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111003'), (SELECT id FROM tm_usuarios WHERE dni='30111225'),
 'Rx_columna_lumbar_AP_lateral.pdf', 'uploads/estudios/rx_40111003_202606.pdf', 'radiografia',
 'Rectificacion de lordosis lumbar. Sin pinzamientos significativos.', '2026-06-28', NOW() - INTERVAL 60 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111006'), (SELECT id FROM tm_usuarios WHERE dni='30111222'),
 'Doppler_venoso_MMII.pdf', 'uploads/estudios/doppler_40111006_202606.pdf', 'ecografia',
 'Buena perfusion distal. Sin signos de trombosis.', '2026-06-15', NOW() - INTERVAL 65 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111007'), (SELECT id FROM tm_usuarios WHERE dni='30111225'),
 'RMN_rodilla_derecha_postqx.pdf', 'uploads/estudios/rmn_40111007_202606.pdf', 'resonancia',
 'Post-operatorio de meniscectomia. Buena evolucion.', '2026-06-30', NOW() - INTERVAL 55 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111009'), (SELECT id FROM tm_usuarios WHERE dni='30111222'),
 'Electrocardiograma_2026.pdf', 'uploads/estudios/ecg_40111009_202602.pdf', 'cardiologia',
 'Ritmo sinusal regular. Sin alteraciones agudas.', '2026-02-20', NOW() - INTERVAL 180 DAY),

((SELECT id FROM tm_usuarios WHERE dni='40111008'), (SELECT id FROM tm_usuarios WHERE dni='30111224'),
 'Analisis_clinico_completo.pdf', 'uploads/estudios/lab_40111008_202607.pdf', 'laboratorio',
 'Anemia ferropenica leve. Funcion tiroidea normalizada.', '2026-07-14', NOW() - INTERVAL 18 DAY);
