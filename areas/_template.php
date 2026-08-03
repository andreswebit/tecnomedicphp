<?php
// Este archivo NO se accede directo. Cada areas/<slug>.php setea $slug e incluye esto.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/areas_data.php';

$base = BASE_URL;
$a    = get_area($slug);

if (!$a) {
    http_response_code(404);
    die('Área no encontrada.');
}

// ── Botones según acción declarada en areas_data.php ────────────
$whatsapp = 'https://wa.me/5493794775341';
$acciones_map = [
    'turno'         => ['href' => $base . '/turnos.php', 'label' => 'Reservar turno', 'icon' => 'fa-regular fa-calendar-check', 'class' => 'btn-green'],
    'productos'     => ['href' => $base . '/tienda/', 'label' => 'Ver productos', 'icon' => 'fa-solid fa-cart-shopping', 'class' => 'btn-dark'],
    'presupuesto'   => ['href' => $whatsapp . '?text=' . rawurlencode("Hola! Quiero solicitar un presupuesto de {$a['nombre']}"), 'label' => 'Solicitar presupuesto', 'icon' => 'fa-regular fa-file-lines', 'class' => 'btn-outline'],
    'contacto'      => ['href' => $whatsapp . '?text=' . rawurlencode("Hola! Quiero consultar sobre {$a['nombre']}"), 'label' => 'Contactar', 'icon' => 'fa-brands fa-whatsapp', 'class' => 'btn-outline'],
    'whatsapp'      => ['href' => $whatsapp . '?text=' . rawurlencode("Hola! Quiero consultar sobre {$a['nombre']}"), 'label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp', 'class' => 'btn-outline'],
    'consultar'     => ['href' => $whatsapp . '?text=' . rawurlencode("Hola! Quiero consultar sobre {$a['nombre']}"), 'label' => 'Consultar', 'icon' => 'fa-brands fa-whatsapp', 'class' => 'btn-dark'],
    'indicaciones'  => ['href' => '#hacemos', 'label' => 'Conocer indicaciones', 'icon' => 'fa-regular fa-circle-question', 'class' => 'btn-outline'],
    'miportal'      => ['href' => 'https://nutreando.tecnomedic.com.ar', 'label' => 'Mi Portal', 'icon' => 'fa-regular fa-user', 'class' => 'btn-dark'],
    'suplementacion'=> ['href' => $base . '/tienda/categoria.php?slug=nutricion', 'label' => 'Ver suplementación', 'icon' => 'fa-solid fa-cart-shopping', 'class' => 'btn-outline'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($a['nombre']) ?> – TECNOMEDIC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(mb_substr($a['descripcion'], 0, 155)) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/home.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/areas.css" />
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
            <a href="<?= HOME_URL ?>/#equipo">Nosotros</a>
            <a href="<?= HOME_URL ?>/#contacto">Contacto</a>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="https://nutreando.tecnomedic.com.ar" class="btn btn-outline" style="border-radius:10px;padding:11px 20px;">
                <i class="fa-regular fa-user"></i> Mi Portal
            </a>
            <a href="<?= $base ?>/turnos.php" class="btn btn-dark" style="border-radius:10px;padding:11px 20px;">
                <i class="fa-regular fa-calendar"></i> Reservar Turno
            </a>
            <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" id="mobileClose"><i class="fa-solid fa-xmark"></i></button>
        <span class="mobile-close-hint"><span></span>Cerrar</span>
        <a href="<?= HOME_URL ?>/#areas" onclick="closeMobile()">Áreas</a>
        <a href="<?= $base ?>/tienda/" onclick="closeMobile()">Tienda</a>
        <a href="<?= HOME_URL ?>/#equipo" onclick="closeMobile()">Nosotros</a>
        <a href="<?= HOME_URL ?>/#contacto" onclick="closeMobile()">Contacto</a>
        <a href="https://nutreando.tecnomedic.com.ar" class="btn btn-green" style="font-size:17px;margin-top:8px;">
            <i class="fa-regular fa-user"></i> Mi Portal
        </a>
        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">
            <i class="fa-regular fa-calendar-check"></i> Reservar Turno
        </a>
        <div class="mobile-swipe-hint"><span></span><p>Tocá afuera para cerrar</p></div>
    </div>

    <!-- BREADCRUMB -->
    <div class="area-breadcrumb">
        <div class="container">
            <a href="<?= HOME_URL ?>/">Inicio</a> <span>/</span> <a href="<?= HOME_URL ?>/#areas">Áreas</a> <span>/</span> <?= htmlspecialchars($a['nombre']) ?>
        </div>
    </div>

    <!-- HERO DEL ÁREA -->
    <section class="area-hero">
        <div class="container area-hero-inner">
            <div class="area-hero-icon"><i class="fa-solid <?= $a['icono'] ?>"></i></div>
            <h1><?= htmlspecialchars($a['nombre']) ?></h1>
            <p><?= htmlspecialchars($a['descripcion']) ?></p>
        </div>
    </section>

    <!-- QUÉ HACEMOS -->
    <section class="area-section" id="hacemos">
        <div class="container area-section-grid">
            <div class="area-col">
                <div class="section-label">Qué hacemos</div>
                <ul class="area-list">
                    <?php foreach ($a['hacemos'] as $item): ?>
                    <li><i class="fa-solid fa-check"></i> <?= htmlspecialchars(rtrim($item, '.')) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="area-col">
                <div class="section-label">Qué ofrecemos</div>
                <ul class="area-list">
                    <?php foreach ($a['ofrecemos'] as $item): ?>
                    <li><i class="fa-solid fa-check"></i> <?= htmlspecialchars(rtrim($item, '.')) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!empty($a['nota'])): ?>
                <p class="area-nota"><?= htmlspecialchars($a['nota']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ACCIONES -->
    <section class="area-cta">
        <div class="container area-cta-inner">
            <h2>¿Listo para empezar?</h2>
            <div class="area-cta-btns">
                <?php foreach ($a['acciones'] as $key):
                    $btn = $acciones_map[$key] ?? null;
                    if (!$btn) continue;
                    $target = str_starts_with($btn['href'], 'http') && !str_starts_with($btn['href'], $base) ? ' target="_blank"' : '';
                ?>
                <a href="<?= $btn['href'] ?>" class="btn <?= $btn['class'] ?>"<?= $target ?>>
                    <i class="<?= $btn['icon'] ?>"></i> <?= htmlspecialchars($btn['label']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="ftr-logo-row">
                        <img class="ftr-logo" src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" />
                        <div class="ftr-name">TECNOMEDIC</div>
                    </div>
                    <p class="ftr-desc">Soluciones integrales para el cuidado de tu salud en Corrientes, Argentina.</p>
                    <div class="ftr-social">
                        <a href="https://www.instagram.com/tecnomedicsalud_/" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://wa.me/5493794775341" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>
                <div>
                    <div class="ftr-col-ttl">Áreas</div>
                    <div class="ftr-links">
                        <a href="<?= $base ?>/areas/audiologia.php">Audiología</a>
                        <a href="<?= $base ?>/areas/medicina-hiperbarica.php">Medicina Hiperbárica</a>
                        <a href="<?= $base ?>/areas/nutricion.php">Nutrición</a>
                        <a href="<?= $base ?>/areas/ortopedia-rehabilitacion.php">Ortopedia y Rehabilitación</a>
                        <a href="<?= $base ?>/areas/equipamiento-medico.php">Equipamiento Médico</a>
                    </div>
                </div>
                <div>
                    <div class="ftr-col-ttl">Turnos</div>
                    <div class="ftr-links">
                        <a href="<?= $base ?>/turnos.php">Reservar turno</a>
                        <a href="<?= $base ?>/admin/">Panel admin</a>
                    </div>
                </div>
                <div class="ftr-nl">
                    <div class="ftr-col-ttl">Contacto</div>
                    <div class="ftr-contact">
                        <a href="tel:+5437943490278"><i class="fa-solid fa-phone"></i> (3794) 34-9278</a>
                        <a href="https://wa.me/5493794775341" target="_blank"><i class="fa-brands fa-whatsapp"></i> WhatsApp directo</a>
                        <span><i class="fa-regular fa-clock" style="color:var(--green);"></i> Lun–Vie 8:00–18:00 · Sáb 9:00–13:00</span>
                        <span><i class="fa-solid fa-location-dot" style="color:var(--green);"></i> C. Pellegrini 799, Corrientes</span>
                    </div>
                </div>
            </div>
            <div class="ftr-bottom">
                <span>© <?= date('Y') ?> TECNOMEDIC. Todos los derechos reservados.</span>
                <span>Corrientes, Argentina</span>
            </div>
        </div>
    </footer>

    <script>
        const mMenu = document.getElementById('mobileMenu');
        document.getElementById('hamburger').onclick = () => { mMenu.classList.add('open'); document.body.style.overflow = 'hidden'; };
        document.getElementById('mobileClose').onclick = closeMobile;
        function closeMobile() { mMenu.classList.remove('open'); document.body.style.overflow = ''; }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobile(); });
        mMenu.addEventListener('click', e => { if (e.target === mMenu) closeMobile(); });
        let _swipeY = 0;
        mMenu.addEventListener('touchstart', e => { _swipeY = e.touches[0].clientY; }, { passive: true });
        mMenu.addEventListener('touchend', e => { if (e.changedTouches[0].clientY - _swipeY > 60) closeMobile(); }, { passive: true });
    </script>

</body>
</html>
