<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pagos = pagos_cargar();
$indice = pagos_buscar_indice_por_id($pagos, $id);

if ($indice === null) {
    $_SESSION['error_pago'] = 'Pago no encontrado.';
    header('Location: listar.php');
    exit;
}

$pagos[$indice]['estado'] = 'Aprobado';
pagos_guardar($pagos);
pagos_sync_pedido_estado($id, 'Aprobado');

$_SESSION['ok_pago'] = 'Pago aprobado correctamente.';
header('Location: listar.php');
exit;
