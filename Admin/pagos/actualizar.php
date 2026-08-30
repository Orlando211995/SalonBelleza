<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$numeroPago = trim($_POST['numero_pago'] ?? '');
$metodo = trim($_POST['metodo'] ?? 'SINPE Movil');
$estado = trim($_POST['estado'] ?? 'Pendiente');
$monto = isset($_POST['monto']) ? (float)$_POST['monto'] : -1;
$observaciones = trim($_POST['observaciones'] ?? '');

$pagos = pagos_cargar();
$indice = pagos_buscar_indice_por_id($pagos, $id);

if ($indice === null) {
    $_SESSION['error_pago'] = 'Pago no encontrado.';
    header('Location: listar.php');
    exit;
}

$errores = [];
if ($numeroPago === '') {
    $errores[] = 'El numero de pago es obligatorio.';
}
if (!in_array($metodo, pagos_metodos_validos(), true)) {
    $errores[] = 'Metodo de pago no valido.';
}
if (!in_array($estado, pagos_estados_validos(), true)) {
    $errores[] = 'Estado de pago no valido.';
}
if ($monto < 0) {
    $errores[] = 'El monto debe ser mayor o igual a 0.';
}

foreach ($pagos as $pago) {
    if ((int)($pago['id'] ?? 0) === $id) {
        continue;
    }
    if (strcasecmp((string)($pago['numero_pago'] ?? ''), $numeroPago) === 0) {
        $errores[] = 'Ya existe otro pago con ese numero.';
        break;
    }
}

if ($errores) {
    $_SESSION['errores_pago_editar'] = $errores;
    $_SESSION['old_pago_editar'] = [
        'numero_pago' => $numeroPago,
        'metodo' => $metodo,
        'estado' => $estado,
        'monto' => (string)$monto,
        'observaciones' => $observaciones,
    ];
    header('Location: editar.php?id=' . $id);
    exit;
}

$pagos[$indice]['numero_pago'] = $numeroPago;
$pagos[$indice]['metodo'] = $metodo;
$pagos[$indice]['estado'] = $estado;
$pagos[$indice]['monto'] = $monto;
$pagos[$indice]['observaciones'] = $observaciones;

pagos_guardar($pagos);

$_SESSION['ok_pago'] = 'Pago actualizado correctamente.';
header('Location: listar.php');
exit;
