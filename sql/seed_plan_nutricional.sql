-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Datos de prueba: plan nutricional (Fase E)
-- Inserta un set de ejemplo para 1 paciente en semanal y mensual.
-- Ej: útil para probar UI "Plan nutricional".
--
-- IMPORTANTE: ejecutar DESPUÉS de schema_fase_e_nutricion.sql,
-- sql/migration_plan_nutricional.sql y seeds base (pacientes/profesionales).
-- ══════════════════════════════════════════════════════════════

-- Ajustá si querés usar otro paciente de prueba.
SET @paciente_dni = '40111001'; -- Juan Pérez

-- Buscamos ids (evitamos error #1267 forzando collation en el literal)
-- Buscamos ids (evitamos errores de collation comparando como NUMÉRICO)
SET @paciente_id = (
  SELECT id
  FROM tm_usuarios
  WHERE CAST(dni AS UNSIGNED) = CAST(@paciente_dni AS UNSIGNED)
  LIMIT 1
);
SET @prof_id = (
  SELECT id
  FROM tm_usuarios
  WHERE CAST(dni AS UNSIGNED) = CAST('30111224' AS UNSIGNED)
  LIMIT 1
); -- nutrición prof (área nutrición)

-- =====================
-- Plan semanal ejemplo
-- =====================
SET @plan_semana_json = JSON_OBJECT(
  'dias', JSON_OBJECT(
    'lunes', JSON_OBJECT(
      'desayuno', 'Yogur natural + avena + fruta',
      'almuerzo', 'Pollo a la plancha + arroz + ensalada',
      'merienda', 'Frutos secos + té',
      'cena', 'Tortilla de verduras + pan integral',
      'colacion', '1 banana'
    ),
    'martes', JSON_OBJECT(
      'desayuno', 'Tostadas integrales con queso untable',
      'almuerzo', 'Carne magra + puré de calabaza + vegetales',
      'merienda', 'Batido de yogur + frutos rojos',
      'cena', 'Pescado + ensalada de hojas verdes',
      'colacion', '1 manzana'
    ),
    'miercoles', JSON_OBJECT(
      'desayuno', 'Omelette de 2 huevos + espinaca',
      'almuerzo', 'Lentejas + ensalada',
      'merienda', 'Yogur + granola',
      'cena', 'Salteado de verduras + arroz',
      'colacion', '1 pera'
    ),
    'jueves', JSON_OBJECT(
      'desayuno', 'Avena cocida con leche + canela',
      'almuerzo', 'Tacos de pollo + verduras',
      'merienda', 'Fruta + infusión',
      'cena', 'Sopa de verduras + tortilla',
      'colacion', '10 almendras'
    ),
    'viernes', JSON_OBJECT(
      'desayuno', 'Pan integral + palta + huevo',
      'almuerzo', 'Bife magro + papa al horno + ensalada',
      'merienda', 'Yogur + chía',
      'cena', 'Wok de verduras + pollo',
      'colacion', '1 naranja'
    ),
    'sabado', JSON_OBJECT(
      'desayuno', 'Licua de frutas + yogur',
      'almuerzo', 'Pasta integral + salsa casera + ensalada',
      'merienda', 'Queso untable + crackers integrales',
      'cena', 'Merluza + vegetales al horno',
      'colacion', '1 manzana'
    ),
    'domingo', JSON_OBJECT(
      'desayuno', 'Tostadas + ricota + miel (moderada)',
      'almuerzo', 'Asado magro + ensalada + vegetales',
      'merienda', 'Fruta + nueces',
      'cena', 'Crema de calabaza + tortilla',
      'colacion', '1 banana'
    )
  )
);

INSERT INTO tm_plan_nutricional_semanal (paciente_id, plan_json, actualizado_por)
VALUES (@paciente_id, @plan_semana_json, @prof_id)
ON DUPLICATE KEY UPDATE
  plan_json = VALUES(plan_json),
  actualizado_por = VALUES(actualizado_por);

-- =====================
-- Plan mensual ejemplo
-- =====================
-- Mes: octubre 2026
SET @mesYM = '2026-10';
SET @mes_json = JSON_OBJECT(
  'mes', @mesYM,
  'semanas', JSON_ARRAY(
    JSON_OBJECT(
      'semana_index', 1,
      'dias', JSON_OBJECT(
        'lunes', JSON_OBJECT('desayuno','Avena + yogur','almuerzo','Pollo + arroz + ensalada','merienda','Frutos secos','cena','Tortilla de verduras','colacion','1 fruta'),
        'martes', JSON_OBJECT('desayuno','Tostadas integrales + queso','almuerzo','Lentejas + ensalada','merienda','Yogur + granola','cena','Pescado + ensalada','colacion','1 fruta'),
        'miercoles', JSON_OBJECT('desayuno','Omelette + espinaca','almuerzo','Carne magra + puré de calabaza','merienda','Batido de yogur','cena','Salteado de verduras','colacion','1 fruta'),
        'jueves', JSON_OBJECT('desayuno','Avena cocida + canela','almuerzo','Tacos de pollo + verduras','merienda','Fruta + infusión','cena','Sopa de verduras + tortilla','colacion','10 almendras'),
        'viernes', JSON_OBJECT('desayuno','Pan integral + palta + huevo','almuerzo','Bife magro + papa al horno','merienda','Yogur + chía','cena','Wok de verduras + pollo','colacion','1 fruta'),
        'sabado', JSON_OBJECT('desayuno','Licua de frutas + yogur','almuerzo','Pasta integral + ensalada','merienda','Queso untable + crackers','cena','Merluza + vegetales al horno','colacion','1 fruta'),
        'domingo', JSON_OBJECT('desayuno','Tostadas + ricota','almuerzo','Asado magro + ensalada','merienda','Fruta + nueces','cena','Crema de calabaza + tortilla','colacion','1 fruta')
      )
    ),
    JSON_OBJECT(
      'semana_index', 2,
      'dias', JSON_OBJECT(
        'lunes', JSON_OBJECT('desayuno','Yogur + avena + fruta','almuerzo','Pollo + arroz + ensalada','merienda','Frutos secos','cena','Tortilla de verduras','colacion','1 fruta'),
        'martes', JSON_OBJECT('desayuno','Tostadas integrales + queso','almuerzo','Lentejas + ensalada','merienda','Yogur + granola','cena','Pescado + ensalada','colacion','1 fruta'),
        'miercoles', JSON_OBJECT('desayuno','Omelette + espinaca','almuerzo','Carne magra + puré de calabaza','merienda','Batido de yogur','cena','Salteado de verduras','colacion','1 fruta'),
        'jueves', JSON_OBJECT('desayuno','Avena cocida + canela','almuerzo','Tacos de pollo + verduras','merienda','Fruta + infusión','cena','Sopa de verduras + tortilla','colacion','10 almendras'),
        'viernes', JSON_OBJECT('desayuno','Pan integral + palta + huevo','almuerzo','Bife magro + papa al horno','merienda','Yogur + chía','cena','Wok de verduras + pollo','colacion','1 fruta'),
        'sabado', JSON_OBJECT('desayuno','Licua de frutas + yogur','almuerzo','Pasta integral + ensalada','merienda','Queso untable + crackers','cena','Merluza + vegetales al horno','colacion','1 fruta'),
        'domingo', JSON_OBJECT('desayuno','Tostadas + ricota','almuerzo','Asado magro + ensalada','merienda','Fruta + nueces','cena','Crema de calabaza + tortilla','colacion','1 fruta')
      )
    ),
    JSON_OBJECT(
      'semana_index', 3,
      'dias', JSON_OBJECT(
        'lunes', JSON_OBJECT('desayuno','Yogur + avena + fruta','almuerzo','Pollo + arroz + ensalada','merienda','Frutos secos','cena','Tortilla de verduras','colacion','1 fruta'),
        'martes', JSON_OBJECT('desayuno','Tostadas integrales + queso','almuerzo','Lentejas + ensalada','merienda','Yogur + granola','cena','Pescado + ensalada','colacion','1 fruta'),
        'miercoles', JSON_OBJECT('desayuno','Omelette + espinaca','almuerzo','Carne magra + puré de calabaza','merienda','Batido de yogur','cena','Salteado de verduras','colacion','1 fruta'),
        'jueves', JSON_OBJECT('desayuno','Avena cocida + canela','almuerzo','Tacos de pollo + verduras','merienda','Fruta + infusión','cena','Sopa de verduras + tortilla','colacion','10 almendras'),
        'viernes', JSON_OBJECT('desayuno','Pan integral + palta + huevo','almuerzo','Bife magro + papa al horno','merienda','Yogur + chía','cena','Wok de verduras + pollo','colacion','1 fruta'),
        'sabado', JSON_OBJECT('desayuno','Licua de frutas + yogur','almuerzo','Pasta integral + ensalada','merienda','Queso untable + crackers','cena','Merluza + vegetales al horno','colacion','1 fruta'),
        'domingo', JSON_OBJECT('desayuno','Tostadas + ricota','almuerzo','Asado magro + ensalada','merienda','Fruta + nueces','cena','Crema de calabaza + tortilla','colacion','1 fruta')
      )
    ),
    JSON_OBJECT(
      'semana_index', 4,
      'dias', JSON_OBJECT(
        'lunes', JSON_OBJECT('desayuno','Yogur + avena + fruta','almuerzo','Pollo + arroz + ensalada','merienda','Frutos secos','cena','Tortilla de verduras','colacion','1 fruta'),
        'martes', JSON_OBJECT('desayuno','Tostadas integrales + queso','almuerzo','Lentejas + ensalada','merienda','Yogur + granola','cena','Pescado + ensalada','colacion','1 fruta'),
        'miercoles', JSON_OBJECT('desayuno','Omelette + espinaca','almuerzo','Carne magra + puré de calabaza','merienda','Batido de yogur','cena','Salteado de verduras','colacion','1 fruta'),
        'jueves', JSON_OBJECT('desayuno','Avena cocida + canela','almuerzo','Tacos de pollo + verduras','merienda','Fruta + infusión','cena','Sopa de verduras + tortilla','colacion','10 almendras'),
        'viernes', JSON_OBJECT('desayuno','Pan integral + palta + huevo','almuerzo','Bife magro + papa al horno','merienda','Yogur + chía','cena','Wok de verduras + pollo','colacion','1 fruta'),
        'sabado', JSON_OBJECT('desayuno','Licua de frutas + yogur','almuerzo','Pasta integral + ensalada','merienda','Queso untable + crackers','cena','Merluza + vegetales al horno','colacion','1 fruta'),
        'domingo', JSON_OBJECT('desayuno','Tostadas + ricota','almuerzo','Asado magro + ensalada','merienda','Fruta + nueces','cena','Crema de calabaza + tortilla','colacion','1 fruta')
      )
    )
  )
);

INSERT INTO tm_plan_nutricional_mensual (paciente_id, mes, plan_json, actualizado_por)
VALUES (@paciente_id, CONCAT(@mesYM, '-01'), @mes_json, @prof_id)
ON DUPLICATE KEY UPDATE
  plan_json = VALUES(plan_json),
  actualizado_por = VALUES(actualizado_por);
