<?php
// ════════════════════════════════════════════════════════════════
// TECNOMEDIC — Conversión automática de imágenes y videos a 9:16
//
// Imágenes: se convierten con GD (extensión estándar de PHP).
// Videos:   se convierten con ffmpeg, SOLO si el servidor lo tiene y permite
//           ejecutarlo (en hostings compartidos suele no estar disponible).
//
// Configuración opcional en .env:
//   MEDIA_VERTICAL_MODO=desenfoque   (por defecto) la imagen completa queda
//                                    centrada sobre un fondo desenfocado
//   MEDIA_VERTICAL_MODO=recorte      recorta al centro y llena todo el cuadro
//   FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe   ruta de ffmpeg si no está en el PATH
// ════════════════════════════════════════════════════════════════

if (!defined('TM_MEDIA_MODO')) {
    define('TM_MEDIA_MODO', getenv('MEDIA_VERTICAL_MODO') === 'recorte' ? 'recorte' : 'desenfoque');
}

const TM_IMG_VERT_W      = 1080;
const TM_IMG_VERT_H      = 1920;
const TM_IMG_CALIDAD     = 88;          // calidad JPEG de salida
const TM_IMG_MAX_PIXELES = 40000000;    // 40 megapíxeles
const TM_VID_VERT_W      = 720;
const TM_VID_VERT_H      = 1280;
const TM_VID_MAX_SEG     = 120;         // duración máxima para convertir un video
const TM_VID_TIMEOUT     = 600;         // segundos máximos de ffmpeg

// ── Estado compartido (mensaje de error y avisos de conversión) ──

/** Último error de procesamiento. Sin argumentos lo lee, con texto lo guarda. */
function tm_media_error(?string $set = null): string {
    static $msg = '';
    if ($set !== null) $msg = $set;
    return $msg;
}

/** Registra qué se convirtió ('imagen' / 'video'). Sin argumentos devuelve la lista. */
function tm_media_convertido(?string $tipo = null): array {
    static $lista = [];
    if ($tipo === '') $lista = [];
    elseif ($tipo !== null && !in_array($tipo, $lista, true)) $lista[] = $tipo;
    return $lista;
}

function tm_ratio_es_9_16(float $w, float $h, float $tol = 0.03): bool {
    if ($w <= 0 || $h <= 0) return false;
    $target = 9.0 / 16.0;
    return abs(($w / $h) - $target) <= ($target * $tol);
}

// ── Ejecución de procesos (sin shell) ───────────────────────────

/** Ejecuta un comando como array de argumentos. Devuelve [código, stdout, stderr]. */
function tm_run(array $cmd, int $timeout = 60): array {
    if (!function_exists('proc_open')) return [-1, '', 'proc_open no disponible'];

    $desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $p = @proc_open($cmd, $desc, $pipes);
    if (!is_resource($p)) return [-1, '', 'no se pudo iniciar el proceso'];

    fclose($pipes[0]);
    $windows = (PHP_OS_FAMILY === 'Windows');
    if (!$windows) {
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
    }

    $out = '';
    $err = '';
    $exit = -1;
    $t0 = time();

    if ($windows) {
        // En Windows las pipes no son no bloqueantes: se lee hasta que termine.
        $out = (string)stream_get_contents($pipes[1]);
        $err = (string)stream_get_contents($pipes[2]);
    } else {
        while (true) {
            $out .= (string)stream_get_contents($pipes[1]);
            $err .= (string)stream_get_contents($pipes[2]);
            $st = proc_get_status($p);
            if (!$st['running']) {
                $exit = (int)$st['exitcode'];
                $out .= (string)stream_get_contents($pipes[1]);
                $err .= (string)stream_get_contents($pipes[2]);
                break;
            }
            if (time() - $t0 > $timeout) {
                proc_terminate($p, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($p);
                return [-2, $out, 'tiempo de espera agotado'];
            }
            usleep(100000);
        }
    }

    fclose($pipes[1]);
    fclose($pipes[2]);
    $cierre = proc_close($p);
    if ($exit < 0) $exit = $cierre;

    return [$exit, $out, $err];
}

// ── Detección de ffmpeg ─────────────────────────────────────────

/** Ruta/nombre de un ffmpeg utilizable (con libx264), o '' si no hay. */
function tm_ffmpeg_bin(): string {
    static $bin = null;
    if ($bin !== null) return $bin;
    $bin = '';

    if (!function_exists('proc_open')) return $bin;

    $cands = [];
    $env = getenv('FFMPEG_PATH');
    if ($env) $cands[] = $env;
    $cands[] = 'ffmpeg';
    if (PHP_OS_FAMILY === 'Windows') {
        $cands[] = 'C:\\ffmpeg\\bin\\ffmpeg.exe';
        $cands[] = 'C:\\xampp\\ffmpeg\\bin\\ffmpeg.exe';
    } else {
        $cands[] = '/usr/bin/ffmpeg';
        $cands[] = '/usr/local/bin/ffmpeg';
        $cands[] = '/opt/homebrew/bin/ffmpeg';
    }

    foreach ($cands as $c) {
        [$code] = tm_run([$c, '-hide_banner', '-version'], 5);
        if ($code !== 0) continue;
        [$code2, $out] = tm_run([$c, '-hide_banner', '-encoders'], 10);
        if ($code2 === 0 && strpos($out, 'libx264') !== false) {
            $bin = $c;
            break;
        }
    }
    return $bin;
}

function tm_video_conversion_disponible(): bool {
    return tm_ffmpeg_bin() !== '';
}

/** Ruta de ffprobe junto a ffmpeg, o '' si no se encuentra. */
function tm_ffprobe_bin(): string {
    static $bin = null;
    if ($bin !== null) return $bin;
    $bin = '';

    $ff = tm_ffmpeg_bin();
    if ($ff === '') return $bin;

    $cands = [];
    if (strpos($ff, '/') !== false || strpos($ff, '\\') !== false) {
        $ext = (PHP_OS_FAMILY === 'Windows') ? '.exe' : '';
        $cands[] = dirname($ff) . DIRECTORY_SEPARATOR . 'ffprobe' . $ext;
    }
    $cands[] = 'ffprobe';

    foreach ($cands as $c) {
        [$code] = tm_run([$c, '-hide_banner', '-version'], 5);
        if ($code === 0) { $bin = $c; break; }
    }
    return $bin;
}

// ── Dimensiones de video ────────────────────────────────────────

/** Interpreta la salida de "ffmpeg -i archivo" (respaldo cuando no hay ffprobe). */
function tm_parse_ffmpeg_info(string $texto): ?array {
    if (!preg_match('/Video:.*?,\s*(\d{2,5})x(\d{2,5})[\s,\[]/s', $texto, $m)) return null;
    $w = (int)$m[1];
    $h = (int)$m[2];

    $rot = 0;
    if (preg_match('/rotation of\s*(-?\d+(?:\.\d+)?)\s*degrees/i', $texto, $r)) $rot = (int)round((float)$r[1]);
    elseif (preg_match('/\brotate\s*:\s*(-?\d+)/i', $texto, $r)) $rot = (int)$r[1];
    if (abs($rot) % 180 === 90) [$w, $h] = [$h, $w];

    $dur = 0.0;
    if (preg_match('/Duration:\s*(\d+):(\d+):(\d+(?:\.\d+)?)/', $texto, $d)) {
        $dur = ((int)$d[1]) * 3600 + ((int)$d[2]) * 60 + (float)$d[3];
    }
    return ['w' => $w, 'h' => $h, 'dur' => $dur];
}

/**
 * Dimensiones visibles (con la rotación aplicada) y duración de un video.
 * Devuelve null si no hay ffmpeg o no se pudo leer.
 */
function tm_video_dimensiones(string $path): ?array {
    $ff = tm_ffmpeg_bin();
    if ($ff === '') return null;

    $fp = tm_ffprobe_bin();
    if ($fp !== '') {
        [$code, $out] = tm_run([
            $fp, '-v', 'error', '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height:stream_side_data=rotation:stream_tags=rotate:format=duration',
            '-of', 'json', $path,
        ], 20);
        if ($code === 0) {
            $j = json_decode($out, true);
            $s = $j['streams'][0] ?? null;
            if (is_array($s) && !empty($s['width']) && !empty($s['height'])) {
                $w = (int)$s['width'];
                $h = (int)$s['height'];
                $rot = 0;
                foreach (($s['side_data_list'] ?? []) as $sd) {
                    if (isset($sd['rotation'])) $rot = (int)round((float)$sd['rotation']);
                }
                if ($rot === 0 && isset($s['tags']['rotate'])) $rot = (int)$s['tags']['rotate'];
                if (abs($rot) % 180 === 90) [$w, $h] = [$h, $w];
                return ['w' => $w, 'h' => $h, 'dur' => (float)($j['format']['duration'] ?? 0)];
            }
        }
    }

    // Respaldo: ffmpeg -i imprime los datos en stderr y termina con error (es normal).
    [, , $err] = tm_run([$ff, '-hide_banner', '-nostdin', '-i', $path], 20);
    return tm_parse_ffmpeg_info($err);
}

// ── Conversión de video ─────────────────────────────────────────

/** Convierte un video a 9:16 (MP4/H.264). Devuelve true si se generó $dest. */
function tm_video_a_vertical(string $src, string $dest): bool {
    $ff = tm_ffmpeg_bin();
    if ($ff === '') {
        tm_media_error('Este servidor no puede convertir videos.');
        return false;
    }

    $W = TM_VID_VERT_W;
    $H = TM_VID_VERT_H;

    if (TM_MEDIA_MODO === 'recorte') {
        $filtro = "[0:v:0]scale={$W}:{$H}:force_original_aspect_ratio=increase,crop={$W}:{$H},setsar=1,format=yuv420p[v]";
    } else {
        $bw = (int)($W / 4);
        $bh = (int)($H / 4);
        $filtro = "[0:v:0]split=2[bg][fg];"
            . "[bg]scale={$bw}:{$bh}:force_original_aspect_ratio=increase,crop={$bw}:{$bh},boxblur=8:2,scale={$W}:{$H},setsar=1[b];"
            . "[fg]scale={$W}:{$H}:force_original_aspect_ratio=decrease[f];"
            . "[b][f]overlay=(W-w)/2:(H-h)/2,format=yuv420p[v]";
    }

    if (function_exists('set_time_limit')) @set_time_limit(TM_VID_TIMEOUT + 60);
    if (function_exists('ignore_user_abort')) @ignore_user_abort(true);

    [$code, , $err] = tm_run([
        $ff, '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
        '-i', $src,
        '-filter_complex', $filtro,
        '-map', '[v]', '-map', '0:a?',
        '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '26',
        '-c:a', 'aac', '-b:a', '128k',
        '-movflags', '+faststart',
        $dest,
    ], TM_VID_TIMEOUT);

    if ($code !== 0 || !is_file($dest) || filesize($dest) === 0) {
        if (is_file($dest)) @unlink($dest);
        tm_media_error($code === -2
            ? 'La conversión del video tardó demasiado. Probá con un video más corto.'
            : 'No se pudo convertir el video a 9:16.');
        return false;
    }
    return true;
}

// ── Imágenes (GD) ───────────────────────────────────────────────

function tm_exif_orientacion(string $path): int {
    if (!function_exists('exif_read_data')) return 1;
    $ex = @exif_read_data($path, 'IFD0');
    $o = is_array($ex) ? (int)($ex['Orientation'] ?? 1) : 1;
    return ($o >= 1 && $o <= 8) ? $o : 1;
}

/** Dimensiones tal como se ven (aplicando la orientación EXIF de los JPEG). */
function tm_imagen_dimensiones_visibles(string $path): ?array {
    $info = @getimagesize($path);
    if (!$info || empty($info[0]) || empty($info[1])) return null;
    $w = (int)$info[0];
    $h = (int)$info[1];
    if (($info[2] ?? 0) === IMAGETYPE_JPEG) {
        $o = tm_exif_orientacion($path);
        if ($o >= 5 && $o <= 8) [$w, $h] = [$h, $w];
    }
    return [$w, $h];
}

function tm_ini_bytes(string $v): int {
    $v = trim($v);
    if ($v === '' || $v === '-1') return -1;
    $n = (int)$v;
    switch (strtolower(substr($v, -1))) {
        case 'g': $n *= 1024;
        // no break
        case 'm': $n *= 1024;
        // no break
        case 'k': $n *= 1024;
    }
    return $n;
}

/** Intenta asegurar memoria para procesar una imagen grande. */
function tm_asegurar_memoria(int $necesario): bool {
    if (!function_exists('ini_get')) return true;
    $lim = tm_ini_bytes((string)ini_get('memory_limit'));
    if ($lim < 0) return true;
    $uso = memory_get_usage(true);
    if ($lim - $uso >= $necesario) return true;

    // Hay hostings que desactivan ini_set: en ese caso no se puede ampliar.
    if (!function_exists('ini_set')) return false;
    @ini_set('memory_limit', (string)(int)(($uso + $necesario) * 1.15));
    $lim = tm_ini_bytes((string)ini_get('memory_limit'));
    return $lim < 0 || ($lim - $uso >= $necesario);
}

/** Aplica la orientación EXIF y devuelve la imagen resultante. */
function tm_aplicar_orientacion($img, int $o) {
    if (!function_exists('imagerotate') || !function_exists('imageflip')) return $img;
    $nuevo = $img;
    switch ($o) {
        case 2: imageflip($img, IMG_FLIP_HORIZONTAL); return $img;
        case 3: $nuevo = imagerotate($img, 180, 0); break;
        case 4: imageflip($img, IMG_FLIP_VERTICAL); return $img;
        case 5: imageflip($img, IMG_FLIP_HORIZONTAL); $nuevo = imagerotate($img, 90, 0); break;
        case 6: $nuevo = imagerotate($img, -90, 0); break;
        case 7: imageflip($img, IMG_FLIP_HORIZONTAL); $nuevo = imagerotate($img, -90, 0); break;
        case 8: $nuevo = imagerotate($img, 90, 0); break;
        default: return $img;
    }
    if ($nuevo === false) return $img;
    imagedestroy($img);
    return $nuevo;
}

/** Copia $src sobre todo $dst recortando al centro (cover). */
function tm_gd_cover($dst, $src, int $dw, int $dh): void {
    $sw = imagesx($src);
    $sh = imagesy($src);
    $esc = max($dw / $sw, $dh / $sh);
    $cw = max(1, (int)round($dw / $esc));
    $ch = max(1, (int)round($dh / $esc));
    $cx = (int)(($sw - $cw) / 2);
    $cy = (int)(($sh - $ch) / 2);
    imagecopyresampled($dst, $src, 0, 0, $cx, $cy, $dw, $dh, $cw, $ch);
}

/** Convierte una imagen a 9:16 y la guarda como JPEG en $dest. */
function tm_imagen_a_vertical(string $src, string $dest): bool {
    if (!function_exists('imagecreatetruecolor')) {
        tm_media_error('El servidor no tiene la extensión GD para convertir imágenes.');
        return false;
    }

    $info = @getimagesize($src);
    if (!$info) {
        tm_media_error('No se pudo leer la imagen.');
        return false;
    }
    [$w0, $h0, $tipo] = $info;

    if ($w0 * $h0 > TM_IMG_MAX_PIXELES) {
        tm_media_error('La imagen es demasiado grande (máximo 40 megapíxeles).');
        return false;
    }
    $W = TM_IMG_VERT_W;
    $H = TM_IMG_VERT_H;
    if (!tm_asegurar_memoria((int)(($w0 * $h0 * 2 + $W * $H * 2) * 4 + 8 * 1024 * 1024))) {
        tm_media_error('La imagen es demasiado grande para procesarla en este servidor. Reducila y volvé a subirla.');
        return false;
    }

    $img = false;
    if ($tipo === IMAGETYPE_JPEG) {
        $img = @imagecreatefromjpeg($src);
    } elseif ($tipo === IMAGETYPE_PNG) {
        $img = @imagecreatefrompng($src);
    } elseif ($tipo === IMAGETYPE_WEBP) {
        if (!function_exists('imagecreatefromwebp')) {
            tm_media_error('Este servidor no puede procesar imágenes WEBP. Subí un JPG o PNG.');
            return false;
        }
        $img = @imagecreatefromwebp($src);
    }
    if (!$img) {
        tm_media_error('No se pudo leer la imagen.');
        return false;
    }

    // PNG/WEBP con transparencia: se aplanan sobre blanco
    if ($tipo !== IMAGETYPE_JPEG) {
        $flat = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagealphablending($flat, true);
        imagecopy($flat, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagedestroy($img);
        $img = $flat;
    } else {
        $img = tm_aplicar_orientacion($img, tm_exif_orientacion($src));
    }

    $sw = imagesx($img);
    $sh = imagesy($img);
    $out = imagecreatetruecolor($W, $H);

    if (TM_MEDIA_MODO === 'recorte') {
        tm_gd_cover($out, $img, $W, $H);
    } else {
        // Fondo: la misma imagen recortada, reducida, desenfocada y ampliada
        $bw = (int)round($W / 10);
        $bh = (int)round($H / 10);
        $bg = imagecreatetruecolor($bw, $bh);
        tm_gd_cover($bg, $img, $bw, $bh);
        if (function_exists('imagefilter')) {
            for ($i = 0; $i < 4; $i++) imagefilter($bg, IMG_FILTER_GAUSSIAN_BLUR);
            imagefilter($bg, IMG_FILTER_BRIGHTNESS, -25);
        }
        imagecopyresampled($out, $bg, 0, 0, 0, 0, $W, $H, $bw, $bh);
        imagedestroy($bg);

        // Primer plano: imagen completa, centrada, sin recortar
        $esc = min($W / $sw, $H / $sh);
        $dw = max(1, (int)round($sw * $esc));
        $dh = max(1, (int)round($sh * $esc));
        imagecopyresampled($out, $img, (int)(($W - $dw) / 2), (int)(($H - $dh) / 2), 0, 0, $dw, $dh, $sw, $sh);
    }
    imagedestroy($img);

    imageinterlace($out, true);
    $ok = @imagejpeg($out, $dest, TM_IMG_CALIDAD);
    imagedestroy($out);

    if (!$ok) {
        tm_media_error('No se pudo guardar la imagen convertida.');
        return false;
    }
    return true;
}
