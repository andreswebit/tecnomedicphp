<?php
require_once __DIR__ . '/../includes/tienda.php';
$base = BASE_URL;

$id = (int)($_GET['id'] ?? 0);
$p  = $id ? get_producto($id) : null;

if (!$p) {
    http_response_code(404);
    die('Producto no encontrado.');
}

$msg_wa = rawurlencode("Hola! Quiero consultar por: {$p['nombre']}");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($p['nombre']) ?> – Tienda TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/home.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/areas.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/tienda.css" />
</head>
<body>

    <nav class="navbar" id="navbar">
        <div class="nb-brand">
            <a href="<?= HOME_URL ?>/"><img class="nb-logo" src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" /></a>
        </div>
        <div class="nb-links">
            <a href="<?= HOME_URL ?>/#areas">Áreas</a>
            <a href="<?= $base ?>/tienda/">Tienda</a>
            <a href="<?= $base ?>/turnos.php">Turnos</a>
            <a href="<?= HOME_URL ?>/#contacto">Contacto</a>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="<?= $base ?>/turnos.php" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;">
                <i class="fa-regular fa-calendar"></i> Reservar Turno
            </a>
            <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" id="mobileClose"><i class="fa-solid fa-xmark"></i></button>
        <a href="<?= $base ?>/tienda/" onclick="closeMobile()">Tienda</a>
        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">Reservar Turno</a>
    </div>

    <div class="area-breadcrumb">
        <div class="container">
            <a href="<?= HOME_URL ?>/">Inicio</a> <span>/</span>
            <a href="<?= $base ?>/tienda/">Tienda</a> <span>/</span>
            <a href="<?= $base ?>/tienda/categoria.php?slug=<?= urlencode($p['categoria_slug']) ?>"><?= htmlspecialchars($p['categoria_nombre']) ?></a> <span>/</span>
            <?= htmlspecialchars($p['nombre']) ?>
        </div>
    </div>

    <section class="area-section" style="padding-top:44px;">
        <div class="container">
            <div class="tienda-prod-detalle">
                <div class="tienda-prod-detalle-img">
                    <?php if (!empty($p['imagen'])): ?>
                        <img src="<?= $base ?>/static/img/productos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                    <?php else: ?>
                        <i class="fa-solid fa-box" style="font-size:64px;color:#cbd5e1;"></i>
                    <?php endif; ?>
                </div>
                <div class="tienda-prod-detalle-info">
                    <span class="tienda-cat-tag"><?= htmlspecialchars($p['familia_nombre']) ?></span>
                    <h1><?= htmlspecialchars($p['nombre']) ?></h1>
                    <span class="tienda-prod-modalidad tienda-prod-modalidad--lg"><?= modalidad_label($p['modalidad']) ?></span>
                    <?php if (!empty($p['descripcion'])): ?>
                    <p class="tienda-prod-detalle-desc"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                    <?php endif; ?>
                    <div class="area-cta-btns" style="justify-content:flex-start;margin-top:24px;">
                        <a href="https://wa.me/5493794775341?text=<?= $msg_wa ?>" target="_blank" class="btn btn-green">
                            <i class="fa-brands fa-whatsapp"></i> Consultar precio
                        </a>
                        <a href="<?= $base ?>/tienda/categoria.php?slug=<?= urlencode($p['categoria_slug']) ?>" class="btn btn-outline">
                            <i class="fa-solid fa-arrow-left"></i> Volver a <?= htmlspecialchars($p['categoria_nombre']) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="ftr-bottom" style="border-top:none;padding-top:0;">
                <span>© <?= date('Y') ?> TECNOMEDIC. Todos los derechos reservados.</span>
            </div>
        </div>
    </footer>

    <script>
        const mMenu = document.getElementById('mobileMenu');
        document.getElementById('hamburger').onclick = () => { mMenu.classList.add('open'); document.body.style.overflow = 'hidden'; };
        document.getElementById('mobileClose').onclick = closeMobile;
        function closeMobile() { mMenu.classList.remove('open'); document.body.style.overflow = ''; }
        mMenu.addEventListener('click', e => { if (e.target === mMenu) closeMobile(); });
    </script>
</body>
</html>
