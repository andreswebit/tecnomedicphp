<?php
// El panel de turnos ahora usa el mismo login unificado del Portal
// (login.php en la raíz). Este archivo queda solo como redirect, para
// no romper links/accesos directos guardados de antes.
require_once __DIR__ . '/../includes/db.php';
header('Location: ' . BASE_URL . '/login.php');
exit;
