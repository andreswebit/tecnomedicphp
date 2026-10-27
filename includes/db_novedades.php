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

/** Mueve el archivo subido a su destino. TM_TEST_MODE solo lo define el script de pruebas. */
function novedad_mover_subido(string $tmp, string $dest): bool {
    if (is_uploaded_file($tmp)) return move_uploaded_file($tmp, $dest);
    if (defined('TM_TEST_MODE') && TM_TEST_MODE) return copy($tmp, $dest);
    return false;
}

// ── Archivos duplicados ─────────────────────────────────────────
// Los archivos se nombran con el SHA-1 de lo que se subió:
//   novedad_<sha1>.<ext>            subido tal cual (ya era 9:16)
//   novedad_<sha1>_<modo>.jpg|mp4   convertido a 9:16 (modo: desenfoque o recorte)
//   novedad_video_<sha1>_sinverificar.mp4   video guardado sin poder medirlo (sin ffmpeg)
// Subir dos veces el mismo archivo da el mismo nombre, así que no se graba otra copia.
// Los archivos viejos (nombre con uniqid) se detectan comparando el contenido.

/** Registra qué archivos ya existían y se reutilizaron ('imagen' / 'video'). Sin argumentos devuelve la lista. */
function novedad_reutilizado(?string $tipo = null): array {
    static $lista = [];
    if ($tipo === '') $lista = [];
    elseif ($tipo !== null && !in_array($tipo, $lista, true)) $lista[] = $tipo;
    return $lista;
}

/** SHA-1 del contenido de un archivo, o '' si no se puede leer. */
function novedad_hash(string $path): string {
    $h = @sha1_file($path);
    return $h === false ? '' : $h;
}

function novedad_archivo_valido(string $path): bool {
    return is_file($path) && filesize($path) > 0;
}

/** Busca en storage/novedades un archivo con el mismo contenido. Devuelve la ruta relativa o ''. */
function novedad_buscar_duplicado(string $path, string $hash = ''): string {
    $size = @filesize($path);
    if ($size === false || $size === 0) return '';
    if ($hash === '') $hash = novedad_hash($path);
    if ($hash === '') return '';

    foreach (glob(novedad_carpeta() . '/novedad_*') ?: [] as $f) {
        if (!is_file($f) || filesize($f) !== $size) continue;
        if (novedad_hash($f) === $hash) return 'storage/novedades/' . basename($f);
    }
    return '';
}

/** Borra temporales de conversiones que quedaron a medias (más de 1 hora). */
function novedad_limpiar_temporales(): void {
    foreach (glob(novedad_carpeta() . '/tmp_*') ?: [] as $f) {
        if (is_file($f) && filemtime($f) < time() - 3600) @unlink($f);
    }
}

/**
 * Cierra una conversión hecha en un archivo temporal: si ya había un archivo con el
 * mismo contenido lo reutiliza; si no, deja el nuevo con su nombre final.
 */
function novedad_cerrar_conversion(string $tmpOut, string $dest, string $rel, string $tipo): string {
    $dup = novedad_buscar_duplicado($tmpOut);
    if ($dup !== '') {
        @unlink($tmpOut);
        novedad_reutilizado($tipo);
        return $dup;
    }
    if (!@rename($tmpOut, $dest)) {
        @unlink($tmpOut);
        tm_media_error('No se pudo guardar el archivo convertido en el servidor.');
        return '';
    }
    tm_media_convertido($tipo);
    return $rel;
}

/**
 * Sube una imagen. Si no es 9:16 la convierte (ver includes/media_vertical.php).
 * Si el mismo archivo ya está en storage/novedades lo reutiliza.
 * Devuelve la ruta relativa, o '' si falló (el motivo queda en tm_media_error()).
 */
function novedad_subir_imagen(array $file): string {
    require_once __DIR__ . '/media_vertical.php';
    tm_media_error('');

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';
    $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidas, true)) {
        tm_media_error('Formato no permitido. Usá JPG, PNG o WEBP.');
        return '';
    }
    if ($file['size'] > NOVEDAD_MAX_IMAGEN) {
        tm_media_error('La imagen supera los 5 MB.');
        return '';
    }

    $tmp = (string)$file['tmp_name'];
    $dim = tm_imagen_dimensiones_visibles($tmp);
    if ($dim === null) {
        tm_media_error('No se pudo leer la imagen.');
        return '';
    }

    $carpeta = novedad_carpeta();
    novedad_limpiar_temporales();
    $hash = novedad_hash($tmp);
    $modo = '_' . TM_MEDIA_MODO;
    $convertir = !tm_ratio_es_9_16((float)$dim[0], (float)$dim[1]);

    // 1) Ya se subió antes este mismo archivo: se reutiliza y no se graba otra copia
    if ($hash !== '') {
        foreach (["novedad_{$hash}.{$ext}", "novedad_{$hash}{$modo}.jpg"] as $cand) {
            if (novedad_archivo_valido($carpeta . '/' . $cand)) {
                novedad_reutilizado('imagen');
                return 'storage/novedades/' . $cand;
            }
        }
    }

    // 2) Ya es 9:16: se guarda tal cual (o se reutiliza un archivo viejo idéntico)
    if (!$convertir) {
        $dup = novedad_buscar_duplicado($tmp, $hash);
        if ($dup !== '') {
            novedad_reutilizado('imagen');
            return $dup;
        }
        $nombre = $hash !== '' ? "novedad_{$hash}.{$ext}" : 'novedad_' . uniqid() . '.' . $ext;
        if (novedad_mover_subido($tmp, $carpeta . '/' . $nombre)) {
            return 'storage/novedades/' . $nombre;
        }
        tm_media_error('No se pudo guardar la imagen en el servidor.');
        return '';
    }

    // 3) No es 9:16: se convierte (siempre sale como JPG)
    $nombre = $hash !== '' ? "novedad_{$hash}{$modo}.jpg" : 'novedad_' . uniqid() . '.jpg';
    $tmpOut = $carpeta . '/tmp_' . uniqid() . '.jpg';
    if (!tm_imagen_a_vertical($tmp, $tmpOut)) {
        if (is_file($tmpOut)) @unlink($tmpOut);
        return '';
    }
    return novedad_cerrar_conversion($tmpOut, $carpeta . '/' . $nombre, 'storage/novedades/' . $nombre, 'imagen');
}

/**
 * Sube un video MP4. Si no es 9:16 y el servidor tiene ffmpeg, lo convierte.
 * Sin ffmpeg no se puede medir ni convertir: se guarda tal cual (el panel avisa
 * al usuario antes de subir). Si el mismo archivo ya está en storage/novedades lo reutiliza.
 */
function novedad_subir_video(array $file): string {
    require_once __DIR__ . '/media_vertical.php';
    tm_media_error('');

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'mp4') {
        tm_media_error('El video debe ser MP4.');
        return '';
    }
    if ($file['size'] > NOVEDAD_MAX_VIDEO) {
        tm_media_error('El video supera los 80 MB.');
        return '';
    }

    $tmp = (string)$file['tmp_name'];
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $fi ? (string)finfo_file($fi, $tmp) : '';
        if ($fi) finfo_close($fi);
        if ($mime !== '' && !in_array($mime, ['video/mp4', 'application/octet-stream'], true)) {
            tm_media_error('El archivo no es un video MP4 válido.');
            return '';
        }
    }

    $carpeta = novedad_carpeta();
    novedad_limpiar_temporales();
    $hash = novedad_hash($tmp);
    $modo = '_' . TM_MEDIA_MODO;
    $puedeConvertir = tm_video_conversion_disponible();

    // 1) Ya se subió antes este mismo archivo: se reutiliza y no se graba otra copia
    if ($hash !== '') {
        $cands = ["novedad_video_{$hash}.mp4", "novedad_video_{$hash}{$modo}.mp4"];
        // Un video guardado sin verificar solo sirve mientras el servidor siga sin poder verificarlo
        if (!$puedeConvertir) array_unshift($cands, "novedad_video_{$hash}_sinverificar.mp4");
        foreach ($cands as $cand) {
            if (novedad_archivo_valido($carpeta . '/' . $cand)) {
                novedad_reutilizado('video');
                return 'storage/novedades/' . $cand;
            }
        }
    }

    // 2) Medir el video (solo si hay ffmpeg)
    $convertir = false;
    if ($puedeConvertir) {
        $dim = tm_video_dimensiones($tmp);
        if ($dim === null) {
            tm_media_error('No se pudo leer el video. Verificá que sea un MP4 válido.');
            return '';
        }
        $convertir = !tm_ratio_es_9_16((float)$dim['w'], (float)$dim['h']);
        if ($convertir && $dim['dur'] > TM_VID_MAX_SEG) {
            tm_media_error('El video dura más de ' . (int)(TM_VID_MAX_SEG / 60) . ' minutos. Para convertirlo a 9:16 debe ser más corto.');
            return '';
        }
    }

    // 3) Ya es 9:16 (o no se puede medir): se guarda tal cual o se reutiliza un archivo idéntico
    if (!$convertir) {
        $dup = novedad_buscar_duplicado($tmp, $hash);
        if ($dup !== '') {
            novedad_reutilizado('video');
            return $dup;
        }
        $sufijo = $puedeConvertir ? '' : '_sinverificar';
        $nombre = $hash !== '' ? "novedad_video_{$hash}{$sufijo}.mp4" : 'novedad_video_' . uniqid() . '.mp4';
        if (novedad_mover_subido($tmp, $carpeta . '/' . $nombre)) {
            return 'storage/novedades/' . $nombre;
        }
        tm_media_error('No se pudo guardar el video en el servidor.');
        return '';
    }

    // 4) No es 9:16: se convierte con ffmpeg
    $nombre = $hash !== '' ? "novedad_video_{$hash}{$modo}.mp4" : 'novedad_video_' . uniqid() . '.mp4';
    $tmpOut = $carpeta . '/tmp_' . uniqid() . '.mp4';
    if (!tm_video_a_vertical($tmp, $tmpOut)) {
        if (is_file($tmpOut)) @unlink($tmpOut);
        return '';
    }
    return novedad_cerrar_conversion($tmpOut, $carpeta . '/' . $nombre, 'storage/novedades/' . $nombre, 'video');
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

/** ¿Alguna publicación usa este archivo? Ante un error de base se asume que sí (no se borra). */
function novedad_archivo_en_uso(string $ruta): bool {
    if ($ruta === '') return false;
    try {
        $st = db()->prepare("SELECT COUNT(*) AS n FROM tm_novedades WHERE imagen = ? OR video_url = ?");
        $st->bind_param('ss', $ruta, $ruta);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();
        return (int)($r['n'] ?? 0) > 0;
    } catch (Throwable $e) {
        return true;
    }
}

/**
 * Borra un archivo solo si ninguna publicación lo usa. Como un mismo archivo puede
 * estar compartido por varias publicaciones, esta es la función que hay que usar
 * al eliminar o reemplazar medios (después de actualizar la base).
 */
function novedad_liberar_archivo(?string $ruta): void {
    $ruta = (string)$ruta;
    if ($ruta === '' || novedad_archivo_en_uso($ruta)) return;
    novedad_borrar_archivo($ruta);
}
