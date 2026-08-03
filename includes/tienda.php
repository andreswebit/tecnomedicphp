<?php
require_once __DIR__ . '/db.php';

function get_categorias(): array {
    $r = db()->query("SELECT * FROM tm_categorias ORDER BY orden ASC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function get_categoria_by_slug(string $slug): ?array {
    $st = db()->prepare("SELECT * FROM tm_categorias WHERE slug=?");
    $st->bind_param('s', $slug);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function get_familias(int $categoria_id): array {
    $st = db()->prepare("SELECT * FROM tm_familias WHERE categoria_id=? ORDER BY orden ASC");
    $st->bind_param('i', $categoria_id);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_productos_por_familia(int $familia_id): array {
    $st = db()->prepare("SELECT * FROM tm_productos WHERE familia_id=? AND activo=1 ORDER BY destacado DESC, nombre ASC");
    $st->bind_param('i', $familia_id);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Devuelve las familias de una categoría, cada una con sus productos ya cargados
function get_familias_con_productos(int $categoria_id): array {
    $familias = get_familias($categoria_id);
    foreach ($familias as &$f) {
        $f['productos'] = get_productos_por_familia((int)$f['id']);
    }
    return $familias;
}

function get_producto(int $id): ?array {
    $st = db()->prepare(
        "SELECT p.*, f.nombre AS familia_nombre, f.categoria_id, c.nombre AS categoria_nombre, c.slug AS categoria_slug
         FROM tm_productos p
         JOIN tm_familias f ON f.id = p.familia_id
         JOIN tm_categorias c ON c.id = f.categoria_id
         WHERE p.id=? AND p.activo=1"
    );
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function modalidad_label(string $m): string {
    return match ($m) {
        'venta'          => 'Venta',
        'alquiler'       => 'Alquiler',
        'venta_alquiler' => 'Venta y Alquiler',
        default          => $m,
    };
}
// Productos marcados como destacado=1, para la vidriera de la Home
function get_productos_destacados(int $limite = 6): array {
    $st = db()->prepare(
        "SELECT p.*, f.nombre AS familia_nombre, c.nombre AS categoria_nombre, c.slug AS categoria_slug
         FROM tm_productos p
         JOIN tm_familias f ON f.id = p.familia_id
         JOIN tm_categorias c ON c.id = f.categoria_id
         WHERE p.destacado = 1 AND p.activo = 1
         ORDER BY p.creado_en DESC
         LIMIT ?"
    );
    $st->bind_param('i', $limite);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}