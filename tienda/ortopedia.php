<?php
/**
 * TECNOMEDIC: Tienda de ortopedia y movilidad (pagina publica).
 *
 * Esta pagina solo arma las piezas. Cada bloque vive en includes/tienda_ui/:
 *   data.php       contenido (productos, categorias, contacto)
 *   helpers.php    funciones de formato y URLs
 *   layout_*.php   estructura HTML (head, scripts)
 *   header, hero, ofertas, destacados, categorias, catalogo, beneficios, footer, carrito
 *   cards/*.php    tarjetas de producto
 * Estilos: static/tienda-ortopedia.css (+ -extra.css). Logica: static/js/tienda-ortopedia.js
 */
require_once __DIR__ . '/../includes/tienda_ui/bootstrap.php';

// Muestra los enlaces "Administrar" y "Volver al panel".
// Cambialo a true solo cuando el usuario sea administrador (por ejemplo con tu sistema de sesion).
$tienda_modo_admin = false; // se ajusta cuando haya lógica de admin en sesión


$ui = __DIR__ . '/../includes/tienda_ui/';

require $ui . 'layout_top.php';
require $ui . 'header.php';
require $ui . 'hero.php';
require $ui . 'ofertas.php';
require $ui . 'destacados.php';
require $ui . 'categorias.php';
require $ui . 'catalogo.php';
require $ui . 'beneficios.php';
require $ui . 'footer.php';
require $ui . 'carrito.php';
require $ui . 'layout_bottom.php';
