-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-09-2026 a las 02:21:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tecnomedic_local`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_asignaciones`
--

CREATE TABLE `tm_asignaciones` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `profesional_id` int(11) NOT NULL,
  `area` varchar(50) DEFAULT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_asignaciones`
--

INSERT INTO `tm_asignaciones` (`id`, `paciente_id`, `profesional_id`, `area`, `fecha_asignacion`, `activa`) VALUES
(1, 2, 4, 'nutricion', '2026-07-25 20:49:07', 1),
(2, 16, 4, 'nutricion', '2026-08-27 11:21:21', 1),
(3, 12, 3, 'audiologia', '2026-08-27 11:21:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_categorias`
--

CREATE TABLE `tm_categorias` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `icono` varchar(50) DEFAULT 'fa-box',
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_categorias`
--

INSERT INTO `tm_categorias` (`id`, `slug`, `nombre`, `icono`, `orden`) VALUES
(1, 'audiologia', 'Audiología', 'fa-headphones', 1),
(2, 'nutricion', 'Nutrición', 'fa-apple-whole', 2),
(3, 'ortopedia-rehabilitacion', 'Ortopedia y Rehabilitación', 'fa-wheelchair', 3),
(4, 'equipamiento-medico', 'Equipamiento Médico y Quirúrgico', 'fa-kit-medical', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_comidas`
--

CREATE TABLE `tm_comidas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_comida` enum('desayuno','almuerzo','merienda','cena','colacion') NOT NULL,
  `descripcion` text NOT NULL,
  `registrado_por` int(11) NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_comidas`
--

INSERT INTO `tm_comidas` (`id`, `paciente_id`, `fecha`, `tipo_comida`, `descripcion`, `registrado_por`, `creado_en`) VALUES
(1, 2, '2026-08-22', 'merienda', 'pan', 6, '2026-08-22 18:46:24'),
(3, 10, '2026-07-20', 'desayuno', 'Tostadas integrales con palta y huevo, café con leche descremada', 10, '2026-08-22 21:13:03'),
(4, 10, '2026-07-20', 'almuerzo', 'Pechuga de pollo grillada con ensalada de hojas verdes y tomate', 10, '2026-08-22 21:13:03'),
(5, 10, '2026-07-20', 'merienda', 'Yogur descremado con granola casera', 10, '2026-08-22 21:13:03'),
(7, 10, '2026-07-20', 'desayuno', 'Tostadas integrales con palta y huevo, café con leche descremada', 10, '2026-08-22 21:27:52'),
(8, 10, '2026-07-20', 'almuerzo', 'Pechuga de pollo grillada con ensalada de hojas verdes y tomate', 10, '2026-08-22 21:27:52'),
(9, 10, '2026-07-20', 'merienda', 'Yogur descremado con granola casera', 10, '2026-08-22 21:27:52'),
(11, 14, '2026-07-21', 'desayuno', 'Licuado de banana con avena y leche de almendras', 14, '2026-08-22 21:27:52'),
(12, 14, '2026-07-21', 'colacion', 'Puñado de almendras y una manzana', 14, '2026-08-22 21:27:52'),
(14, 14, '2026-07-21', 'cena', 'Tortilla de vegetales con ensalada', 14, '2026-08-22 21:27:52'),
(15, 15, '2026-07-22', 'desayuno', 'Mate cocido con dos tostadas de pan integral y queso untable', 15, '2026-08-22 21:27:52'),
(16, 16, '2026-07-22', 'merienda', 'Infusión con dos galletas de arroz y mermelada light', 16, '2026-08-22 21:27:52'),
(17, 10, '2026-07-20', 'desayuno', 'Tostadas integrales con palta y huevo, café con leche descremada', 10, '2026-08-22 21:30:30'),
(18, 10, '2026-07-20', 'almuerzo', 'Pechuga de pollo grillada con ensalada de hojas verdes y tomate', 10, '2026-08-22 21:30:30'),
(19, 10, '2026-07-20', 'merienda', 'Yogur descremado con granola casera', 10, '2026-08-22 21:30:30'),
(21, 14, '2026-07-21', 'desayuno', 'Licuado de banana con avena y leche de almendras', 14, '2026-08-22 21:30:30'),
(22, 14, '2026-07-21', 'colacion', 'Puñado de almendras y una manzana', 14, '2026-08-22 21:30:30'),
(24, 14, '2026-07-21', 'cena', 'Tortilla de vegetales con ensalada', 14, '2026-08-22 21:30:30'),
(25, 15, '2026-07-22', 'desayuno', 'Mate cocido con dos tostadas de pan integral y queso untable', 15, '2026-08-22 21:30:30'),
(26, 16, '2026-07-22', 'merienda', 'Infusión con dos galletas de arroz y mermelada light', 16, '2026-08-22 21:30:30'),
(27, 10, '2026-07-20', 'desayuno', 'Tostadas integrales con palta y huevo, café con leche descremada', 10, '2026-08-22 21:40:40'),
(28, 10, '2026-07-20', 'almuerzo', 'Pechuga de pollo grillada con ensalada de hojas verdes y tomate', 10, '2026-08-22 21:40:40'),
(29, 10, '2026-07-20', 'merienda', 'Yogur descremado con granola casera', 10, '2026-08-22 21:40:40'),
(31, 14, '2026-07-21', 'desayuno', 'Licuado de banana con avena y leche de almendras', 14, '2026-08-22 21:40:40'),
(32, 14, '2026-07-21', 'colacion', 'Puñado de almendras y una manzana', 14, '2026-08-22 21:40:40'),
(34, 14, '2026-07-21', 'cena', 'Tortilla de vegetales con ensalada', 14, '2026-08-22 21:40:40'),
(35, 15, '2026-07-22', 'desayuno', 'Mate cocido con dos tostadas de pan integral y queso untable', 15, '2026-08-22 21:40:40'),
(36, 16, '2026-07-22', 'merienda', 'Infusión con dos galletas de arroz y mermelada light', 16, '2026-08-22 21:40:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_config`
--

CREATE TABLE `tm_config` (
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `actualizado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_config`
--

INSERT INTO `tm_config` (`clave`, `valor`, `actualizado_en`) VALUES
('hero_video_url', 'https://www.youtube.com/embed/2Ebyr0AScOo?si=MRo7jO3WThgQKdvP\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>', '2026-09-13 21:14:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_contactos`
--

CREATE TABLE `tm_contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `motivo` varchar(50) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `atendido` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_contactos`
--

INSERT INTO `tm_contactos` (`id`, `nombre`, `email`, `telefono`, `motivo`, `mensaje`, `atendido`, `creado_en`) VALUES
(1, 'Esteban', 'andrestester0001@gmail.com', '3794775341', 'turnos', 'hola turno', 0, '2026-08-11 20:31:57'),
(2, 'Esteban', 'andrestester0001@gmail.com', '3794775341', 'turnos', 'hola turno', 0, '2026-08-11 20:32:50'),
(3, 'Esteban', 'andrestester0001@gmail.com', '3794775341', 'turnos', 'hola turno', 1, '2026-08-11 20:33:30'),
(4, 'Marcela Funes', 'marcela.funes@test.com', '3794112201', 'consulta_general', 'Hola, quería saber el horario de atención de audiología los sábados.', 0, '2026-08-21 21:00:19'),
(5, 'Ricardo Aguirre', 'ricardo.aguirre@test.com', '3794112202', 'turnos', 'Necesito reprogramar mi turno de la semana que viene, no puedo asistir.', 1, '2026-08-17 21:00:19'),
(6, 'Yolanda Cabrera', 'yolanda.cabrera@test.com', '3794112203', 'presupuesto', 'Quisiera saber si atienden con OSDE para cámara hiperbárica.', 0, '2026-08-20 21:00:19'),
(7, 'Hugo Espínola', 'hugo.espinola@test.com', '3794112204', 'tienda', 'Consulto stock de bastones ortopédicos regulables.', 1, '2026-08-12 21:00:19'),
(8, 'Norma Villalba', 'norma.villalba@test.com', '3794112205', 'consulta_general', '¿Atienden los feriados?', 1, '2026-08-22 18:00:19'),
(9, 'Ariel Duarte', 'ariel.duarte@test.com', '3794112206', 'otro', 'Quiero dejar una sugerencia sobre el estacionamiento del centro.', 1, '2026-08-07 21:00:19'),
(10, 'Silvia Rolón', 'silvia.rolon@test.com', '3794112207', 'turnos', 'No me llegó el mail de confirmación de mi turno de nutrición.', 0, '2026-08-22 15:00:19'),
(11, 'Pablo Servín', 'pablo.servin@test.com', '3794112208', 'presupuesto', 'Necesito presupuesto para sesiones de fonoaudiología, mi hijo de 6 años.', 0, '2026-08-21 21:00:19'),
(12, 'Claudia Ojeda', 'claudia.ojeda@test.com', '3794112209', 'consulta_general', 'Buenas tardes, ¿hacen estudios de audiometría sin turno previo?', 1, '2026-08-02 21:00:19'),
(13, 'Fabián Barrios', 'fabian.barrios@test.com', '3794112210', 'tienda', 'Consulto por sillas de ruedas plegables, precio y financiación.', 0, '2026-08-18 21:00:19'),
(14, 'Marcela Funes', 'marcela.funes@test.com', '3794112201', 'consulta_general', 'Hola, quería saber el horario de atención de audiología los sábados.', 0, '2026-08-21 21:41:27'),
(15, 'Ricardo Aguirre', 'ricardo.aguirre@test.com', '3794112202', 'turnos', 'Necesito reprogramar mi turno de la semana que viene, no puedo asistir.', 1, '2026-08-17 21:41:27'),
(16, 'Yolanda Cabrera', 'yolanda.cabrera@test.com', '3794112203', 'presupuesto', 'Quisiera saber si atienden con OSDE para cámara hiperbárica.', 0, '2026-08-20 21:41:27'),
(17, 'Hugo Espínola', 'hugo.espinola@test.com', '3794112204', 'tienda', 'Consulto stock de bastones ortopédicos regulables.', 1, '2026-08-12 21:41:27'),
(18, 'Norma Villalba', 'norma.villalba@test.com', '3794112205', 'consulta_general', '¿Atienden los feriados?', 1, '2026-08-22 18:41:27'),
(19, 'Ariel Duarte', 'ariel.duarte@test.com', '3794112206', 'otro', 'Quiero dejar una sugerencia sobre el estacionamiento del centro.', 1, '2026-08-07 21:41:27'),
(20, 'Silvia Rolón', 'silvia.rolon@test.com', '3794112207', 'turnos', 'No me llegó el mail de confirmación de mi turno de nutrición.', 0, '2026-08-22 15:41:27'),
(21, 'Pablo Servín', 'pablo.servin@test.com', '3794112208', 'presupuesto', 'Necesito presupuesto para sesiones de fonoaudiología, mi hijo de 6 años.', 0, '2026-08-21 21:41:27'),
(22, 'Claudia Ojeda', 'claudia.ojeda@test.com', '3794112209', 'consulta_general', 'Buenas tardes, ¿hacen estudios de audiometría sin turno previo?', 1, '2026-08-02 21:41:27'),
(23, 'Fabián Barrios', 'fabian.barrios@test.com', '3794112210', 'tienda', 'Consulto por sillas de ruedas plegables, precio y financiación.', 0, '2026-08-18 21:41:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_estudios`
--

CREATE TABLE `tm_estudios` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `subido_por` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `notas` varchar(255) DEFAULT NULL,
  `fecha_estudio` date DEFAULT NULL,
  `subido_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_estudios`
--

INSERT INTO `tm_estudios` (`id`, `paciente_id`, `subido_por`, `nombre_original`, `ruta_archivo`, `tipo`, `notas`, `fecha_estudio`, `subido_en`) VALUES
(1, 2, 6, 'Silla de ruedas manual cuadro rígido.pdf', 'storage/estudios/2/estudio_6a84e243ccacf.pdf', 'PDF', '', '2026-08-17', '2026-08-18 19:52:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_familias`
--

CREATE TABLE `tm_familias` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_familias`
--

INSERT INTO `tm_familias` (`id`, `categoria_id`, `slug`, `nombre`, `orden`) VALUES
(1, 1, 'audifonos', 'Audífonos', 1),
(2, 1, 'accesorios', 'Accesorios y conectividad', 2),
(3, 1, 'pilas', 'Pilas y cargadores', 3),
(4, 1, 'protectores', 'Protectores auditivos', 4),
(5, 2, 'proteinas', 'Proteínas', 1),
(6, 2, 'creatina', 'Creatina', 2),
(7, 2, 'omega3', 'Omega 3', 3),
(8, 2, 'vitaminas', 'Vitaminas y minerales', 4),
(9, 2, 'aminoacidos', 'Aminoácidos', 5),
(10, 3, 'soportes', 'Soportes articulares (fajas, rodilleras, tobilleras, muñequeras)', 1),
(11, 3, 'movilidad', 'Ayudas para la movilidad', 2),
(12, 3, 'rehabilitacion', 'Productos para rehabilitación', 3),
(13, 3, 'compresion', 'Medias de compresión', 4),
(14, 4, 'diagnostico', 'Tensiómetros y oxímetros', 1),
(15, 4, 'respiratorio', 'Equipamiento respiratorio (nebulizadores, aspiradores)', 2),
(16, 4, 'oxigenoterapia', 'Oxigenoterapia domiciliaria (concentradores, tubos, mochilas)', 3),
(17, 4, 'quirurgico', 'Instrumental e insumos quirúrgicos', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_historia_clinica`
--

CREATE TABLE `tm_historia_clinica` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `antecedentes` text DEFAULT NULL,
  `diagnostico` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `actualizado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_historia_clinica`
--

INSERT INTO `tm_historia_clinica` (`id`, `paciente_id`, `fecha`, `antecedentes`, `diagnostico`, `observaciones`, `actualizado_en`, `actualizado_por`) VALUES
(1, 2, '2026-09-13', 'nada', 'nada', 'nadanada', '2026-08-18 19:24:52', 6),
(3, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-09 21:20:42', 6),
(4, 7, '2026-09-13', 'Hipertension arterial controlada desde 2010. Ex tabaquista (dejo en 2018). Sin alergias conocidas.', 'Sindrome metabolico en tratamiento', 'Paciente en control por pie diabetico. Se evalua continuar con camara hiperbarica.', '2026-09-03 20:17:03', NULL),
(5, 8, '2026-09-13', 'Sin antecedentes patologicos relevantes. Embarazo y parto sin complicaciones.', 'Hipoacusia leve bilateral', 'Trabaja en ambiente ruidoso. Se recomienda uso de proteccion auditiva laboral.', '2026-09-03 20:17:03', NULL),
(6, 9, '2026-09-13', 'Escoliosis leve diagnosticada en adolescencia. Sedentarismo. obeso', 'Dolor lumbar mecanico cronico', 'Se indican plantillas correctivas y plan de ejercicios de fortalecimiento lumbar. y biceps', '2026-09-12 21:27:06', 6),
(7, 10, '2026-09-13', 'Sobrepeso desde la juventud. Madre con diabetes tipo 2. Intolerancia a la lactosa.', 'Obesidad grado I (IMC 32)', 'Buena adherencia al plan alimentario inicial. Se ajusta por intolerancia a lacteos.', '2026-09-03 20:17:03', NULL),
(8, 11, '2026-09-13', 'Accidente de transito 2019. Lesion medular incompleta. Vejiga neurogenica.', 'Lesion medular incompleta - usuario de silla de ruedas', 'Equipamiento entregado y ajustado. Capacitacion al paciente y familiar sobre uso y mantenimiento.', '2026-09-03 20:17:03', 28),
(9, 12, '2026-09-13', 'Diabetes mellitus tipo 2 diagnosticada en 2015. HTA en tratamiento.', 'Pie diabetico en evolucion favorable', 'Continuar con sesiones de camara hiperbarica. Control glucemico estable.', '2026-09-04 19:17:38', NULL),
(10, 13, '2026-09-13', 'Cirugia de rodilla derecha por rotura de menisco en mayo 2026. Kinesiologia pre-quirurgica.', 'Post-operatorio de meniscectomia', 'Buena evolucion. Continuar con ejercicios de movilidad y fortalecimiento.', '2026-09-03 20:17:03', NULL),
(11, 14, '2026-09-13', 'Hipotiroidismo en tratamiento con levotiroxina. Anemia ferropenica.', 'Sobrepeso + anemia ferropenica', 'Ajuste de plan por intolerancia a lacteos. Suplementacion con hierro en evaluacion.', '2026-09-03 20:17:03', NULL),
(12, 15, '2026-09-13', 'HTA de larga data. Cardiopatia isquemica. Stent colocado en 2019.', 'Cardiopatia isquemica - seguimiento', 'Paciente estable. Continuar con tratamiento farmacologico y controles periodicos.', '2026-09-03 20:17:03', NULL),
(53, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lll', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 20:46:32', 6),
(54, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lll', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 20:46:32', 6),
(55, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lll', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 20:47:10', 6),
(56, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lll', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 20:47:10', 6),
(57, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lllyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 21:15:41', 6),
(58, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lllyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 21:15:41', 6),
(59, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lllyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 21:16:16', 6),
(60, 16, '2026-09-13', 'Sin antecedentes relevantes. Deportista amateur (running).lllyyyyyyyyyyyyyyyyyyyyyyyyyyyy', 'Consulta preventiva - apto fisico', 'Sin hallazgos patologicos. Se sugieren controles anuales de rutina.', '2026-09-13 21:16:16', 6),
(61, 9, '2026-09-13', 'Escoliosis leve diagnosticada en adolescencia. Sedentarismo. obeso', 'Dolor lumbar mecanico cronico', 'Se indican plantillas correctivas y plan de ejercicios de fortalecimiento lumbar. y biceps', '2026-09-13 21:20:35', 6),
(62, 9, '2026-09-13', 'Escoliosis leve diagnosticada en adolescencia. Sedentarismo. obeso', 'Dolor lumbar mecanico cronico', 'Se indican plantillas correctivas y plan de ejercicios de fortalecimiento lumbar. y biceps', '2026-09-13 21:20:35', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_medicamentos_recetados`
--

CREATE TABLE `tm_medicamentos_recetados` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `nombre_comercial` varchar(255) NOT NULL COMMENT 'Nombre de la marca/fórmula',
  `principio_activo` varchar(255) DEFAULT NULL COMMENT 'Droga / principio activo',
  `dosis` varchar(100) DEFAULT NULL,
  `frecuencia` varchar(100) DEFAULT NULL COMMENT 'Cómo y cuándo tomarlo',
  `via_administracion` varchar(100) DEFAULT NULL COMMENT 'Oral, subcutánea, IV, etc.',
  `estado` enum('activo','suspendido','finalizado') NOT NULL DEFAULT 'activo',
  `fecha_receta` date DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_medicamentos_recetados`
--

INSERT INTO `tm_medicamentos_recetados` (`id`, `paciente_id`, `nombre_comercial`, `principio_activo`, `dosis`, `frecuencia`, `via_administracion`, `estado`, `fecha_receta`, `creado_en`, `actualizado_en`) VALUES
(17, 7, 'Losartan', 'Losartan potasico 50mg', '50 mg', '1 vez por dia', 'Oral', 'activo', '2026-06-15', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(18, 7, 'Metformina', 'Clorhidrato de metformina 850mg', '850 mg', '2 veces por dia', 'Oral', 'activo', '2026-06-15', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(19, 7, 'Enalapril', 'Enalapril maleato 10mg', '10 mg', '1 vez por dia', 'Oral', 'suspendido', '2026-05-20', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(20, 7, 'Ibuprofeno 600', 'Ibuprofeno 600mg', '400 mg', 'Cada 8 horas (PRN)', 'Oral', 'finalizado', '2026-04-10', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(21, 10, 'Orlistat', 'Orlistat 120mg', '120 mg', '3 veces por dia con las comidas', 'Oral', 'activo', '2026-07-01', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(22, 10, 'Complejo vitaminico B', 'Vitaminas B1, B6, B12', '1 comprimido', '1 vez por dia', 'Oral', 'activo', '2026-07-15', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(23, 12, 'Insulina Lantus', 'Insulina glargina', '20 UI', '1 vez por dia (antes de dormir)', 'Subcutanea', 'activo', '2026-04-05', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(24, 12, 'Metformina', 'Clorhidrato de metformina 500mg', '500 mg', '2 veces por dia', 'Oral', 'activo', '2026-04-05', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(25, 12, 'Ciprofloxacina', 'Ciprofloxacina 500mg', '500 mg', '2 veces por dia (7 dias)', 'Oral', 'finalizado', '2026-06-20', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(26, 13, 'Ibuprofeno 600', 'Ibuprofeno 600mg', '600 mg', 'Cada 8 horas (con alimentos)', 'Oral', 'activo', '2026-05-15', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(27, 13, 'Diclofenac gel', 'Diclofenac dietilamina 1%', 'Aplicar 3 cm', '3 veces por dia', 'Topica', 'finalizado', '2026-05-15', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(28, 14, 'Levotiroxina', 'Levotiroxina sodica 50mcg', '50 mcg', '1 vez por dia (ayunas)', 'Oral', 'activo', '2025-09-10', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(29, 14, 'Sulfato ferroso', 'Sulfato ferroso 200mg', '200 mg', '1 vez por dia (con vitamina C)', 'Oral', 'activo', '2026-07-14', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(30, 15, 'Aspirina Prevent', 'Acido acetilsalicilico 100mg', '100 mg', '1 vez por dia', 'Oral', 'activo', '2026-02-20', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(31, 15, 'Atorvastatina', 'Atorvastatina 20mg', '20 mg', '1 vez por dia (antes de dormir)', 'Oral', 'activo', '2026-02-20', '2026-09-04 19:17:38', '2026-09-04 19:17:38'),
(32, 15, 'Carvedilol', 'Carvedilol 6.25mg', '6.25 mg', '2 veces por dia', 'Oral', 'activo', '2026-02-20', '2026-09-04 19:17:38', '2026-09-04 19:17:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_mediciones`
--

CREATE TABLE `tm_mediciones` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `altura` decimal(5,2) DEFAULT NULL,
  `imc` decimal(4,2) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `registrado_por` int(11) NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_mediciones`
--

INSERT INTO `tm_mediciones` (`id`, `paciente_id`, `fecha`, `peso`, `altura`, `imc`, `observaciones`, `registrado_por`, `creado_en`) VALUES
(1, 2, '2026-08-19', 99.00, 155.00, 41.21, 'obeso', 6, '2026-08-18 19:54:06'),
(5, 10, '2026-08-01', 76.10, 165.00, 27.95, NULL, 10, '2026-08-22 21:29:15'),
(9, 8, '2026-07-05', 68.40, 160.00, 26.72, NULL, 8, '2026-08-22 21:29:15'),
(13, 10, '2026-08-01', 76.10, 165.00, 27.95, NULL, 10, '2026-08-22 21:30:53'),
(17, 8, '2026-07-05', 68.40, 160.00, 26.72, NULL, 8, '2026-08-22 21:30:53'),
(21, 10, '2026-08-01', 76.10, 165.00, 27.95, NULL, 10, '2026-08-22 21:40:55'),
(25, 8, '2026-07-05', 68.40, 160.00, 26.72, NULL, 8, '2026-08-22 21:40:55'),
(27, 15, '2026-07-20', 55.80, 162.00, 21.26, 'Autorregistro desde el Portal', 15, '2026-08-22 21:40:55'),
(28, 16, '2026-07-22', 71.50, 169.00, 25.03, NULL, 16, '2026-08-22 21:40:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_novedades`
--

CREATE TABLE `tm_novedades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `tipo` enum('noticia','novedad') DEFAULT 'novedad',
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_objetivo_nutricional`
--

CREATE TABLE `tm_objetivo_nutricional` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `objetivo` text DEFAULT NULL,
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `actualizado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_objetivo_nutricional`
--

INSERT INTO `tm_objetivo_nutricional` (`id`, `paciente_id`, `objetivo`, `actualizado_en`, `actualizado_por`) VALUES
(1, 2, 'baja con ozempic 5 kg', '2026-08-18 19:53:51', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_obras_sociales`
--

CREATE TABLE `tm_obras_sociales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_obras_sociales`
--

INSERT INTO `tm_obras_sociales` (`id`, `nombre`, `activa`) VALUES
(1, 'Particular / Sin cobertura', 1),
(2, 'PAMI', 1),
(3, 'IOSCOR', 1),
(4, 'OSDE', 1),
(5, 'Swiss Medical', 1),
(6, 'Galeno', 1),
(7, 'Medifé', 1),
(8, 'Sancor Salud', 1),
(9, 'Unión Personal', 1),
(10, 'OSDEPYM', 1),
(11, 'Jerárquicos Salud', 1),
(12, 'Apross', 1),
(13, 'Otra', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_perfiles_paciente`
--

CREATE TABLE `tm_perfiles_paciente` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `obra_social` varchar(100) DEFAULT NULL,
  `obra_social_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_perfiles_paciente`
--

INSERT INTO `tm_perfiles_paciente` (`id`, `usuario_id`, `fecha_nacimiento`, `obra_social`, `obra_social_id`, `observaciones`) VALUES
(1, 2, '1970-01-01', 'ioscor', 9, NULL),
(2, 7, '1955-03-12', NULL, 2, NULL),
(3, 8, '1988-07-22', NULL, 4, NULL),
(4, 9, '1992-01-05', NULL, 3, NULL),
(5, 10, '1975-11-30', NULL, 5, NULL),
(6, 11, '2001-05-18', NULL, 1, NULL),
(7, 12, '1968-09-09', NULL, 6, NULL),
(8, 13, '1983-02-14', NULL, 7, NULL),
(9, 14, '1995-12-01', NULL, 8, NULL),
(10, 15, '1950-06-25', NULL, 2, NULL),
(11, 16, '1980-01-01', NULL, 9, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_perfiles_profesional`
--

CREATE TABLE `tm_perfiles_profesional` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `area` enum('audiologia','hiperbarica','nutricion','ortopedia','equipamiento') NOT NULL,
  `matricula` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_perfiles_profesional`
--

INSERT INTO `tm_perfiles_profesional` (`id`, `usuario_id`, `area`, `matricula`) VALUES
(1, 3, 'audiologia', '12456'),
(2, 4, 'nutricion', '4568');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_personas`
--

CREATE TABLE `tm_personas` (
  `dni` varchar(20) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `obra_social_id` int(11) DEFAULT NULL,
  `actualizado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_personas`
--

INSERT INTO `tm_personas` (`dni`, `nombre`, `apellido`, `telefono`, `email`, `obra_social_id`, `actualizado`) VALUES
('11000000', 'Dra  Maria', 'Unger', '37945555555', 'andrestester0001-3@gmail.com', NULL, '2026-09-04 20:49:10'),
('11123456', 'Gustavo', 'Valdez', '3794775341', 'andrestester0001-2@gmail.com', 9, '2026-09-11 17:12:18'),
('12000000', 'Dra Carolina', 'Unger', '37945555554', 'andrestester0001-4@gmail.com', NULL, '2026-09-04 20:50:46'),
('30111222', 'Juan', 'Pérez', '3794775341', 'andrestester0001@gmail.com', NULL, '2026-09-06 11:46:49'),
('40111001', 'Juan', 'Pérez', '3794111001', 'paciente1@test.com', 2, '2026-08-22 21:02:26'),
('40111002', 'María', 'González', '3794111002', 'paciente2@test.com', 4, '2026-08-22 21:02:26'),
('40111003', 'Carlos', 'Fernández', '3794111003', 'paciente3@test.com', 3, '2026-08-22 21:02:26'),
('40111004', 'Ana', 'López', '3794111004', 'paciente4@test.com', 5, '2026-08-22 21:02:26'),
('40111005', 'Roberto', 'Martínez', '3794111005', 'paciente5@test.com', 1, '2026-08-22 21:02:26'),
('40111006', 'Lucía', 'Sánchez', '3794111006', 'paciente6@test.com', 6, '2026-08-22 21:02:26'),
('40111007', 'Pablo', 'Romero', '3794111007', 'paciente7@test.com', 7, '2026-08-22 21:02:26'),
('40111008', 'Sofía', 'Torres', '3794111008', 'paciente8@test.com', 8, '2026-08-22 21:02:26'),
('40111009', 'Miguel', 'Flores', '3794111009', 'paciente9@test.com', 2, '2026-08-22 21:02:26'),
('40111010', 'Valentina', 'Benítez', '3794111010', 'paciente10@test.com', 9, '2026-09-12 18:39:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_presupuestos`
--

CREATE TABLE `tm_presupuestos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni_cuit` varchar(20) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `area` varchar(50) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('Pendiente','Elaborado','Enviado') NOT NULL DEFAULT 'Pendiente',
  `archivo_pdf` varchar(500) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_presupuestos`
--

INSERT INTO `tm_presupuestos` (`id`, `nombre`, `apellido`, `dni_cuit`, `telefono`, `email`, `area`, `descripcion`, `estado`, `archivo_pdf`, `creado_en`, `actualizado_en`) VALUES
(1, 'Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NULL, '2026-08-21 21:00:42', '2026-08-22 21:00:42'),
(2, 'María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NULL, '2026-08-19 21:00:42', '2026-08-22 21:00:42'),
(3, 'Carlos', 'López', '40111003', '3794000003', 'carlos.lopez@mail.com', 'ortopedia', 'Presupuesto de plantillas ortopédicas a medida.', 'Pendiente', NULL, '2026-08-22 15:00:42', '2026-08-22 21:00:42'),
(4, 'Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NULL, '2026-08-14 21:00:42', '2026-08-22 21:00:42'),
(5, 'Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NULL, '2026-08-20 21:00:42', '2026-08-22 21:00:42'),
(6, 'Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NULL, '2026-08-22 09:00:42', '2026-08-22 21:00:42'),
(7, 'Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NULL, '2026-08-18 21:00:42', '2026-08-22 21:00:42'),
(8, 'Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NULL, '2026-08-17 21:00:42', '2026-08-22 21:00:42'),
(9, 'Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NULL, '2026-08-22 20:30:42', '2026-08-22 21:00:42'),
(10, 'Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NULL, '2026-08-07 21:00:42', '2026-08-22 21:00:42'),
(11, 'Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NULL, '2026-08-21 21:00:58', '2026-08-22 21:00:58'),
(12, 'María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NULL, '2026-08-19 21:00:58', '2026-08-22 21:00:58'),
(14, 'Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NULL, '2026-08-14 21:00:58', '2026-08-22 21:00:58'),
(15, 'Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NULL, '2026-08-20 21:00:58', '2026-08-22 21:00:58'),
(16, 'Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NULL, '2026-08-22 09:00:58', '2026-08-22 21:00:58'),
(17, 'Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NULL, '2026-08-18 21:00:58', '2026-08-22 21:00:58'),
(18, 'Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NULL, '2026-08-17 21:00:58', '2026-08-22 21:00:58'),
(19, 'Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NULL, '2026-08-22 20:30:58', '2026-08-22 21:00:58'),
(20, 'Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NULL, '2026-08-07 21:00:58', '2026-08-22 21:00:58'),
(21, 'Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NULL, '2026-08-21 21:30:12', '2026-08-22 21:30:12'),
(22, 'María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NULL, '2026-08-19 21:30:12', '2026-08-22 21:30:12'),
(23, 'Carlos', 'López', '40111003', '3794000003', 'carlos.lopez@mail.com', 'ortopedia', 'Presupuesto de plantillas ortopédicas a medida.', 'Pendiente', NULL, '2026-08-22 15:30:12', '2026-08-22 21:30:12'),
(24, 'Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NULL, '2026-08-14 21:30:12', '2026-08-22 21:30:12'),
(25, 'Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NULL, '2026-08-20 21:30:12', '2026-08-22 21:30:12'),
(26, 'Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NULL, '2026-08-22 09:30:12', '2026-08-22 21:30:12'),
(27, 'Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NULL, '2026-08-18 21:30:12', '2026-08-22 21:30:12'),
(28, 'Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NULL, '2026-08-17 21:30:12', '2026-08-22 21:30:12'),
(29, 'Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NULL, '2026-08-22 21:00:12', '2026-08-22 21:30:12'),
(30, 'Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NULL, '2026-08-07 21:30:12', '2026-08-22 21:30:12'),
(31, 'Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NULL, '2026-08-21 21:41:14', '2026-08-22 21:41:14'),
(32, 'María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NULL, '2026-08-19 21:41:14', '2026-08-22 21:41:14'),
(33, 'Carlos', 'López', '40111003', '3794000003', 'carlos.lopez@mail.com', 'ortopedia', 'Presupuesto de plantillas ortopédicas a medida.', 'Pendiente', NULL, '2026-08-22 15:41:14', '2026-08-22 21:41:14'),
(34, 'Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NULL, '2026-08-14 21:41:14', '2026-08-22 21:41:14'),
(35, 'Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NULL, '2026-08-20 21:41:14', '2026-08-22 21:41:14'),
(36, 'Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NULL, '2026-08-22 09:41:14', '2026-08-22 21:41:14'),
(37, 'Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NULL, '2026-08-18 21:41:14', '2026-08-22 21:41:14'),
(38, 'Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NULL, '2026-08-17 21:41:14', '2026-08-22 21:41:14'),
(39, 'Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NULL, '2026-08-22 21:11:14', '2026-08-22 21:41:14'),
(40, 'Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NULL, '2026-08-07 21:41:14', '2026-08-22 21:41:14'),
(41, 'Juan', 'Pérez', '40111001', '3794000001', 'juan.perez@mail.com', 'hiperbarica', 'Presupuesto para 10 sesiones de cámara hiperbárica por pie diabético.', 'Pendiente', NULL, '2026-08-21 21:41:44', '2026-08-22 21:41:44'),
(42, 'María', 'Gómez', '40111002', '3794000002', 'maria.gomez@mail.com', 'audiologia', 'Necesito presupuesto de audífonos digitales recargables, ambos oídos.', 'Pendiente', NULL, '2026-08-19 21:41:44', '2026-08-22 21:41:44'),
(43, 'Carlos', 'López', '40111003', '3794000003', 'carlos.lopez@mail.com', 'ortopedia', 'Presupuesto de plantillas ortopédicas a medida.', 'Pendiente', NULL, '2026-08-22 15:41:44', '2026-08-22 21:41:44'),
(44, 'Ana', 'Fernández', '40111004', '3794000004', 'ana.fernandez@mail.com', 'nutricion', 'Consulta de plan nutricional personalizado, paquete de 3 meses.', 'Pendiente', NULL, '2026-08-14 21:41:44', '2026-08-22 21:41:44'),
(45, 'Luis', 'Martínez', '40111005', '3794000005', 'luis.martinez@mail.com', 'equipamiento', 'Presupuesto de silla de ruedas motorizada.', 'Pendiente', NULL, '2026-08-20 21:41:44', '2026-08-22 21:41:44'),
(46, 'Sofía', 'Benítez', '40111006', '3794000006', 'sofia.benitez@mail.com', 'tienda', 'Necesito precio de andador plegable con asiento.', 'Pendiente', NULL, '2026-08-22 09:41:44', '2026-08-22 21:41:44'),
(47, 'Pedro', 'Ramírez', '40111007', '3794000007', 'pedro.ramirez@mail.com', 'hiperbarica', 'Presupuesto de tratamiento completo, me derivó mi médico de cabecera.', 'Pendiente', NULL, '2026-08-18 21:41:44', '2026-08-22 21:41:44'),
(48, 'Lucía', 'Acosta', '40111008', '3794000008', 'lucia.acosta@mail.com', 'ortopedia', 'Presupuesto de corsé lumbar rígido.', 'Pendiente', NULL, '2026-08-17 21:41:44', '2026-08-22 21:41:44'),
(49, 'Valentina', 'Silva', 'sin_dato', '3794000009', 'valentina.silva@mail.com', 'otro', 'Consulta general sobre convenio con mi obra social para varios servicios.', 'Pendiente', NULL, '2026-08-22 21:11:44', '2026-08-22 21:41:44'),
(50, 'Diego', 'Romero', '30444333', '3794000010', 'diego.romero@mail.com', 'equipamiento', 'Presupuesto de muletas de aluminio regulables, un par.', 'Pendiente', NULL, '2026-08-07 21:41:44', '2026-08-22 21:41:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_productos`
--

CREATE TABLE `tm_productos` (
  `id` int(11) NOT NULL,
  `familia_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `modalidad` enum('venta','alquiler','venta_alquiler') NOT NULL DEFAULT 'venta',
  `imagen` varchar(255) DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_productos`
--

INSERT INTO `tm_productos` (`id`, `familia_id`, `nombre`, `descripcion`, `modalidad`, `imagen`, `destacado`, `activo`, `creado_en`) VALUES
(1, 1, 'Au A-M BTE', 'Audífono BTE con pila 312, conectividad Bluetooth directa y múltiples modos de micrófono. Clasificación IP68, 12 canales de ajuste y 4 programas manuales. 128 dB SPL.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54'),
(2, 1, 'Au A-SP BTE', 'Audífono retroauricular (BTE) con pila 13 y gran potencia de salida. Ideal para pérdidas auditivas severas, hasta 81 dB de ganancia y 155 h de duración. 139 dB SPL, Bluetooth, IP68, Telecoil.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54'),
(3, 1, 'Au A-UP BTE', 'Audífono ultra potente con pila 675, ganancia máxima de hasta 84 dB. Clasificación IP68 y duración prolongada de 330 h, ideal para pérdidas auditivas profundas. 141 dB SPL, Bluetooth, Telecoil.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54'),
(4, 9, 'Silla de ruedas plegable', 'Estructura liviana, plegable para fácil traslado.', 'venta_alquiler', NULL, 1, 1, '2026-07-21 00:22:54'),
(5, 10, 'Muletas regulables', 'Aluminio, altura ajustable.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54'),
(6, 10, 'Bastón plegable con asiento', 'Con función de asiento integrado para descanso.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54'),
(7, 15, 'Concentrador de oxígeno', 'Equipo para oxigenoterapia domiciliaria continua.', 'venta_alquiler', NULL, 1, 1, '2026-07-21 00:22:54'),
(8, 14, 'Nebulizador', 'Para tratamientos respiratorios en domicilio.', 'venta', NULL, 1, 1, '2026-07-21 00:22:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_recursos`
--

CREATE TABLE `tm_recursos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `area` varchar(50) DEFAULT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `archivo_ruta` varchar(500) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `publico` tinyint(1) NOT NULL DEFAULT 0,
  `subido_por` int(11) NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_recursos`
--

INSERT INTO `tm_recursos` (`id`, `titulo`, `descripcion`, `area`, `archivo_nombre`, `archivo_ruta`, `tipo`, `publico`, `subido_por`, `creado_en`) VALUES
(1, 'FICHA DE ADMISION', '', NULL, 'Admission_Form_tecnomedic.pdf', 'storage/recursos/recurso_6a84e9b7b1b01.pdf', 'PDF', 0, 6, '2026-08-18 20:24:39'),
(2, 'DISCLAIMER', '', NULL, 'Disclaimer_Form_tecnomedic.pdf', 'storage/recursos/recurso_6a84e9cf70467.pdf', 'PDF', 0, 6, '2026-08-18 20:25:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_sesiones_bot`
--

CREATE TABLE `tm_sesiones_bot` (
  `phone` varchar(30) NOT NULL,
  `step` varchar(30) DEFAULT 'menu',
  `nombre` varchar(100) DEFAULT '',
  `apellido` varchar(100) DEFAULT '',
  `dni` varchar(20) DEFAULT '',
  `obra_social` varchar(50) DEFAULT '',
  `telefono` varchar(30) DEFAULT '',
  `email` varchar(150) DEFAULT '',
  `fecha` varchar(10) DEFAULT '',
  `hora` varchar(5) DEFAULT '',
  `disp` text DEFAULT '',
  `fila_turno` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_testimonios`
--

CREATE TABLE `tm_testimonios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` varchar(100) DEFAULT NULL,
  `texto` text NOT NULL,
  `resultado` varchar(255) DEFAULT NULL,
  `media_tipo` enum('imagen','video','ninguno') DEFAULT 'ninguno',
  `media_url` varchar(500) DEFAULT NULL,
  `video_thumb` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_tratamientos`
--

CREATE TABLE `tm_tratamientos` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `profesional_id` int(11) NOT NULL,
  `area` varchar(50) DEFAULT NULL,
  `fecha` date NOT NULL,
  `descripcion` text NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_tratamientos`
--

INSERT INTO `tm_tratamientos` (`id`, `paciente_id`, `profesional_id`, `area`, `fecha`, `descripcion`, `creado_en`) VALUES
(1, 2, 6, 'nutricion', '2026-08-19', 'nutri', '2026-08-18 19:25:08'),
(2, 2, 6, 'audiologia', '2026-08-19', 'audifonos', '2026-08-18 19:42:34'),
(33, 11, 28, 'equipamiento', '2026-06-20', 'Entrega y ajuste de silla de ruedas. Se explica mantenimiento básico.', '2026-08-22 21:40:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_turnos`
--

CREATE TABLE `tm_turnos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) DEFAULT '',
  `obra_social` varchar(50) DEFAULT '',
  `area` varchar(50) DEFAULT 'hiperbarica',
  `telefono` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time(5) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Pendiente',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_turnos`
--

INSERT INTO `tm_turnos` (`id`, `nombre`, `apellido`, `dni`, `obra_social`, `area`, `telefono`, `email`, `fecha`, `hora`, `estado`, `creado_en`) VALUES
(9, 'Pedro', 'Ramírez', '29888777', 'PAMI', 'hiperbarica', '3794000007', 'pedro.ramirez@mail.com', '2026-07-20', '10:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(10, 'Lucía', 'Acosta', '32111999', 'OSDE', 'hiperbarica', '3794000008', 'lucia.acosta@mail.com', '2026-07-20', '10:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(11, 'Diego', 'Romero', '30444333', 'Particular', 'hiperbarica', '3794000009', 'diego.romero@mail.com', '2026-07-20', '10:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(12, 'Valeria', 'Silva', '31555777', 'IOSCOR', 'hiperbarica', '3794000010', 'valeria.silva@mail.com', '2026-07-20', '11:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(14, 'María', 'Gómez', '28999111', 'IOSCOR', 'hiperbarica', '3794000002', 'maria.gomez@mail.com', '2026-07-21', '08:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(15, 'Carlos', 'López', '31222333', 'PAMI', 'hiperbarica', '3794000003', 'carlos.lopez@mail.com', '2026-07-21', '08:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(16, 'Ana', 'Fernández', '33444555', 'Particular', 'hiperbarica', '3794000004', 'ana.fernandez@mail.com', '2026-07-21', '09:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(17, 'Luis', 'Martínez', '27888999', 'OSDE', 'hiperbarica', '3794000005', 'luis.martinez@mail.com', '2026-07-21', '09:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(18, 'Laura', 'Sosa', '33666111', 'IOSCOR', 'hiperbarica', '3794000011', 'laura.sosa@mail.com', '2026-07-21', '09:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(19, 'Miguel', 'Herrera', '29999888', 'PAMI', 'hiperbarica', '3794000012', 'miguel.herrera@mail.com', '2026-07-21', '10:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(28, 'Gabriel', 'Suárez', '31111444', 'PAMI', 'hiperbarica', '3794000016', 'gabriel.suarez@mail.com', '2026-07-22', '09:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(29, 'Paula', 'Luna', '33322111', 'OSDE', 'hiperbarica', '3794000017', 'paula.luna@mail.com', '2026-07-22', '10:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(30, 'Raúl', 'Méndez', '29000222', 'IOSCOR', 'hiperbarica', '3794000018', 'raul.mendez@mail.com', '2026-07-22', '10:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(31, 'Julieta', 'Leiva', '34777888', 'Particular', 'hiperbarica', '3794000019', 'julieta.leiva@mail.com', '2026-07-22', '10:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(32, 'Verónica', 'Paz', '30888555', 'OSDE', 'hiperbarica', '3794000020', 'veronica.paz@mail.com', '2026-07-22', '11:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(33, 'Juan', 'Pérez', '30111222', 'OSDE', 'hiperbarica', '3794000001', 'juan.perez@mail.com', '2026-08-15', '16:30:00.00000', 'Pendiente', '2026-07-18 22:04:48'),
(34, 'María', 'Gómez', '28999111', 'IOSCOR', 'hiperbarica', '3794000002', 'maria.gomez@mail.com', '2026-07-23', '08:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(35, 'Carlos', 'López', '31222333', 'PAMI', 'hiperbarica', '3794000003', 'carlos.lopez@mail.com', '2026-07-23', '08:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(36, 'Ana', 'Fernández', '33444555', 'Particular', 'hiperbarica', '3794000004', 'ana.fernandez@mail.com', '2026-07-23', '09:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(37, 'Luis', 'Martínez', '27888999', 'OSDE', 'hiperbarica', '3794000005', 'luis.martinez@mail.com', '2026-07-23', '09:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(38, 'Daniel', 'Aguirre', '29999111', 'PAMI', 'hiperbarica', '3794000021', 'daniel.aguirre@mail.com', '2026-07-23', '09:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(39, 'Camila', 'Bravo', '34444888', 'IOSCOR', 'hiperbarica', '3794000022', 'camila.bravo@mail.com', '2026-07-23', '10:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(40, 'Victoria', 'Flores', '32222333', 'OSDE', 'hiperbarica', '3794000023', 'victoria.flores@mail.com', '2026-07-23', '10:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(41, 'Matías', 'Vera', '30000999', 'Particular', 'hiperbarica', '3794000024', 'matias.vera@mail.com', '2026-07-23', '10:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(42, 'Jimena', 'Campos', '31111999', 'IOSCOR', 'hiperbarica', '3794000025', 'jimena.campos@mail.com', '2026-07-23', '11:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(43, 'Juan', 'Pérez', '30111222', 'OSDE', 'hiperbarica', '3794775341', 'andrestester0001@gmail.com', '2026-08-15', '11:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(44, 'María', 'Gómez', '28999111', 'IOSCOR', 'hiperbarica', '3794000002', 'maria.gomez@mail.com', '2026-07-24', '08:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(45, 'Carlos', 'López', '31222333', 'PAMI', 'hiperbarica', '3794000003', 'carlos.lopez@mail.com', '2026-07-24', '08:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(46, 'Ana', 'Fernández', '33444555', 'Particular', 'hiperbarica', '3794000004', 'ana.fernandez@mail.com', '2026-07-24', '09:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(47, 'Luis', 'Martínez', '27888999', 'OSDE', 'hiperbarica', '3794000005', 'luis.martinez@mail.com', '2026-07-24', '09:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(48, 'Federico', 'Almirón', '33333999', 'OSDE', 'hiperbarica', '3794000026', 'federico.almiron@mail.com', '2026-07-24', '09:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(49, 'Rocío', 'Ponce', '34555444', 'IOSCOR', 'hiperbarica', '3794000027', 'rocio.ponce@mail.com', '2026-07-24', '10:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(50, 'Cristian', 'Barrios', '29888222', 'PAMI', 'hiperbarica', '3794000028', 'cristian.barrios@mail.com', '2026-07-24', '10:20:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(51, 'Carla', 'Giménez', '32000444', 'Particular', 'hiperbarica', '3794000029', 'carla.gimenez@mail.com', '2026-07-24', '10:40:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(52, 'Gustavo', 'Navarro', '30999333', 'OSDE', 'hiperbarica', '3794000030', 'gustavo.navarro@mail.com', '2026-07-24', '11:00:00.00000', 'Confirmado', '2026-07-18 22:04:48'),
(54, 'Roque', 'Fort', '30123123', 'OSDE', 'hiperbarica', '3794775341', 'andrestester0001@gmail.com', '2026-08-22', '08:30:00.00000', 'Confirmado', '2026-08-03 23:27:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_usuarios`
--

CREATE TABLE `tm_usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('paciente','profesional','admin') NOT NULL DEFAULT 'paciente',
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `telefono` varchar(30) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_aprobacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_usuarios`
--

INSERT INTO `tm_usuarios` (`id`, `email`, `dni`, `password_hash`, `rol`, `nombre`, `apellido`, `titulo`, `especialidad`, `descripcion`, `foto`, `instagram`, `orden`, `telefono`, `activo`, `fecha_alta`, `fecha_aprobacion`) VALUES
(1, 'andrestester0001@gmail.com', '12123456', 'Tecno2026!', 'admin', 'andres', 'p', NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2026-07-25 12:43:47', '2026-07-25 12:43:47'),
(2, 'andrestester0001-2@gmail.com', '11123456', '$2y$10$DLgHL09GKisqviceEk5YNOdEF/uLtbwS6vaJLJzyibDUq6Q4w9QHG', 'paciente', 'Gustavo', 'Valdez', NULL, NULL, NULL, NULL, NULL, 0, '3794775341', 1, '2026-07-25 20:39:11', '2026-07-25 20:46:38'),
(3, 'andrestester0001-3@gmail.com', '11000000', '$2y$10$OkZHrhIP5vGSv7f4Y2Y/Zu/zgc7wDJbu2/Gj4C.Xn9fDYMHTmKq8C', 'profesional', 'Dra  Maria', 'Unger', 'medica', 'fonoaudiología en general', 'fonoaudiologia', 'storage/staff/staff_6a9b58f6622a3.jpg', '', 0, '37945555555', 1, '2026-07-25 20:48:03', '2026-07-25 20:48:03'),
(4, 'andrestester0001-4@gmail.com', '12000000', '$2y$10$SQDr6SBsVv6RolwiPhq/M.awbPNYLCx9l/DCpMxewH3VQcwcTOR1C', 'profesional', 'Dra Carolina', 'Unger', 'medica', 'nutricion  heridas', 'posgrado en heridas nutricion etc', 'storage/staff/staff_6a90570fb48ff.jpg', '', 1, '37945555554', 1, '2026-07-25 20:48:50', '2026-07-25 20:48:50'),
(6, 'andrestester0001-5@gmail.com', '55000111', '$2y$10$FJ8dboyIkCAhf1.eagM3ReUD.pNEghWPcAX3eh1ssjXy3m/8cb4sS', 'admin', 'admin', 'admin', NULL, NULL, NULL, NULL, NULL, 0, NULL, 1, '2026-08-11 19:40:14', '2026-08-11 19:40:14'),
(7, 'paciente1@test.com', '40111001', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Juan', 'Pérez', NULL, NULL, NULL, NULL, NULL, 0, '3794111001', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(8, 'paciente2@test.com', '40111002', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'María', 'González', NULL, NULL, NULL, NULL, NULL, 0, '3794111002', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(9, 'paciente3@test.com', '40111003', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Carlos', 'Fernández', NULL, NULL, NULL, NULL, NULL, 0, '3794111003', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(10, 'paciente4@test.com', '40111004', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Ana', 'López', NULL, NULL, NULL, NULL, NULL, 0, '3794111004', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(11, 'paciente5@test.com', '40111005', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Roberto', 'Martínez', NULL, NULL, NULL, NULL, NULL, 0, '3794111005', 1, '2026-08-22 21:02:26', '2026-09-06 13:54:34'),
(12, 'paciente6@test.com', '40111006', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Lucía', 'Sánchez', NULL, NULL, NULL, NULL, NULL, 0, '3794111006', 1, '2026-08-22 21:02:26', '2026-09-06 19:46:09'),
(13, 'paciente7@test.com', '40111007', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Pablo', 'Romero', NULL, NULL, NULL, NULL, NULL, 0, '3794111007', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(14, 'paciente8@test.com', '40111008', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Sofía', 'Torres', NULL, NULL, NULL, NULL, NULL, 0, '3794111008', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(15, 'paciente9@test.com', '40111009', '$2b$10$iJpj8281pQgW5M.Ikkn3NOnS2/HLQkEajJyaZWRFsSbjJZJRkPnSe', 'paciente', 'Miguel', 'Flores', NULL, NULL, NULL, NULL, NULL, 0, '3794111009', 1, '2026-08-22 21:02:26', '2026-08-22 21:02:26'),
(16, 'paciente10@test.com', '40111010', '$2y$10$qCFFxx7.Z.cO2Jq5Dt8Q8.AtC8AEZ50DjvQ/QztSO/YBjoiOFAGq.', 'paciente', 'Valentina', 'Benítez', NULL, NULL, NULL, NULL, NULL, 0, '3794111010', 1, '2026-08-22 21:02:26', '2026-09-07 19:55:25'),
(28, 'prokine@gmail.com', '30111226', '$2y$10$FJ8dboyIkCAhf1.eagM3ReUD.pNEghWPcAX3eh1ssjX...', 'profesional', 'Christian', 'Villa', 'kinesiologo', 'rehabilitacion', 'rehabilitacion pacientes neurologicos', 'storage/staff/staff_6a9b5a06463bc.png', '', 0, NULL, 1, '2026-08-22 21:39:59', '2026-09-04 19:25:22');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tm_asignaciones`
--
ALTER TABLE `tm_asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paciente_profesional` (`paciente_id`,`profesional_id`),
  ADD KEY `profesional_id` (`profesional_id`);

--
-- Indices de la tabla `tm_categorias`
--
ALTER TABLE `tm_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `tm_comidas`
--
ALTER TABLE `tm_comidas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `registrado_por` (`registrado_por`);

--
-- Indices de la tabla `tm_config`
--
ALTER TABLE `tm_config`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `tm_contactos`
--
ALTER TABLE `tm_contactos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tm_estudios`
--
ALTER TABLE `tm_estudios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `subido_por` (`subido_por`);

--
-- Indices de la tabla `tm_familias`
--
ALTER TABLE `tm_familias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `tm_historia_clinica`
--
ALTER TABLE `tm_historia_clinica`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actualizado_por` (`actualizado_por`),
  ADD KEY `idx_historia_pacienteFecha` (`paciente_id`,`fecha`,`id`);

--
-- Indices de la tabla `tm_medicamentos_recetados`
--
ALTER TABLE `tm_medicamentos_recetados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indices de la tabla `tm_mediciones`
--
ALTER TABLE `tm_mediciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `registrado_por` (`registrado_por`);

--
-- Indices de la tabla `tm_novedades`
--
ALTER TABLE `tm_novedades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tm_objetivo_nutricional`
--
ALTER TABLE `tm_objetivo_nutricional`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paciente_id` (`paciente_id`),
  ADD KEY `actualizado_por` (`actualizado_por`);

--
-- Indices de la tabla `tm_obras_sociales`
--
ALTER TABLE `tm_obras_sociales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tm_perfiles_paciente`
--
ALTER TABLE `tm_perfiles_paciente`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `obra_social_id` (`obra_social_id`);

--
-- Indices de la tabla `tm_perfiles_profesional`
--
ALTER TABLE `tm_perfiles_profesional`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tm_personas`
--
ALTER TABLE `tm_personas`
  ADD PRIMARY KEY (`dni`),
  ADD KEY `obra_social_id` (`obra_social_id`);

--
-- Indices de la tabla `tm_presupuestos`
--
ALTER TABLE `tm_presupuestos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dni_cuit` (`dni_cuit`);

--
-- Indices de la tabla `tm_productos`
--
ALTER TABLE `tm_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `familia_id` (`familia_id`);

--
-- Indices de la tabla `tm_recursos`
--
ALTER TABLE `tm_recursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subido_por` (`subido_por`);

--
-- Indices de la tabla `tm_sesiones_bot`
--
ALTER TABLE `tm_sesiones_bot`
  ADD PRIMARY KEY (`phone`);

--
-- Indices de la tabla `tm_testimonios`
--
ALTER TABLE `tm_testimonios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tm_tratamientos`
--
ALTER TABLE `tm_tratamientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `profesional_id` (`profesional_id`);

--
-- Indices de la tabla `tm_turnos`
--
ALTER TABLE `tm_turnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tm_usuarios`
--
ALTER TABLE `tm_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tm_asignaciones`
--
ALTER TABLE `tm_asignaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tm_categorias`
--
ALTER TABLE `tm_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tm_comidas`
--
ALTER TABLE `tm_comidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `tm_contactos`
--
ALTER TABLE `tm_contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `tm_estudios`
--
ALTER TABLE `tm_estudios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `tm_familias`
--
ALTER TABLE `tm_familias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `tm_historia_clinica`
--
ALTER TABLE `tm_historia_clinica`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `tm_medicamentos_recetados`
--
ALTER TABLE `tm_medicamentos_recetados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `tm_mediciones`
--
ALTER TABLE `tm_mediciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `tm_novedades`
--
ALTER TABLE `tm_novedades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_objetivo_nutricional`
--
ALTER TABLE `tm_objetivo_nutricional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tm_obras_sociales`
--
ALTER TABLE `tm_obras_sociales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tm_perfiles_paciente`
--
ALTER TABLE `tm_perfiles_paciente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tm_perfiles_profesional`
--
ALTER TABLE `tm_perfiles_profesional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_presupuestos`
--
ALTER TABLE `tm_presupuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `tm_productos`
--
ALTER TABLE `tm_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tm_recursos`
--
ALTER TABLE `tm_recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tm_testimonios`
--
ALTER TABLE `tm_testimonios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tm_tratamientos`
--
ALTER TABLE `tm_tratamientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `tm_turnos`
--
ALTER TABLE `tm_turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `tm_usuarios`
--
ALTER TABLE `tm_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tm_asignaciones`
--
ALTER TABLE `tm_asignaciones`
  ADD CONSTRAINT `tm_asignaciones_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_asignaciones_ibfk_2` FOREIGN KEY (`profesional_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_comidas`
--
ALTER TABLE `tm_comidas`
  ADD CONSTRAINT `tm_comidas_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_comidas_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_estudios`
--
ALTER TABLE `tm_estudios`
  ADD CONSTRAINT `tm_estudios_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_estudios_ibfk_2` FOREIGN KEY (`subido_por`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_familias`
--
ALTER TABLE `tm_familias`
  ADD CONSTRAINT `tm_familias_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `tm_categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_medicamentos_recetados`
--
ALTER TABLE `tm_medicamentos_recetados`
  ADD CONSTRAINT `tm_medicamentos_recetados_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_mediciones`
--
ALTER TABLE `tm_mediciones`
  ADD CONSTRAINT `tm_mediciones_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_mediciones_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_objetivo_nutricional`
--
ALTER TABLE `tm_objetivo_nutricional`
  ADD CONSTRAINT `tm_objetivo_nutricional_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_objetivo_nutricional_ibfk_2` FOREIGN KEY (`actualizado_por`) REFERENCES `tm_usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tm_perfiles_paciente`
--
ALTER TABLE `tm_perfiles_paciente`
  ADD CONSTRAINT `tm_perfiles_paciente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_perfiles_paciente_ibfk_2` FOREIGN KEY (`obra_social_id`) REFERENCES `tm_obras_sociales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tm_perfiles_profesional`
--
ALTER TABLE `tm_perfiles_profesional`
  ADD CONSTRAINT `tm_perfiles_profesional_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_personas`
--
ALTER TABLE `tm_personas`
  ADD CONSTRAINT `tm_personas_ibfk_1` FOREIGN KEY (`obra_social_id`) REFERENCES `tm_obras_sociales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tm_productos`
--
ALTER TABLE `tm_productos`
  ADD CONSTRAINT `tm_productos_ibfk_1` FOREIGN KEY (`familia_id`) REFERENCES `tm_familias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_recursos`
--
ALTER TABLE `tm_recursos`
  ADD CONSTRAINT `tm_recursos_ibfk_1` FOREIGN KEY (`subido_por`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tm_tratamientos`
--
ALTER TABLE `tm_tratamientos`
  ADD CONSTRAINT `tm_tratamientos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tm_tratamientos_ibfk_2` FOREIGN KEY (`profesional_id`) REFERENCES `tm_usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
