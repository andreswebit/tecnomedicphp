<?php
/**
 * TECNOMEDIC Tienda de ortopedia: carga helpers y datos.
 * Se incluye al inicio de tienda/ortopedia.php
 */

// Necesitamos BASE_URL/HOME_URL para que b() arme rutas correctas
// cuando esta página se ejecuta desde un subdirectorio.
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../db_config.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/data.php';
