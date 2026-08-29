<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — CRUD Staff Médico
// ════════════════════════════════════════════════════════════════

function staff_listar(): array {
    $r = db()->query("SELECT * FROM tm_staff ORDER BY orden ASC, creado_en DESC");
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

function staff_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_staff WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function staff_crear(array $d): int {
    $st = db()->prepare(
        "INSERT INTO tm_staff (nombre, apellido, titulo, especialidad, descripcion, foto, instagram, orden, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('sssssssii',
        $d['nombre'], $d['apellido'], $d['titulo'], $d['especialidad'],
        $d['descripcion'], $d['foto'], $d['instagram'], $d['orden'], $activo
    );
    $st->execute();
    return db()->insert_id;
}

function staff_editar(int $id, array $d): void {
    $st = db()->prepare(
        "UPDATE tm_staff SET nombre=?, apellido=?, titulo=?, especialidad=?, descripcion=?,
         foto=?, instagram=?, orden=?, activo=? WHERE id=?"
    );
    $activo = isset($d['activo']) ? 1 : 0;
    $st->bind_param('sssssssiii',
        $d['nombre'], $d['apellido'], $d['titulo'], $d['especialidad'],
        $d['descripcion'], $d['foto'], $d['instagram'], $d['orden'], $activo, $id
    );
    $st->execute();
}

function staff_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_staff WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}

function staff_subir_foto(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) return '';
    $permitidas = ['jpg' => 'jpg', 'jpeg' => 'jpeg', 'png' => 'png', 'webp' => 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!isset($permitidas[$ext])) return '';
    if ($file['size'] > 3 * 1024 * 1024) return '';
    $carpeta = __DIR__ . '/../storage/staff';
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
    $nombre = 'staff_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $carpeta . '/' . $nombre)) {
        return 'storage/staff/' . $nombre;
    }
    return '';
}