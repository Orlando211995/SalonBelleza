<?php
session_start();

require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pedidos = pedidos_cargar();
$indice = pedidos_buscar_indice_por_id($pedidos, $id);

if ($indice === null) {
    $_SESSION['error_pedido'] = 'Pedido no encontrado.';
    header('Location: listar.php');
    exit;
}

unset($pedidos[$indice]);
pedidos_guardar(array_values($pedidos));

$_SESSION['ok_pedido'] = 'Pedido eliminado correctamente.';
header('Location: listar.php');
exit;
