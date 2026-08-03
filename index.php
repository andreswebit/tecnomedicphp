<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/tienda.php';
$categorias_tienda = get_categorias();
$base = BASE_URL; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="<?= b('/static/img/favicon.ico') ?>" type="image/x-icon" />
    <title>TECNOMEDIC | Centro Médico Hiperbárico – Corrientes</title>
    <meta name="description"
        content="Centro especializado en oxigenoterapia hiperbárica, tratamiento de heridas complejas, nutrición deportiva y productos ortopédicos en Corrientes, Argentina." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700;800;900&family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/home.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/areas.css" />
    <link rel="stylesheet" href="<?= $base ?>/static/tienda.css" />
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="nb-brand">
            <img class="nb-logo" src="<?= $base ?>/static/img/logotec.png" alt="TECNOMEDIC"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';" />
        </div>
        <div class="nb-links">
            <a href="#areas">Áreas</a>
            <a href="<?= $base ?>/tienda/">Tienda</a>
            <!-- <a href="<?= $base ?>/turnos.php">Turnos</a> -->
            <a href="#equipo">Nosotros</a>
            <a href="#contacto">Contacto</a>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="https://nutreando.com/auth/login" class="btn btn-dark"
                style="border-radius:8px;padding:7px 10px;font-size:11px;">
                <i class="fa-regular fa-user"></i> Mi Portal N
            </a>
            <a href="<?= $base ?>/turnos.php" class="btn btn-dark"
                style="border-radius:8px;padding:7px 10px;font-size:11px;">
                <i class="fa-regular fa-calendar"></i> Reservar Turno
            </a>
            <a href="<?= $base ?>/admin/login.php" class="btn btn-dark"
                style="border-radius:8px;padding:7px 10px;font-size:11px;">
                🔒 Acceso Adm
            </a>
            <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" id="mobileClose"><i class="fa-solid fa-xmark"></i></button>
        <span class="mobile-close-hint"><span></span>Cerrar</span>
        <a href="#areas" onclick="closeMobile()">Áreas</a>
        <a href="<?= $base ?>/tienda/" onclick="closeMobile()">Tienda</a>
        <a href="#equipo" onclick="closeMobile()">Nosotros</a>
        <a href="#contacto" onclick="closeMobile()">Contacto</a>
        <a href="https://nutreando.com/auth/login" class="btn btn-green" style="font-size:17px;margin-top:8px;">
            <i class="fa-regular fa-user"></i> Mi Portal
        </a>
        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">
            <i class="fa-regular fa-calendar-check"></i> Reservar Turno
        </a>
        <a href="<?= $base ?>/admin/login.php" class="btn btn-green" style="font-size:17px;margin-top:8px;">
            🔒 Acceso Admin
        </a>
        <div class="mobile-swipe-hint"><span></span>
            <p>Tocá afuera para cerrar</p>
        </div>
    </div>

    <!-- HERO -->
    <section id="inicio">
        <div class="hero-yt-bg">
            <iframe
                src="https://www.youtube.com/embed/IqcZl86vtXU?autoplay=1&mute=1&loop=1&controls=0&playlist=IqcZl86vtXU&modestbranding=1&showinfo=0&rel=0&iv_load_policy=3"
                title="Sesión BioBarica – video de fondo" allow="autoplay; encrypted-media" allowfullscreen
                loading="eager"></iframe>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge"><i class="fa-solid fa-circle-check"></i> Innovación tecnológica aplicada</div>
                <h1 class="hero-title">Experiencia y<br><span class="accent">Compromiso</span><br>al Servicio de la
                    Salud</h1>
                <p class="hero-desc">En TecnoMedic trabajamos para acercar soluciones orientadas a mejorar la calidad de
                    vida de las personas integrando servicios profesionales, equipamiento médico e insumos
                    especializados para la salud.</p>
                <p class="hero-areas-strip">Audiología &nbsp;•&nbsp; Medicina Hiperbárica &nbsp;•&nbsp; Nutrición
                    &nbsp;•&nbsp; Ortopedia y Rehabilitación &nbsp;•&nbsp; Equipamiento Médico y Quirúrgico</p>
                <div class="hero-btns">
                    <a href="#areas" class="btn btn-green"><i class="fa-solid fa-grid-2"></i> Conocer nuestras Áreas</a>
                    <a href="<?= $base ?>/turnos.php" class="btn btn-outline"><i
                            class="fa-regular fa-calendar-check"></i> Reservar Turno</a>
                    <a href="<?= $base ?>/tienda/" class="btn btn-outline"><i class="fa-solid fa-cart-shopping"></i> Ir
                        a la Tienda</a>
                </div>
                <div class="hero-stats">
                    <div>
                        <div class="stat-num">+500</div>
                        <div class="stat-lbl">Pacientes tratados</div>
                    </div>
                    <div>
                        <div class="stat-num">+8</div>
                        <div class="stat-lbl">Años de experiencia</div>
                    </div>
                    <div>
                        <div class="stat-num">Equipos</div>
                        <div class="stat-lbl"><i class="ion-volume-high"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STRIP -->
    <div class="strip" aria-hidden="true">
        <div class="strip-inner">
            <span>Oxigenoterapia Hiperbárica</span><i class="fa-solid fa-diamond"></i>
            <span>Tratamiento Pie Diabético</span><i class="fa-solid fa-diamond"></i>
            <span>Nutrición Deportiva</span><i class="fa-solid fa-diamond"></i>
            <span>Cuidado de Heridas</span><i class="fa-solid fa-diamond"></i>
            <span>Audífonos Digitales</span><i class="fa-solid fa-diamond"></i>
            <span>Sillas de Ruedas</span><i class="fa-solid fa-diamond"></i>
            <span>Revitalair 430</span><i class="fa-solid fa-diamond"></i>
            <span>Oxigenoterapia Hiperbárica</span><i class="fa-solid fa-diamond"></i>
            <span>Tratamiento Pie Diabético</span><i class="fa-solid fa-diamond"></i>
            <span>Nutrición Deportiva</span><i class="fa-solid fa-diamond"></i>
            <span>Cuidado de Heridas</span><i class="fa-solid fa-diamond"></i>
            <span>Audífonos Digitales</span><i class="fa-solid fa-diamond"></i>
            <span>Revitalair 430</span><i class="fa-solid fa-diamond"></i>
        </div>
    </div>

    <!-- NOVEDADES CAROUSEL -->
    <section id="novedades">
        <div class="ncar-progress">
            <div class="ncar-bar" id="ncarBar"></div>
        </div>
        <div class="ncar-wrap">
            <div class="ncar-track" id="ncarTrack">

                <!-- SLIDE 1 -->
                <div class="ncar-slide">
                    <div class="ncar-text">
                        <span class="ncar-tag"><i class="fa-brands fa-facebook"></i>&nbsp; Reel TECNOMEDIC</span>
                        <h2 class="ncar-title">OXIGENOTERAPIA<br>HIPERBÁRICA</h2>
                        <p class="ncar-excerpt">La Cámara Hiperbárica Revitalair 430 es la tecnología más avanzada para
                            el tratamiento integral de heridas, pie diabético y recuperación deportiva.<br><br>Es un
                            tratamiento no invasivo en el que la persona respira oxígeno al 100% dentro de una cámara
                            presurizada.</p>
                        <ul class="ncar-list">
                            <li><i class="fa-solid fa-circle-check"></i> Tecnología FDA aprobada — Revitalair 430</li>
                            <li><i class="fa-solid fa-circle-check"></i> Sesiones de 60 a 90 min, sin dolor</li>
                            <li><i class="fa-solid fa-circle-check"></i> Resultados visibles desde las primeras sesiones
                            </li>
                        </ul>
                        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="width:fit-content;"><i
                                class="fa-regular fa-calendar-check"></i> Contactar</a>
                    </div>
                    <div class="ncar-visual">
                        <video controls autoplay muted loop src="<?= $base ?>/static/video/sede.mp4"
                            title="Cámara Hiperbárica"></video>
                        <div class="ncar-badge">
                            <div class="ncar-badge-logo"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                                    alt="Logo" /></div>
                            <div>
                                <div class="ncar-badge-name">Tecnomedic Salud</div>
                                <div class="ncar-badge-sub">Centro Hiperbárico · Corrientes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2 -->
                <div class="ncar-slide">
                    <div class="ncar-text">
                        <span class="ncar-tag"><i class="fa-solid fa-star-of-life"></i>&nbsp; Tratamiento</span>
                        <h2 class="ncar-title">PIE<br>DIABÉTICO</h2>
                        <p class="ncar-excerpt">Las complicaciones del pie diabético son una de las principales causas
                            de hospitalización en personas con diabetes. En TECNOMEDIC abordamos el tratamiento integral
                            con oxigenoterapia hiperbárica, curación avanzada y seguimiento médico continuo.</p>
                        <ul class="ncar-list">
                            <li><i class="fa-solid fa-circle-check"></i> Evaluación especializada de heridas y escaras
                            </li>
                            <li><i class="fa-solid fa-circle-check"></i> OHB para acelerar la cicatrización</li>
                            <li><i class="fa-solid fa-circle-check"></i> Equipo médico multidisciplinario</li>
                        </ul>
                        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="width:fit-content;"><i
                                class="fa-regular fa-calendar"></i> Contactar</a>
                    </div>
                    <div class="ncar-visual">
                        <video controls autoplay muted loop src="<?= $base ?>/static/video/PieDiabetico.mp4"></video>
                        <div class="ncar-badge">
                            <div class="ncar-badge-logo"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                                    alt="Logo" /></div>
                            <div>
                                <div class="ncar-badge-name">Tecnomedic Salud</div>
                                <div class="ncar-badge-sub">Centro Hiperbárico · Corrientes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3 -->
                <div class="ncar-slide">
                    <div class="ncar-text">
                        <span class="ncar-tag"><i class="fa-brands fa-youtube" style="color:#ff0000;"></i>&nbsp; Video
                            educativo</span>
                        <h2 class="ncar-title">CICATRIZACIÓN<br>DE HERIDAS</h2>
                        <p class="ncar-excerpt">El oxígeno hiperbárico genera beneficios fisiológicos comprobados:
                            estimula la formación de vasos sanguíneos, la síntesis de colágeno y actúa sobre bacterias
                            resistentes.</p>
                        <ul class="ncar-list">
                            <li><i class="fa-solid fa-circle-check"></i> Escaras y úlceras por presión</li>
                            <li><i class="fa-solid fa-circle-check"></i> Heridas post-quirúrgicas y quemaduras</li>
                            <li><i class="fa-solid fa-circle-check"></i> Osteomielitis crónica</li>
                        </ul>
                        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="width:fit-content;"><i
                                class="fa-regular fa-calendar"></i> Contactar</a>
                    </div>
                    <div class="ncar-visual">
                        <video controls autoplay muted loop src="<?= $base ?>/static/video/Heridas.mp4"></video>
                        <div class="ncar-badge">
                            <div class="ncar-badge-logo"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                                    alt="Logo" /></div>
                            <div>
                                <div class="ncar-badge-name">Tecnomedic Salud</div>
                                <div class="ncar-badge-sub">Centro Hiperbárico · Corrientes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 4 -->
                <div class="ncar-slide">
                    <div class="ncar-text">
                        <span class="ncar-tag"><i class="fa-solid fa-dumbbell"></i>&nbsp; Deporte</span>
                        <h2 class="ncar-title">RECUPERACIÓN<br>DEPORTIVA</h2>
                        <p class="ncar-excerpt">La oxigenoterapia hiperbárica es el aliado de los deportistas de alto
                            rendimiento. Reduce la inflamación, acelera la recuperación muscular y permite volver a la
                            actividad física hasta 3 semanas antes.</p>
                        <ul class="ncar-list">
                            <li><i class="fa-solid fa-circle-check"></i> Reducción de inflamación y dolor post-esfuerzo
                            </li>
                            <li><i class="fa-solid fa-circle-check"></i> Recuperación acelerada de desgarros y esguinces
                            </li>
                            <li><i class="fa-solid fa-circle-check"></i> Mejora del rendimiento y energía celular</li>
                        </ul>
                        <a href="https://wa.me/5493794775341" target="_blank" class="btn btn-green"
                            style="width:fit-content;"><i class="fa-brands fa-whatsapp"></i> Consultar</a>
                    </div>
                    <div class="ncar-visual">
                        <img src="https://plus.unsplash.com/premium_photo-1664304770925-6f9a1386d7b9?q=80&w=1073&auto=format&fit=crop"
                            alt="Recuperación deportiva" loading="lazy" />
                        <div class="ncar-badge">
                            <div class="ncar-badge-logo"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                                    alt="Logo" /></div>
                            <div>
                                <div class="ncar-badge-name">Tecnomedic Salud</div>
                                <div class="ncar-badge-sub">Centro Hiperbárico · Corrientes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 5 -->
                <div class="ncar-slide">
                    <div class="ncar-text">
                        <span class="ncar-tag"><i class="fa-brands fa-youtube" style="color:#ff0000;"></i>&nbsp;
                            Video</span>
                        <h2 class="ncar-title">ASÍ ES UNA<br>SESIÓN EN<br>CÁMARA</h2>
                        <p class="ncar-excerpt">Cada sesión dura entre 60 y 90 minutos. El paciente descansa dentro de
                            la cámara respirando oxígeno puro al 100% a presión controlada. Es un tratamiento
                            completamente seguro, no invasivo y sin dolor.</p>
                        <ul class="ncar-list">
                            <li><i class="fa-solid fa-circle-check"></i> Ambiente cómodo y climatizado</li>
                            <li><i class="fa-solid fa-circle-check"></i> Monitoreo continuo del paciente</li>
                            <li><i class="fa-solid fa-circle-check"></i> Puede leer, escuchar música o descansar</li>
                        </ul>
                        <a href="<?= $base ?>/turnos.php" class="btn btn-green" style="width:fit-content;"><i
                                class="fa-regular fa-calendar-check"></i> Empezar tratamiento</a>
                    </div>
                    <div class="ncar-visual">
                        <video src="<?= $base ?>/static/video/sesiones.mp4" controls autoplay muted loop
                            style="min-height:480px;"></video>
                        <div class="ncar-badge">
                            <div class="ncar-badge-logo"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                                    alt="Logo" /></div>
                            <div>
                                <div class="ncar-badge-name">Tecnomedic Salud</div>
                                <div class="ncar-badge-sub">Centro Hiperbárico · Corrientes</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="ncar-controls">
            <button class="ncar-btn" id="ncarPrev"><i class="fa-solid fa-chevron-left"></i></button>
            <div class="ncar-dots" id="ncarDots"></div>
            <div class="ncar-counter" id="ncarCounter">01 / 05</div>
            <button class="ncar-btn" id="ncarNext"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </section>

    <!-- NUESTRAS ÁREAS -->
    <section id="areas">
        <div class="container">
            <div class="section-label">Nuestras Áreas</div>
            <h2
                style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(36px,5vw,54px);color:var(--navy);line-height:1.02;">
                SOLUCIONES INTEGRALES<br>PARA CADA NECESIDAD</h2>
            <div class="areas-grid">

                <div class="area-card">
                    <div class="area-img area-img--placeholder"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                            alt="Audiología"></div>
                    <div class="area-body">
                        <div class="area-title"><i class="fa-solid fa-headphones"></i> Audiología</div>
                        <p class="area-desc">Diagnóstico, tratamiento y rehabilitación de la salud auditiva.</p>
                        <a href="<?= $base ?>/areas/audiologia.php" style=" bottom: 10px;  position: relative ;"
                            class="btn btn-green btn-sm">Conocer más</a>
                    </div>

                </div>

                <div class="area-card">
                    <div class="area-img area-img--placeholder"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                            alt="Medicina Hiperbárica"></div>
                    <div class="area-body">
                        <div class="area-title"><i class="fa-solid fa-lungs"></i> Medicina Hiperbárica</div>
                        <p class="area-desc">Oxigenoterapia hiperbárica con protocolos supervisados por profesionales.
                        </p>
                        <a href="<?= $base ?>/areas/medicina-hiperbarica.php"
                            style=" bottom: 10px; position: relative ;" class="btn btn-green btn-sm">Conocer más</a>
                    </div>
                </div>

                <div class="area-card">
                    <div class="area-img area-img--placeholder"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                            alt="Nutrición"></div>
                    <div class="area-body">
                        <div class="area-title"><i class="fa-solid fa-apple-whole"></i> Nutrición</div>
                        <p class="area-desc">Nutrición clínica, funcional y deportiva con evaluación profesional.</p>
                        <a href="<?= $base ?>/areas/nutricion.php" style=" bottom: 10px; position: relative ;"
                            class="btn btn-green btn-sm">Conocer más</a>
                    </div>
                </div>

                <div class="area-card">
                    <div class="area-img area-img--placeholder"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                            alt="Ortopedia y Rehabilitación"></div>
                    <div class="area-body">
                        <div class="area-title"><i class="fa-solid fa-wheelchair"></i> Ortopedia y Rehabilitación</div>
                        <p class="area-desc">Movilidad, recuperación funcional y soluciones adaptadas a cada necesidad.
                        </p>
                        <a href="<?= $base ?>/areas/ortopedia-rehabilitacion.php"
                            style=" bottom: 10px; position: relative ;" class="btn btn-green btn-sm">Conocer más</a>
                    </div>
                </div>

                <div class="area-card">
                    <div class="area-img area-img--placeholder"><img src="<?= $base ?>/static/img/tecno-logo.jpeg"
                            alt="Equipamiento Médico y Quirúrgico"></div>
                    <div class="area-body">
                        <div class="area-title"><i class="fa-solid fa-kit-medical"></i> Equipamiento Médico y Quirúrgico
                        </div>
                        <p class="area-desc">Equipamiento médico, quirúrgico y respiratorio para uso profesional y
                            domiciliario.</p>
                        <a href="<?= $base ?>/areas/equipamiento-medico.php" style=" bottom: 10px; position: relative ;"
                            class="btn btn-green btn-sm">Conocer más</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CONVENIOS Y BENEFICIOS -->
    <section id="convenios" style="background:#f8fafc;">
        <div class="container">
            <div class="section-label">Convenios y Beneficios</div>
            <h2
                style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,4vw,44px);color:var(--navy);line-height:1.02;">
                TRABAJAMOS CON<br>TU OBRA SOCIAL</h2>
            <p style="text-align:center;color:#64748b;max-width:600px;margin:16px auto 0;font-size:15px;">
                Tenemos convenio con las principales obras sociales y prepagas de la región para que accedas a nuestros
                servicios con mayor cobertura.
            </p>
            <div class="convenios-grid">
                <?php foreach (['PAMI','IOSCOR','OSDE','Swiss Medical','Galeno','Medifé','OSECAC','OSPAT','IOMA'] as $convenio): ?>
                <div class="convenio-badge"><?= htmlspecialchars($convenio) ?></div>
                <?php endforeach; ?>
            </div>
            <p style="text-align:center;color:#94a3b8;font-size:13px;margin-top:20px;">
                ¿No ves tu obra social? <a href="https://wa.me/5493794775341" target="_blank"
                    style="color:var(--teal);font-weight:600;">Consultanos por WhatsApp</a>, la mayoría cuenta con
                reintegro.
            </p>
        </div>
    </section>




    <!-- EQUIPO -->
    <section id="equipo">
        <div class="container">
            <div class="equipo-header">
                <div class="section-label">Nuestro equipo médico</div>
                <h2 class="equipo-title">CONOCÉ A NUESTROS<br>ESPECIALISTAS</h2>
            </div>
            <div class="doctors-grid-wrap">
                <div class="doctors-grid">
                    <div class="doctor-card">
                        <div class="doctor-photo-wrap"><img src="<?= $base ?>/static/img/dra unger.jpg"
                                alt="Dra. Carolina Unger" loading="lazy" />
                        </div>
                        <div class="doctor-name">Dra. Carolina Unger</div>
                        <div class="doctor-specialty">M.P 6637 · Médica Clínica-Diabetóloga</div>
                        <p class="doctor-desc">Especialista en cicatrización de heridas complejas, pie diabético y
                            escaras. Referente en oxigenoterapia hiperbárica y medicina regenerativa en Corrientes.
                            Miembro de AIACH y SAD</p>
                        <button class="doctor-plus" title="Ver más"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div class="doctor-card">
                        <div class="doctor-photo-wrap"><img src="<?= $base ?>/static/img/dra repetto.JPG"
                                alt="Lic. Valentina Repetto" loading="lazy" /></div>
                        <div class="doctor-name">Lic. Valentina Repetto</div>
                        <div class="doctor-specialty">Fonoaudióloga</div>
                        <p class="doctor-desc">Especialista en fonoaudiología, estudios auditivos, selección y
                            acompañamiento en la adaptación a los audífonos digitales.</p>
                        <button class="doctor-plus" title="Ver más"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <div class="doctor-card">
                        <div class="doctor-photo-wrap"><img src="<?= $base ?>/static/img/dra repettoL.JPG"
                                alt="Lic. Luciana Repetto" loading="lazy" /></div>
                        <div class="doctor-name">Lic. Luciana Repetto</div>
                        <div class="doctor-specialty">Licenciada en Nutrición</div>
                        <p class="doctor-desc">Especialista en nutrición deportiva y clínica, planes personalizados de
                            nutrición, suplementación especializada y seguimiento continuo.</p>
                        <button class="doctor-plus" title="Ver más"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div style="text-align:center;margin-top:44px;position:relative;z-index:1;">
                <a href="https://www.instagram.com/tecnomedicsalud_/" target="_blank" class="btn btn-dark"
                    style="display:inline-flex;background:#fff;color:var(--navy);border-radius:12px;padding:14px 32px;font-size:15px;">
                    <i class="fa-brands fa-instagram"></i> Seguinos en Instagram
                </a>
            </div>
        </div>
    </section>

    <!-- TURNOS CTA -->
    <section id="turnos">
        <div class="turnos-bg"></div>
        <div class="container">
            <div class="turnos-inner">
                <h2 class="turnos-title">AGENDA TU CONSULTA<br><span class="accent">HOY</span></h2>
                <p class="turnos-sub">Sistema de turnos online disponible 24/7 · Confirmación inmediata por WhatsApp</p>
                <div class="turnos-card">
                    <a href="<?= $base ?>/turnos.php" class="t-submit"
                        style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;">
                        <i class="fa-regular fa-calendar-check"></i> RESERVAR TURNO AHORA &nbsp;→
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- /////////////////////////////////////////////////////////////////// -->
    <!-- ARTÍCULOS / ÚLTIMAS NOTICIAS -->
    <section id="articulos">
        <div class="container">
            <div class="art-header">
                <div>
                    <div class="section-label">Publicaciones &amp; Novedades</div>
                    <h2
                        style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(28px,4vw,44px);color:var(--navy);">
                        ÚLTIMAS NOTICIAS</h2>
                </div>
                <a href="https://www.instagram.com/tecnomedicsalud_/" target="_blank" class="btn btn-green"
                    style="padding:10px 20px;font-size:14px;">
                    <i class="fa-brands fa-instagram"></i> Ver en Instagram
                </a>
            </div>

            <div class="art-grid">

                <!-- ── DESTACADO: Beneficios Cámara Hiperbárica ── -->
                <div class="art-card ">
                    <div class="art-img">
                        <img src="<?= $base ?>/static/img/notice/tec2.png" alt="Cámara Hiperbárica Revitalair"
                            loading="lazy" />
                    </div>
                    <div class="art-body">
                        <span class="art-cat">Cámara Hiperbárica</span>
                        <h3 class="art-title">Beneficios de la Cámara Hiperbárica</h3>
                        <p class="art-excerpt">
                            Oxigená tu cuerpo y mejorá tu vida. ¿Sabías que una sesión de cámara
                            hiperbárica puede mejorar tu salud y bienestar?<br><br>
                            La medicina hiperbárica está revolucionando la recuperación de lesiones
                            y la oxigenación celular. Sus beneficios incluyen:
                        </p>
                        <!-- Lista de beneficios — fiel al carrusel del PDF -->
                        <ul
                            style="margin:10px 0 16px;padding-left:0;list-style:none;display:flex;flex-direction:column;gap:6px;">
                            <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--g600);">
                                <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
                                Acelera la <strong>cicatrización</strong>
                            </li>
                            <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--g600);">
                                <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
                                Mejora la <strong>circulación sanguínea</strong>
                            </li>
                            <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--g600);">
                                <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
                                Reduce la <strong>inflamación</strong>
                            </li>
                            <li style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--g600);">
                                <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
                                Aumenta la <strong>energía y vitalidad</strong>
                            </li>
                        </ul>
                        <div class="art-foot">
                            <span class="art-date"><i class="fa-regular fa-calendar"></i> 17 Feb 2025</span>
                            <a href="https://wa.me/543794775341?text=Quiero+info+sobre+la+cámara+hiperbárica"
                                target="_blank" class="art-more">
                                Consultar <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── Cicatrices – más oxígeno ── -->
                <div class=" art-card ">
                    <div class="art-img">
                        <img src="<?= $base ?>/static/img/notice/tec1.png" alt="Cicatrización hiperbárica"
                            loading="lazy" />
                    </div>
                    <div class="art-body">
                        <span class="art-cat">Heridas</span>
                        <h3 class="art-title">La herida que no cicatriza necesita más oxígeno</h3>
                        <p class="art-excerpt">
                            Si una herida tarda meses en sanar, es porque necesita oxígeno. Las heridas
                            crónicas son un problema serio en personas con diabetes o problemas
                            circulatorios. La cámara hiperbárica acelera la cicatrización reduciendo
                            infecciones y evitando complicaciones mayores.
                        </p>
                        <div class="art-foot">
                            <span class="art-date"><i class="fa-regular fa-calendar"></i> 20 Feb 2025</span>
                            <a href="https://wa.me/543794775341?text=Consulta+sobre+cicatrización" target="_blank"
                                class="art-more">
                                Consultar <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── Quemaduras graves ── -->
                <div class=" art-card ">
                    <div class="art-img">
                        <img src="<?= $base ?>/static/img/notice/tec7.png" alt="Tratamiento de quemaduras"
                            loading="lazy" />
                    </div>
                    <div class="art-body">
                        <span class="art-cat">Tratamientos</span>
                        <h3 class="art-title">Con más oxígeno, las quemaduras graves sanan mejor</h3>
                        <p class="art-excerpt">
                            Las quemaduras afectan la piel y los tejidos internos, pero la oxigenoterapia
                            hiperbárica acelera su regeneración y previene infecciones. Si sufriste una
                            quemadura grave, consultanos sobre este tratamiento.
                        </p>
                        <div class="art-foot">
                            <span class="art-date"><i class="fa-regular fa-calendar"></i> 22 Feb 2025</span>
                            <a href="https://wa.me/543794775341?text=Consulta+sobre+quemaduras" target="_blank"
                                class="art-more">
                                Consultar <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ── Pérdida de audición ── -->
                <div class="art-card ">
                    <div class="testim-media" id="artMedia1">
                        <!-- Thumbnail: reemplazá src por un frame del video -->
                        <img class="tm-thumb" src="<?= $base ?>/static/img/notice/tec9.png" alt="Audífonos digitales"
                            alt="Video " loading="lazy" />
                        <!-- Video -->
                        <video class="tm-video" id="artVideo1" controls preload="none">
                            <source src="<?= $base ?>/static/video/Escuchar.mp4" type="video/mp4" />
                        </video>
                        <!-- Botón play -->
                        <div class="tm-play-btn" onclick="playTestimVideo('artMedia1','artVideo1')">
                            <div class="tm-play-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="tm-video-badge">
                            <i class="fa-solid fa-video"></i> Video
                        </div>
                    </div>
                    <div class="art-body">
                        <span class="art-cat">Audiología</span>
                        <h3 class="art-title">Si escuchás pero no entendés, podés estar perdiendo audición</h3>
                        <p class="art-excerpt">
                            ¿Sentís que escuchás, pero no entendés bien? Este es un síntoma común de
                            pérdida auditiva y afecta la comunicación en el día a día. Consulta con
                            nuestros especialistas en audífonos digitales.
                        </p>
                        <div class="art-foot">
                            <span class="art-date"><i class="fa-regular fa-calendar"></i> 24 Feb 2025</span>
                            <a href="https://wa.me/543794775341?text=Consulta+sobre+audífonos" target="_blank"
                                class="art-more">
                                Consultar <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>


                    <!--
                  ★ PARA AGREGAR MÁS PUBLICACIONES: copiar un bloque <div class="art-card">
                    y completar: art-cat, art-title, art-excerpt, art-date e img src.
                  ★ Para artículo DESTACADO (ancho doble): agregar clase "feat" al art-card.
                -->


                </div>
    </section>



    <!-- TIENDA VIRTUAL (vidriera de destacados) -->
    <section id="tienda-virtual">
        <div class="container">
            <div class="section-label">Tienda Virtual</div>
            <h2 style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,4vw,44px);color:var(--navy);line-height:1.02;">PRODUCTOS<br>DESTACADOS</h2>
            <div class="shop-grid">
                <?php foreach (get_productos_destacados(6) as $p): ?>
                <a href="<?= $base ?>/tienda/producto.php?id=<?= $p['id'] ?>" class="shop-card">
                    <span class="shop-cat"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
                    <div class="shop-img">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="<?= $base ?>/static/img/productos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <?php else: ?>
                            <i class="fa-solid fa-box"></i>
                        <?php endif; ?>
                    </div>
                    <div class="shop-name"><?= htmlspecialchars($p['nombre']) ?></div>
                    <div class="shop-modalidad"><?= modalidad_label($p['modalidad']) ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- TIENDA (categorías) -->
    <section id="tienda-categorias">
        <div class="container">
            <div class="section-label">Tienda</div>
            <h2
                style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(30px,4vw,44px);color:var(--navy);line-height:1.02;">
                PRODUCTOS PARA<br>CADA NECESIDAD</h2>
            <div class="tienda-cat-grid" style="margin-top:36px;">
                <?php foreach ($categorias_tienda as $c): ?>
                <a href="<?= $base ?>/tienda/categoria.php?slug=<?= urlencode($c['slug']) ?>" class="tienda-cat-card">
                    <div class="tienda-cat-icon"><i class="fa-solid <?= htmlspecialchars($c['icono']) ?>"></i></div>
                    <div class="tienda-cat-nombre"><?= htmlspecialchars($c['nombre']) ?></div>
                    <span class="tienda-cat-link">Ver productos <i class="fa-solid fa-arrow-right"></i></span>
                </a>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:32px;">
                <a href="<?= $base ?>/tienda/" class="btn btn-dark">
                    <i class="fa-solid fa-cart-shopping"></i> Ver tienda completa
                </a>
            </div>
        </div>
    </section>

    <!-- TESTIMONIOS -->
    <section id="testimonios">
        <div class="container">
            <div class="section-label">Lo que dicen nuestros pacientes</div>
            <h2
                style="font-family:'Poppins',sans-serif;font-weight:900;text-transform:uppercase;font-size:clamp(28px,4vw,44px);color:var(--navy);">
                TESTIMONIOS REALES</h2>

            <!-- Grilla 4 columnas -->
            <div class="testim-grid">

                <!-- CARD 1 — Imagen simple -->
                <div class="testim-card">
                    <div class="testim-media">
                        <img class="tm-thumb"
                            src="https://plus.unsplash.com/premium_photo-1675808575868-8547c37c047c?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            alt="Susana M." loading="lazy" />
                    </div>
                    <div class="testim-body">
                        <div class="testim-brand">TECNOMEDIC SALUD</div>
                        <div class="testim-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="testim-quote">"Mi herida no cerraba hace 2 años."</div>
                        <div class="testim-result">Con el asesoramiento adecuado y tratamiento , cicatrizó.</div>
                        <div class="testim-name">Susana .</div>
                        <div class="testim-role">Paciente de heridas complejas-pie diabetico</div>
                        <div class="testim-badge"><i class="fa-solid fa-circle-check"></i> Verificado</div>
                    </div>
                </div>

                <!-- CARD 2 — Video con thumbnail (click para reproducir) -->
                <div class="testim-card">
                    <div class="testim-media" id="tmMedia1">
                        <!-- Thumbnail: reemplazá src por un frame del video -->
                        <img class="tm-thumb" src="<?= $base ?>/static/video/deporte2.JPG" alt="Video testimonio"
                            loading="lazy" />
                        <!-- Video: reemplazá src por tu video real -->
                        <video class="tm-video" id="tmVideo1" controls preload="none">
                            <source src="<?= $base ?>/static/video/deporte2.mp4" type="video/mp4" />
                        </video>
                        <!-- Botón play -->
                        <div class="tm-play-btn" onclick="playTestimVideo('tmMedia1','tmVideo1')">
                            <div class="tm-play-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="tm-video-badge">
                            <i class="fa-solid fa-video"></i> Video
                        </div>
                    </div>
                    <div class="testim-body">
                        <div class="testim-brand">TECNOMEDIC SALUD</div>
                        <div class="testim-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="testim-quote">"Tenía dolores en la espalda , vine a recuperacion muscular"</div>
                        <div class="testim-result">Con el tratamiento, senti cambios en la segunda sesion.</div>
                        <div class="testim-name">Luciano</div>
                        <div class="testim-role">Paciente deportista</div>
                        <div class="testim-badge"><i class="fa-solid fa-circle-check"></i> Verificado</div>
                    </div>
                </div>

                <!-- CARD 3 — Imagen simple -->
                <div class="testim-card">
                    <div class="testim-media" id="tmMedia2">
                        <!-- Thumbnail: reemplazá src por un frame del video -->
                        <img class="tm-thumb" src="<?= $base ?>/static/video/deporte1.JPG" alt="Video testimonio"
                            loading="lazy" />
                        <!-- Video: reemplazá src por tu video real -->
                        <video class="tm-video" id="tmVideo2" controls preload="none">
                            <source src="<?= $base ?>/static/video/deporte1.mp4" type="video/mp4" />
                        </video>
                        <!-- Botón play -->
                        <div class="tm-play-btn" onclick="playTestimVideo('tmMedia2','tmVideo2')">
                            <div class="tm-play-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="tm-video-badge">
                            <i class="fa-solid fa-video"></i> Video
                        </div>
                    </div>
                    <div class="testim-body">
                        <div class="testim-brand">TECNOMEDIC SALUD</div>
                        <div class="testim-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="testim-quote">"Me ayudaron en la recuperacion y oxigenación"</div>
                        <div class="testim-result">La OHB aceleró la recuperación de mis atletas.</div>
                        <div class="testim-name">Marilyn -Luciana -Alejandro</div>
                        <div class="testim-role">Deportistas</div>
                        <div class="testim-badge"><i class="fa-solid fa-circle-check"></i> Verificado</div>
                    </div>
                </div>

                <!-- CARD 4 — Video con thumbnail (click para reproducir) -->
                <div class="testim-card">
                    <div class="testim-media" id="tmMedia2">
                        <!-- Thumbnail del video: capturá un frame o usá una imagen representativa -->
                        <img class="tm-thumb"
                            src="https://plus.unsplash.com/premium_photo-1726797723292-b2dc29e3dff2?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            alt="audifonos" loading="lazy" />
                        <!-- Video: reemplazá src por tu segundo video -->
                        <!-- <video class="tm-video" id="tmVideo2" controls preload="none">
                            <source src="{{ url_for('static', filename='video/') }}" type="video/mp4" />
                        </video>
                        <div class="tm-play-btn" onclick="playTestimVideo('tmMedia2','tmVideo2')">
                            <div class="tm-play-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="tm-video-badge">
                            <i class="fa-solid fa-video"></i> Video
                        </div> -->
                    </div>
                    <div class="testim-body">
                        <div class="testim-brand">TECNOMEDIC SALUD</div>
                        <div class="testim-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="testim-quote">"Ahora puedo escuchar a mis nietos jugar."</div>
                        <div class="testim-result">volvi a sentirme parte de la conversación.</div>
                        <div class="testim-name">Marta</div>
                        <div class="testim-role">Paciente hipoacusico</div>
                        <div class="testim-badge"><i class="fa-solid fa-circle-check"></i> Verificado</div>
                    </div>
                </div>

            </div><!-- /testim-grid -->

            <!--
                ★ PARA AGREGAR TARJETAS:
                – Imagen: copiar CARD 1 o CARD 3
                – Video:  copiar CARD 2 o CARD 4 y cambiar:
                    · id="tmMedia3" id="tmVideo3" (número nuevo)
                    · src del <video> por tu archivo
                    · src del <img> por el thumbnail
                    · onclick="playTestimVideo('tmMedia3','tmVideo3')"
            -->

        </div>
    </section>
    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="ftr-logo-row">
                        <img class="ftr-logo" src="<?= $base ?>/static/img/tecno-logo.jpeg" alt="TECNOMEDIC" />
                        <div class="ftr-name">TECNOMEDIC</div>
                    </div>
                    <p class="ftr-desc">Tecnología avanzada para tu recuperación y bienestar integral en Corrientes,
                        Argentina.</p>
                    <div class="ftr-social">
                        <a href="https://www.instagram.com/tecnomedicsalud_/" target="_blank"><i
                                class="fa-brands fa-instagram"></i></a>
                        <a href="https://wa.me/5493794775341" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
                        <a href="https://www.facebook.com/" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                    </div>
                </div>
                <div>
                    <div class="ftr-col-ttl">Servicios</div>
                    <div class="ftr-links">
                        <a href="#servicios">Cámara Hiperbárica</a>
                        <a href="#servicios">Nutrición</a>
                        <a href="#servicios">Ortopedia</a>
                        <a href="#servicios">Tratamiento de Heridas</a>
                    </div>
                </div>
                <div>
                    <div class="ftr-col-ttl">Síguenos</div>
                    <div class="ftr-links">
                        <a href="https://www.instagram.com/tecnomedicsalud_/" target="_blank"><i
                                class="fa-brands fa-instagram"></i> Instagram</a>
                        <a href="https://wa.me/5493794775341" target="_blank"><i class="fa-brands fa-whatsapp"></i>
                            WhatsApp</a>
                    </div>
                    <div style="margin-top:22px;">
                        <div class="ftr-col-ttl">Turnos</div>
                        <div class="ftr-links">
                            <a href="<?= $base ?>/turnos.php">Reservar turno</a>
                            <a href="<?= $base ?>/admin/">Panel admin</a>
                        </div>
                    </div>
                </div>
                <div class="ftr-nl">
                    <div class="ftr-col-ttl">Contacto</div>
                    <div class="ftr-contact">
                        <a href="tel:+5437943490278"><i class="fa-solid fa-phone"></i> (3794) 34-9278</a>
                        <a href="https://wa.me/5493794775341" target="_blank"><i class="fa-brands fa-whatsapp"></i>
                            WhatsApp directo</a>
                        <span><i class="fa-regular fa-clock" style="color:var(--green);"></i> Lun–Vie 8:00–18:00 · Sáb
                            9:00–13:00</span>
                        <span><i class="fa-solid fa-location-dot" style="color:var(--green);"></i> C. Pellegrini 799,
                            Corrientes</span>
                    </div>
                </div>
            </div>
            <div class="ftr-wm">CENTRO HIPERBÁRICO</div>
            <div class="ftr-bottom">
                <span>© <?= date('Y') ?> TECNOMEDIC · Centro Médico Hiperbárico. Todos los derechos reservados.</span>
                <span>Corrientes, Argentina</span>
            </div>
        </div>
    </footer>

    <a class="wa-float" href="https://wa.me/5493794775341?text=Hola!%20Quiero%20consultar%20sobre%20un%20turno"
        target="_blank" title="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <button class="scroll-top" id="scrollTopBtn" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <i class="fa-solid fa-chevron-up"></i>
    </button>

    <script>
    const nb = document.getElementById('navbar');
    const stb = document.getElementById('scrollTopBtn');
    window.addEventListener('scroll', () => {
        nb.classList.toggle('scrolled', scrollY > 40);
        stb.classList.toggle('visible', scrollY > 380);
    });
    const mMenu = document.getElementById('mobileMenu');

    document.getElementById('hamburger').onclick = () => {
        mMenu.classList.add('open');
        document.body.style.overflow = 'hidden';
    };
    document.getElementById('mobileClose').onclick = closeMobile;

    function closeMobile() {
        mMenu.classList.remove('open');
        document.body.style.overflow = '';
    }

    /* Cerrar con Escape */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMobile();
    });

    /* Cerrar tocando el fondo (fuera de los links/botones) */
    mMenu.addEventListener('click', e => {
        if (e.target === mMenu) closeMobile();
    });

    /* Cerrar con swipe-down en móvil */
    let _swipeY = 0;
    mMenu.addEventListener('touchstart', e => {
        _swipeY = e.touches[0].clientY;
    }, {
        passive: true
    });
    mMenu.addEventListener('touchend', e => {
        if (e.changedTouches[0].clientY - _swipeY > 60) closeMobile();
    }, {
        passive: true
    });
    document.getElementById('mobileClose').onclick = closeMobile;

    function closeMobile() {
        mMenu.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeMobile();
    });
    mMenu.addEventListener('click', e => {
        if (e.target === mMenu) closeMobile();
    });
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const t = document.querySelector(a.getAttribute('href'));
            if (t) {
                e.preventDefault();
                t.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    (function() {
        const TOTAL = 5,
            INTERVAL = 10000;
        const track = document.getElementById('ncarTrack');
        const dotsW = document.getElementById('ncarDots');
        const ctr = document.getElementById('ncarCounter');
        const bar = document.getElementById('ncarBar');
        let cur = 0,
            timer;
        const dots = Array.from({
            length: TOTAL
        }, (_, i) => {
            const b = document.createElement('button');
            b.className = 'ncar-dot' + (i === 0 ? ' active' : '');
            b.onclick = () => goTo(i);
            dotsW.appendChild(b);
            return b;
        });

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function render() {
            track.style.transform = `translateX(-${cur * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === cur));
            ctr.textContent = `${pad(cur + 1)} / ${pad(TOTAL)}`;
            bar.style.transition = 'none';
            bar.style.width = '0%';
            requestAnimationFrame(() => requestAnimationFrame(() => {
                bar.style.transition = `width ${INTERVAL}ms linear`;
                bar.style.width = '100%';
            }));
        }

        function goTo(idx) {
            cur = (idx + TOTAL) % TOTAL;
            render();
            clearInterval(timer);
            timer = setInterval(() => goTo(cur + 1), INTERVAL);
        }
        document.getElementById('ncarNext').onclick = () => goTo(cur + 1);
        document.getElementById('ncarPrev').onclick = () => goTo(cur - 1);
        let tx = 0;
        track.addEventListener('touchstart', e => tx = e.touches[0].clientX, {
            passive: true
        });
        track.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - tx;
            if (Math.abs(dx) > 42) goTo(dx < 0 ? cur + 1 : cur - 1);
        }, {
            passive: true
        });
        track.addEventListener('mouseenter', () => clearInterval(timer));
        track.addEventListener('mouseleave', () => {
            timer = setInterval(() => goTo(cur + 1), INTERVAL);
        });
        render();
        timer = setInterval(() => goTo(cur + 1), INTERVAL);
    })();

    // // TESTIMONIOS: reproducir video al hacer click en el thumbnail
    /* Testimonios scroll */
    const tt = document.getElementById('testimTrack');
    document.getElementById('tNext').onclick = () => tt.scrollBy({
        left: 490,
        behavior: 'smooth'
    });
    document.getElementById('tPrev').onclick = () => tt.scrollBy({
        left: -490,
        behavior: 'smooth'
    });

    /* Testimonios: reproducir video al click del thumbnail */
    function playTestimVideo(mediaId, videoId) {
        const media = document.getElementById(mediaId);
        const video = document.getElementById(videoId);
        if (!media || !video) return;
        media.classList.add('playing');
        video.play().catch(() => {});
        // Si el usuario pausa el video, mostrar thumbnail de nuevo
        video.addEventListener('pause', function onPause() {
            media.classList.remove('playing');
            video.removeEventListener('pause', onPause);
        }, {
            once: true
        });
    }
    </script>
</body>

</html>