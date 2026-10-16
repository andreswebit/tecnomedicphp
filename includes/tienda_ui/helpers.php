<?php
/**
 * TECNOMEDIC Tienda de ortopedia: funciones de ayuda para las vistas.
 */

// Si el sitio todavia no define b() (prefijo de URL base), se intenta armarlo con BASE_URL.
// Esto evita roturas cuando el sitio corre en un subdirectorio (ej: /tecnomedic_php).
if (!function_exists('b')) {
    function b(string $path): string
    {
        // $path puede venir con o sin leading slash; nos normaliza.
        $path = '/' . ltrim($path, '/');
        // Si existe BASE_URL, armamos la URL absoluta con el prefijo del sitio.
        if (defined('BASE_URL')) {
            return rtrim(BASE_URL, '/') . $path;
        }
        return $path;
    }
}

/** Escapa texto para HTML. */
function tienda_e($s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Minusculas con soporte de tildes (usa mbstring si esta disponible). */
function tienda_minusculas(string $s): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}

/** $6.50 */
function tienda_precio($n): string
{
    return '$' . number_format((float)$n, 2, '.', ',');
}

/** URL de una imagen de producto (archivo dentro de static/img/tienda/). */
function tienda_img(string $archivo): string
{
    return b('/static/img/tienda/' . ltrim($archivo, '/'));
}

/** URL del detalle de un producto. */
function tienda_url_producto($id): string
{
    global $TIENDA;
    return b(sprintf($TIENDA['url_producto'], (int)$id));
}

/** Porcentaje de descuento (0 si no esta en oferta). */
function tienda_descuento(array $p): int
{
    if (empty($p['precio_anterior'])) return 0;
    return (int)round((1 - $p['precio'] / $p['precio_anterior']) * 100);
}

/** Monto ahorrado (0 si no esta en oferta). */
function tienda_ahorro(array $p): float
{
    if (empty($p['precio_anterior'])) return 0.0;
    return (float)$p['precio_anterior'] - (float)$p['precio'];
}

/** Atributos del boton "Agregar" que lee static/js/tienda-ortopedia.js */
function tienda_attrs_carrito(array $p): string
{
    return 'data-add-cart'
        . ' data-id="' . (int)$p['id'] . '"'
        . ' data-nombre="' . tienda_e($p['nombre']) . '"'
        . ' data-precio="' . tienda_e(number_format((float)$p['precio'], 2, '.', '')) . '"'
        . ' data-imagen="' . tienda_e(tienda_img($p['imagen'])) . '"';
}

/** Incluye una vista parcial con variables propias (aisla el alcance). */
function tienda_render(string $archivo, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    include __DIR__ . '/' . $archivo;
}
