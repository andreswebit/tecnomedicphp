-- ══════════════════════════════════════════════════════════════
-- TECNOMEDIC — Fase H: Presupuestos
-- ══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS tm_presupuestos (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    dni_cuit        VARCHAR(20)  NOT NULL,       -- DNI o CUIT/CUIL (para consulta desde el bot)
    telefono        VARCHAR(30)  DEFAULT NULL,
    email           VARCHAR(150) NOT NULL,
    area            VARCHAR(50)  DEFAULT NULL,   -- audiologia|hiperbarica|nutricion|ortopedia|equipamiento|NULL=general/tienda
    descripcion     TEXT NOT NULL,               -- qué necesita presupuestar
    estado          ENUM('Pendiente','Elaborado','Enviado') NOT NULL DEFAULT 'Pendiente',
    archivo_pdf     VARCHAR(500) DEFAULT NULL,   -- ruta relativa en storage/presupuestos/
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dni_cuit (dni_cuit)                -- para que el bot consulte rápido por CUIT/DNI
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
