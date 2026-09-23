<?php
require_once __DIR__ . '/../../includes/auth.php';
portal_require_role(['paciente']);

$user = portal_current_user();
$perfil = perfil_paciente($user['id']);
$turnos = turnos_de_paciente($user['dni']);

$portal_titulo = 'Mi Portal · ' . $user['nombre'];
$portal_activo = 'inicio';
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="portal-card">
    <img class="icon-image" src="<?= $base ?>/static/img/icons/home.ico" alt="" />
    <h2>Hola, <?= htmlspecialchars($user['nombre']) ?> </h2>
    <p><strong>DNI:</strong> <?= htmlspecialchars($user['dni']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Teléfono:</strong> <?= htmlspecialchars($user['telefono'] ?: '-') ?></p>
    <p><strong>Obra social:</strong> <?= htmlspecialchars($perfil['obra_social_nombre'] ?? '-') ?></p>
</div>

<div class="portal-card">
    <h2 style="display:flex;align-items:center;gap:20px; padding-bottom: 12px;">
        <span> Accesos rápidos</span>
    </h2>
   

    <div class="tablero-grid" style="padding:20px;">
        <a class="tablero-tile" href="<?= b('/portal/paciente/perfil.php') ?>">
            <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/usuario.ico" alt="" /></div>
            <div class="tablero-title" data-tooltip="Ver mis datos">Mi perfil</div>
            <!-- <div class="tablero-desc">Datos personales y obra social</div> -->
        </a>

        <a class="tablero-tile" href="#" onclick="abrirFicha(<?= (int)$user['id'] ?>); return false;">
            <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/ficha-medica.ico" alt="" /></div>
            <div class="tablero-title" data-tooltip="Ver ficha médica">Mi ficha médica</div>
            <!-- <div class="tablero-desc">Historia clínica y tratamientos</div> -->
        </a>

        <a class="tablero-tile" href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $user['id']) ?>">
            <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/registro.ico" alt="" /></div>
            <div class="tablero-title" data-tooltip="Registro alimentario">Registro alimentario</div>
            <!-- <div class="tablero-desc">Control nutricional (Fase E)</div> -->
        </a>

        <a class="tablero-tile" href="<?= b('/portal/recursos.php') ?>">
            <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/carpeta.ico" alt="" /></div>
            <div class="tablero-title" data-tooltip="Ver recursos">Recursos</div>
            <!-- <div class="tablero-desc">Archivos y formularios</div> -->
        </a>

        <a class="tablero-tile" href="#turnos">
            <div class="tablero-icon"><img class="icon-image" src="<?= $base ?>/static/img/icons/calendario2.ico" alt="" /></div>
            <div class="tablero-title" data-tooltip="Ver mis turnos">Mis turnos</div>
            <!-- <div class="tablero-desc">Fecha, hora y estado</div> -->
        </a>
    </div>
</div>

<div class="portal-card" id="turnos">
    <h2>Mis turnos</h2>

    <div style="margin: 12px 0 18px; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <a class="portal-btn" style="margin-top:0;" href="<?= b('/turnos.php?' . http_build_query([
            'area' => 'hiperbarica',
            'nombre' => $user['nombre'] ?? '',
            'apellido' => $user['apellido'] ?? '',
            'dni' => $user['dni'] ?? '',
            'telefono' => $user['telefono'] ?? '',
            'email' => $user['email'] ?? '',
            'obra_social' => $perfil['obra_social_nombre'] ?? '',
        ])) ?>">Agregar turno</a>
    </div>

    <?php if (!$turnos): ?>
        <p>No tenés turnos registrados todavía.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach ($turnos as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['fecha']) ?></td>
                    <td><?= htmlspecialchars($t['hora']) ?></td>
                    <td><?= htmlspecialchars($t['estado']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
