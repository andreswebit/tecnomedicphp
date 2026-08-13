<?php
// Esta página se dividió en admin/pacientes.php y admin/profesionales.php
// (más admin/usuarios.php para alta/edición/borrado general). Queda como
// redirect para no romper links guardados de antes.
require_once __DIR__ . '/../includes/db.php';
header('Location: ' . BASE_URL . '/admin/tablero.php');
exit;
