<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — CRUD Novedades (carrusel) y Noticias (artículos)
//
// Tabla: tm_novedades
//   tipo 'tarjeta' → slide del carrusel   (index.php, section id="novedades")
//   tipo 'noticia' → card de artículos    (index.php, section id="articulos")
//
// Campos del formulario → columnas:
//   ncar_title  → titulo
//   ncar_tag    → categoria
//   ncar_expert → contenido (JSON) clave "ncar_expert"  (extracto)
//   ncar_list   → contenido (JSON) clave "ncar_list"    (viñetas, array)
//   descripcion → contenido (JSON) clave "descripcion"  (solo registros viejos)
// ════════════════════════════════════════════════════════════════

const NOVEDAD_MAX_IMAGEN = 5 * 1024 * 1024;    // 5 MB
const NOVEDAD_MAX_VIDEO  = 80 * 1024 * 1024;   // 80 MB

// ── Normalización ───────────────────────────────────────────────

/** 'novedad' (valor histórico) se trata como 'tarjeta'. */
function novedad_normalizar_tipo(?string $tipo): string {
    return ((string)$tipo === 'noticia') ? 'noticia' : 'tarjeta';
}

/** Texto multilínea (o array) → array de viñetas limpias. */
function novedad_lista_a_array($lista): array {
    if (is_array($lista)) {
        $lineas = $lista;
    } else {
        $lineas = preg_split('/\R/u', (string)$lista) ?: [];
    }
    $out = [];
    foreach ($lineas as $l) {
        $l = trim((string)$l);
        $l = trim(preg_replace('/^[\x{2022}\x{25CF}\-\*]+\s*/u', '', $l) ?? $l);
        if ($l !== '') $out[] = $l;
    }
    return $out;
}

/** Arma el JSON que se guarda en la columna contenido. */
function novedad_payload_encode(array $d): string {
    $payload = [
        'ncar_expert' => trim((string)($d['ncar_expert'] ?? '')),
        'ncar_list'   => novedad_lista_a_array($d['ncar_list'] ?? []),
    ];
    $descripcion = trim((string)($d['descripcion'] ?? ''));
    if ($descripcion !== '') $payload['descripcion'] = $descripcion;

    return json_encode($payload, JSON_UNESCAPED_UNICODE);
}

/**
 * Lee la columna contenido. Siempre devuelve las tres claves.
 * Si el contenido es texto plano (registros viejos) lo deja en descripcion.
 */
function novedad_payload_decode(string $contenido): array {
    $base = ['ncar_expert' => '', 'descripcion' => '', 'ncar_list' => []];
    $contenido = trim($contenido);
    if ($contenido === '') return $base;

    $json = json_decode($contenido, true);
    if (!is_array($json)) {
        $base['descripcion'] = $contenido;
        return $base;
    }

    $base['ncar_expert'] = trim((string)($json['ncar_expert'] ?? ''));
    $base['descripcion'] = trim((string)($json['descripcion'] ?? ''));
    $base['ncar_list']   = novedad_lista_a_array($json['ncar_list'] ?? []);
    return $base;
}

/** Texto de extracto para mostrar: ncar_expert, o descripcion si es un registro viejo. */
function novedad_extracto(array $payload): string {
    $t = (string)($payload['ncar_expert'] ?? '');
    return $t !== '' ? $t : (string)($payload['descripcion'] ?? '');
}

/**
 * Convierte los datos del formulario en la fila de tm_novedades.
 * También acepta las claves ya mapeadas (titulo, categoria, contenido).
 */
function novedad_fila(array $d): array {
    $titulo = trim((string)($d['ncar_title'] ?? $d['titulo'] ?? ''));
    $cat    = trim((string)($d['ncar_tag'] ?? $d['categoria'] ?? ''));

    if (isset($d['contenido']) && !isset($d['ncar_expert']) && !isset($d['ncar_list'])) {
        $contenido = (string)$d['contenido'];
    } else {
        $contenido = novedad_payload_encode($d);
    }

    return [
        'titulo'    => $titulo,
        'contenido' => $contenido,
        'imagen'    => (string)($d['imagen'] ?? ''),
        'video_url' => (string)($d['video_url'] ?? ''),
        'categoria' => $cat,
        'tipo'      => novedad_normalizar_tipo($d['tipo'] ?? ''),
        'orden'     => (int)($d['orden'] ?? 0),
        'activo'    => !empty($d['activo']) ? 1 : 0,
    ];
}

// ── Consultas ───────────────────────────────────────────────────

/**
 * Lista publicaciones. Sin argumentos devuelve todas (uso del panel admin).
 * $tipo: 'tarjeta' | 'noticia' | null.  $soloActivos: solo visibles en el sitio.
 */
function novedades_listar(?string $tipo = null, bool $soloActivos = false): array {
    $sql = "SELECT * FROM tm_novedades";
    $where = [];
    $types = '';
    $vals = [];

    if ($tipo !== null) {
        $where[] = "tipo = ?";
        $types .= 's';
        $vals[] = novedad_normalizar_tipo($tipo);
    }
    if ($soloActivos) {
        $where[] = "activo = 1";
    }
    if ($where) $sql .= " WHERE " . implode(' AND ', $where);
    $sql .= " ORDER BY orden ASC, creado_en DESC, id DESC";

    if (!$vals) {
        $r = db()->query($sql);
        return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
    }

    $st = db()->prepare($sql);
    $st->bind_param($types, ...$vals);
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}

function novedad_get(int $id): ?array {
    $st = db()->prepare("SELECT * FROM tm_novedades WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
    return $st->get_result()->fetch_assoc() ?: null;
}

function novedad_crear(array $d): int {
    $f = novedad_fila($d);
    $st = db()->prepare(
        "INSERT INTO tm_novedades (titulo, contenido, imagen, video_url, categoria, tipo, orden, activo)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $st->bind_param('ssssssii',
        $f['titulo'], $f['contenido'], $f['imagen'], $f['video_url'],
        $f['categoria'], $f['tipo'], $f['orden'], $f['activo']
    );
    $st->execute();
    return db()->insert_id;
}

function novedad_editar(int $id, array $d): void {
    $f = novedad_fila($d);
    $st = db()->prepare(
        "UPDATE tm_novedades SET titulo=?, contenido=?, imagen=?, video_url=?,
         categoria=?, tipo=?, orden=?, activo=? WHERE id=?"
    );
    $st->bind_param('ssssssiii',
        $f['titulo'], $f['contenido'], $f['imagen'], $f['video_url'],
        $f['categoria'], $f['tipo'], $f['orden'], $f['activo'], $id
    );
    $st->execute();
}

function novedad_eliminar(int $id): void {
    $st = db()->prepare("DELETE FROM tm_novedades WHERE id = ?");
    $st->bind_param('i', $id);
    $st->execute();
}

// ── Archivos ────────────────────────────────────────────────────

/** Mensaje legible para un error de $_FILES (vacío si no hubo error). */
function novedad_upload_error(array $file): string {
    switch ((int)($file['error'] ?? UPLOAD_ERR_NO_FILE)) {
        case UPLOAD_ERR_OK:
            return '';
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo supera el tamaño máximo que permite el servidor.';
        case UPLOAD_ERR_PARTIAL:
            return 'La subida se cortó. Intentá de nuevo.';
        case UPLOAD_ERR_NO_FILE:
            return 'No se recibió ningún archivo.';
        default:
            return 'No se pudo subir el archivo.';
    }
}

function novedad_carpeta(): string {
    $carpeta = __DIR__ . '/../storage/novedades';
    if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);
    return $carpeta;
}

function novedad_subir_imagen(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidas, true)) return '';
    if ($file['size'] > NOVEDAD_MAX_IMAGEN) return '';
    if (@getimagesize((string)$file['tmp_name']) === false) return '';

    $nombre = 'novedad_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], novedad_carpeta() . '/' . $nombre)) {
        return 'storage/novedades/' . $nombre;
    }
    return '';
}

function novedad_subir_video(array $file): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'mp4') return '';
    if ($file['size'] > NOVEDAD_MAX_VIDEO) return '';

    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $fi ? (string)finfo_file($fi, (string)$file['tmp_name']) : '';
        if ($fi) finfo_close($fi);
        if ($mime !== '' && !in_array($mime, ['video/mp4', 'application/octet-stream'], true)) return '';
    }

    $nombre = 'novedad_video_' . uniqid() . '.mp4';
    if (move_uploaded_file($file['tmp_name'], novedad_carpeta() . '/' . $nombre)) {
        return 'storage/novedades/' . $nombre;
    }
    return '';
}

/** Borra un archivo solo si está dentro de storage/novedades. */
function novedad_borrar_archivo(?string $ruta): void {
    $ruta = (string)$ruta;
    if ($ruta === '' || preg_match('#^https?://#i', $ruta)) return;

    $base = realpath(__DIR__ . '/../storage/novedades');
    $real = realpath(__DIR__ . '/../' . $ruta);
    if ($base === false || $real === false) return;
    if (strpos($real, $base . DIRECTORY_SEPARATOR) !== 0) return;
    if (is_file($real)) @unlink($real);
}
