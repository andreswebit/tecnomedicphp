<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — CRUD Novedades/Noticias
// ════════════════════════════════════════════════════════════════

function novedades_listar(): array {
    $r = db()->query("SELECT * FROM tm_novedades ORDER BY orden ASC, creado_en DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function novedad_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_novedades WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function novedad_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_novedades (titulo, contenido, imagen, video_url, categoria, tipo, orden, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('ssssssii',
        $d['titulo'], $d['contenido'], $d['imagen'], $d['video_url'],
        $d['categoria'], $d['tipo'], $d['orden'], $activo
    );
    $st->execute();
    return db()->insert_id;
}

function novedad_editar(int $id, array $d): void {
    $st = db()->prepare(
        "UPDATE tm_novedades SET titulo=?, contenido=?, imagen=?, video_url=?,
         categoria=?, tipo=?, orden=?, activo=? WHERE id=?"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('ssssssiii',
        $d['titulo'], $d['contenido'], $d['imagen'], $d['video_url'],
        $d['categoria'], $d['tipo'], $d['orden'], $activo, $id
    );
    $st->execute();
}

function novedad_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_novedades WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}

function novedad_subir_imagen(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) return '';
    $permitidas = ['jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png', 'webp' => 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($permitidas[$ext])) return '';
    if ($file['size'] > 5 * 1024 * 1024) return '';
    $carpeta = __DIR__ . '/../storage/novedades';
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
    $nombre = 'novedad_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $carpeta . '/' . $nombre)) {
        return 'storage/novedades/' . $nombre;
    }
    return '';
}