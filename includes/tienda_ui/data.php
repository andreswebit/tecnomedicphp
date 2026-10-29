<?php
/**
 * TECNOMEDIC Tienda de ortopedia: datos de la pagina.
 * Aca se edita el contenido (productos, categorias, contacto). Si luego pasas
 * los productos a la base de datos, reemplaza estos arreglos por una consulta
 * que devuelva la misma estructura.
 */

// Textos y enlaces generales
$TIENDA = [
    'titulo' => 'TecnoMedic - Ortopedia y movilidad',
    'marca_1' => 'TECNO',
    'marca_2' => 'MEDIC',
    'etiqueta' => 'Ortopedia',
    'telefono' => '+54 11 4000-0000',
    'email' => 'tienda@tecnomedic.com',
    'url_producto' => '/tienda/producto.php?id=%d',
    'url_checkout' => '/contacto.php',
    'url_panel' => '/admin/index.php',
    'url_admin_ofertas' => '/admin/tienda_section.php',
    'url_admin_destacados' => '/admin/productos_destacados.php',
    'hero_imagen' => 'ortho-hero-01.jpg',
];

// Categorias (el orden define los botones y el pie de pagina)
$TIENDA_CATEGORIAS = ['Movilidad', 'Bastones y apoyo', 'Vendajes y curación', 'Insumos hospitalarios'];

// Productos (clave = id). precio_anterior != null => esta en oferta.
$TIENDA_PRODUCTOS = [
    '100000' => [
        'id' => 100000,
        'nombre' => 'Muletas axilares de aluminio (par)',
        'categoria' => 'Movilidad',
        'precio' => 65.0,
        'precio_anterior' => null,
        'stock' => 40,
        'imagen' => 'ortho-crutches-01.jpg',
        'descripcion' => '',
    ],
    '100001' => [
        'id' => 100001,
        'nombre' => 'Alcohol en gel hospitalario 1 L',
        'categoria' => 'Insumos hospitalarios',
        'precio' => 6.5,
        'precio_anterior' => 8.5,
        'stock' => 120,
        'imagen' => 'ortho-gel-01.jpg',
        'descripcion' => '',
    ],
    '100002' => [
        'id' => 100002,
        'nombre' => 'Andador plegable con altura regulable',
        'categoria' => 'Movilidad',
        'precio' => 69.0,
        'precio_anterior' => 89.0,
        'stock' => 30,
        'imagen' => 'ortho-walker-01.jpg',
        'descripcion' => 'Andador de aluminio con mangos ergonomicos, altura regulable en varias posiciones y plegado compacto. Brinda estabilidad y seguridad al caminar.',
    ],
    '100003' => [
        'id' => 100003,
        'nombre' => 'Inmovilizador de rodilla ajustable',
        'categoria' => 'Vendajes y curación',
        'precio' => 45.0,
        'precio_anterior' => null,
        'stock' => 35,
        'imagen' => 'ortho-knee-01.jpg',
        'descripcion' => '',
    ],
    '100004' => [
        'id' => 100004,
        'nombre' => 'Baston de apoyo con empunadura ergonomica',
        'categoria' => 'Bastones y apoyo',
        'precio' => 22.0,
        'precio_anterior' => null,
        'stock' => 60,
        'imagen' => 'ortho-cane-01.jpg',
        'descripcion' => '',
    ],
    '100005' => [
        'id' => 100005,
        'nombre' => 'Gasas esteriles estuche x50',
        'categoria' => 'Insumos hospitalarios',
        'precio' => 7.5,
        'precio_anterior' => null,
        'stock' => 180,
        'imagen' => 'ortho-gauze-01.jpg',
        'descripcion' => '',
    ],
    '100006' => [
        'id' => 100006,
        'nombre' => 'Guantes de nitrilo caja x100',
        'categoria' => 'Insumos hospitalarios',
        'precio' => 11.0,
        'precio_anterior' => 14.0,
        'stock' => 200,
        'imagen' => 'ortho-gloves-01.jpg',
        'descripcion' => '',
    ],
    '100007' => [
        'id' => 100007,
        'nombre' => 'Silla de ruedas de transporte ultraliviana',
        'categoria' => 'Movilidad',
        'precio' => 199.0,
        'precio_anterior' => 249.0,
        'stock' => 12,
        'imagen' => 'ortho-wheelchair-02.jpg',
        'descripcion' => '',
    ],
    '100008' => [
        'id' => 100008,
        'nombre' => 'Silla de ruedas plegable de acero',
        'categoria' => 'Movilidad',
        'precio' => 185.0,
        'precio_anterior' => null,
        'stock' => 24,
        'imagen' => 'ortho-wheelchair-01.jpg',
        'descripcion' => '',
    ],
    '100009' => [
        'id' => 100009,
        'nombre' => 'Jeringas desechables 5 ml (pack x20)',
        'categoria' => 'Insumos hospitalarios',
        'precio' => 6.0,
        'precio_anterior' => null,
        'stock' => 300,
        'imagen' => 'ortho-syringe-01.jpg',
        'descripcion' => '',
    ],
    '100010' => [
        'id' => 100010,
        'nombre' => 'Venda elastica cohesiva 10 cm (pack x4)',
        'categoria' => 'Vendajes y curación',
        'precio' => 9.5,
        'precio_anterior' => null,
        'stock' => 150,
        'imagen' => 'ortho-bandage-01.jpg',
        'descripcion' => '',
    ],
    '100011' => [
        'id' => 100011,
        'nombre' => 'Faja lumbar con soporte',
        'categoria' => 'Vendajes y curación',
        'precio' => 28.5,
        'precio_anterior' => 38.0,
        'stock' => 50,
        'imagen' => 'ortho-lumbar-01.jpg',
        'descripcion' => '',
    ],
    '100012' => [
        'id' => 100012,
        'nombre' => 'Baston cuadrupedo de acero',
        'categoria' => 'Bastones y apoyo',
        'precio' => 31.0,
        'precio_anterior' => 39.0,
        'stock' => 25,
        'imagen' => 'ortho-cane-02.jpg',
        'descripcion' => '',
    ],
    '100013' => [
        'id' => 100013,
        'nombre' => 'Vendaje de yeso sintetico 7.5 cm',
        'categoria' => 'Vendajes y curación',
        'precio' => 12.0,
        'precio_anterior' => null,
        'stock' => 90,
        'imagen' => 'ortho-cast-01.jpg',
        'descripcion' => '',
    ],
    '100014' => [
        'id' => 100014,
        'nombre' => 'Baston de aluminio ajustable en 5 alturas',
        'categoria' => 'Bastones y apoyo',
        'precio' => 18.0,
        'precio_anterior' => null,
        'stock' => 80,
        'imagen' => 'ortho-cane-03.jpg',
        'descripcion' => '',
    ],
];

// Ids que se muestran en cada bloque, en orden
$TIENDA_OFERTAS_IDS = [100001, 100002, 100006, 100007, 100011, 100012];

// Los destacados se guardan en tm_config para que el panel y la tienda usen
// la misma selección sin duplicar el catálogo en otra tabla.
$TIENDA_DESTACADOS_IDS_DEFAULT = [100002, 100008, 100011, 100012, 100014];
$TIENDA_DESTACADOS_IDS = $TIENDA_DESTACADOS_IDS_DEFAULT;

if (function_exists('config_get')) {
    try {
        $guardados = json_decode((string)config_get('tienda_destacados_ids'), true);
        if (is_array($guardados)) {
            $TIENDA_DESTACADOS_IDS = array_values(array_filter(
                array_map('intval', $guardados),
                fn($id) => isset($TIENDA_PRODUCTOS[(string)$id])
            ));
        }
    } catch (Throwable $e) {
        // Si la configuración todavía no existe, se usan los valores iniciales.
    }
}
