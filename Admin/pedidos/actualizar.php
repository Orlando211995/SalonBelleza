<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$numeroPedido = trim($_POST['numero_pedido'] ?? '');
$estado = trim($_POST['estado'] ?? 'Pendiente');
$metodoPago = 'SINPE';
$total = isset($_POST['total']) ? (float)$_POST['total'] : -1;
$direccion = trim($_POST['direccion'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

$pedidos = pedidos_cargar();
$indice = pedidos_buscar_indice_por_id($pedidos, $id);

if ($indice === null) {
    $_SESSION['error_pedido'] = 'Pedido no encontrado.';
    header('Location: listar.php');
    exit;
}

$errores = [];

if ($numeroPedido === '') {
    $errores[] = 'El numero de pedido es obligatorio.';
}
if (!in_array($estado, pedidos_estados_validos(), true)) {
    $errores[] = 'El estado del pedido no es valido.';
}
if (!in_array($metodoPago, pedidos_metodos_pago_validos(), true)) {
    $errores[] = 'El metodo de pago no es valido.';
}
if ($total < 0) {
    $errores[] = 'El total debe ser mayor o igual a 0.';
}
if ($direccion === '') {
    $errores[] = 'La direccion es obligatoria.';
}

foreach ($pedidos as $pedido) {
    if ((int)($pedido['id'] ?? 0) === $id) {
        continue;
    }
    if (strcasecmp((string)($pedido['numero_pedido'] ?? ''), $numeroPedido) === 0) {
        $errores[] = 'Ya existe otro pedido con ese numero.';
        break;
    }
}

if ($errores) {
    $_SESSION['errores_pedido_editar'] = $errores;
    $_SESSION['old_pedido_editar'] = [
        'numero_pedido' => $numeroPedido,
        'estado' => $estado,
        'metodo_pago' => $metodoPago,
        'total' => (string)$total,
        'direccion' => $direccion,
        'observaciones' => $observaciones,
    ];
    header('Location: editar.php?id=' . $id);
    exit;
}

$pedidos[$indice]['numero_pedido'] = $numeroPedido;
$pedidos[$indice]['estado'] = $estado;
$pedidos[$indice]['metodo_pago'] = $metodoPago;
$pedidos[$indice]['total'] = $total;
$pedidos[$indice]['direccion'] = $direccion;
$pedidos[$indice]['observaciones'] = $observaciones;

pedidos_guardar($pedidos);

$_SESSION['ok_pedido'] = 'Pedido actualizado correctamente.';
header('Location: listar.php');
exit;
