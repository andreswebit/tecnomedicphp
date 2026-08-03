-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Tienda: categorías, familias y productos
-- Correr en phpMyAdmin (pestaña SQL) sobre la base ya usada
-- (c1452348_wordpre en Ferozo / tecnomedic_local en XAMPP)
-- ══════════════════════════════════════════════════════════════

DROP TABLE IF EXISTS tm_productos;
DROP TABLE IF EXISTS tm_familias;
DROP TABLE IF EXISTS tm_categorias;

CREATE TABLE tm_categorias (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    slug     VARCHAR(50)  NOT NULL UNIQUE,
    nombre   VARCHAR(100) NOT NULL,
    icono    VARCHAR(50)  DEFAULT 'fa-box',
    orden    INT          DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tm_familias (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id  INT NOT NULL,
    slug          VARCHAR(60)  NOT NULL,
    nombre        VARCHAR(120) NOT NULL,
    orden         INT DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES tm_categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tm_productos (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    familia_id   INT NOT NULL,
    nombre       VARCHAR(150) NOT NULL,
    descripcion  TEXT,
    modalidad    ENUM('venta','alquiler','venta_alquiler') NOT NULL DEFAULT 'venta',
    imagen       VARCHAR(255) DEFAULT NULL,
    destacado    TINYINT(1)   DEFAULT 0,
    activo       TINYINT(1)   DEFAULT 1,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (familia_id) REFERENCES tm_familias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Categorías (mismas 4 que la Home/menú, sin Hiperbárica) ─────
INSERT INTO tm_categorias (slug, nombre, icono, orden) VALUES
('audiologia',              'Audiología',                     'fa-headphones', 1),
('nutricion',                'Nutrición',                       'fa-apple-whole', 2),
('ortopedia-rehabilitacion', 'Ortopedia y Rehabilitación',      'fa-wheelchair', 3),
('equipamiento-medico',      'Equipamiento Médico y Quirúrgico','fa-kit-medical', 4);

-- ── Familias por categoría ───────────────────────────────────────
INSERT INTO tm_familias (categoria_id, slug, nombre, orden) VALUES
-- Audiología
(1, 'audifonos',      'Audífonos', 1),
(1, 'accesorios',      'Accesorios y conectividad', 2),
(1, 'pilas',            'Pilas y cargadores', 3),
(1, 'protectores',      'Protectores auditivos', 4),
-- Nutrición
(2, 'proteinas',        'Proteínas', 1),
(2, 'creatina',          'Creatina', 2),
(2, 'omega3',            'Omega 3', 3),
(2, 'vitaminas',         'Vitaminas y minerales', 4),
(2, 'aminoacidos',       'Aminoácidos', 5),
-- Ortopedia y Rehabilitación
(3, 'soportes',          'Soportes articulares (fajas, rodilleras, tobilleras, muñequeras)', 1),
(3, 'movilidad',         'Ayudas para la movilidad', 2),
(3, 'rehabilitacion',    'Productos para rehabilitación', 3),
(3, 'compresion',        'Medias de compresión', 4),
-- Equipamiento Médico y Quirúrgico
(4, 'diagnostico',       'Tensiómetros y oxímetros', 1),
(4, 'respiratorio',      'Equipamiento respiratorio (nebulizadores, aspiradores)', 2),
(4, 'oxigenoterapia',    'Oxigenoterapia domiciliaria (concentradores, tubos, mochilas)', 3),
(4, 'quirurgico',        'Instrumental e insumos quirúrgicos', 4);

-- ── Productos de ejemplo (los reales de audífonos, del documento) ──
INSERT INTO tm_productos (familia_id, nombre, descripcion, modalidad, destacado) VALUES
(1, 'Au A-M BTE',  'Audífono BTE con pila 312, conectividad Bluetooth directa y múltiples modos de micrófono. Clasificación IP68, 12 canales de ajuste y 4 programas manuales. 128 dB SPL.', 'venta', 1),
(1, 'Au A-SP BTE', 'Audífono retroauricular (BTE) con pila 13 y gran potencia de salida. Ideal para pérdidas auditivas severas, hasta 81 dB de ganancia y 155 h de duración. 139 dB SPL, Bluetooth, IP68, Telecoil.', 'venta', 1),
(1, 'Au A-UP BTE', 'Audífono ultra potente con pila 675, ganancia máxima de hasta 84 dB. Clasificación IP68 y duración prolongada de 330 h, ideal para pérdidas auditivas profundas. 141 dB SPL, Bluetooth, Telecoil.', 'venta', 1),

(9,  'Silla de ruedas plegable',  'Estructura liviana, plegable para fácil traslado.', 'venta_alquiler', 1),
(10, 'Muletas regulables',        'Aluminio, altura ajustable.', 'venta', 0),
(10, 'Bastón plegable con asiento', 'Con función de asiento integrado para descanso.', 'venta', 0),

(15, 'Concentrador de oxígeno',   'Equipo para oxigenoterapia domiciliaria continua.', 'venta_alquiler', 1),
(14, 'Nebulizador',                'Para tratamientos respiratorios en domicilio.', 'venta', 0);

-- Nota: esto es un punto de partida. Cargá el resto del catálogo real
-- desde admin/productos.php (lo armamos en el siguiente paso si querés
-- un CRUD, o directo por phpMyAdmin mientras tanto).
