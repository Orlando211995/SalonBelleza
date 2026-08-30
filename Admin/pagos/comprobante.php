<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pagos = pagos_cargar();
$pago = pagos_buscar_por_id($pagos, $id);

if (!$pago) {
    $_SESSION['error_pago'] = 'Pago no encontrado.';
    header('Location: listar.php');
    exit;
}

$comprobante = trim((string)($pago['comprobante'] ?? ''));
if ($comprobante === '') {
    $_SESSION['error_pago'] = 'Este pago no tiene comprobante adjunto.';
    header('Location: ver.php?id=' . $id);
    exit;
}

if (strpos($comprobante, '/') === 0) {
    header('Location: ' . $comprobante);
    exit;
}

$archivo = __DIR__ . '/../../' . ltrim($comprobante, '/');
if (is_file($archivo)) {
    header('Location: /' . ltrim($comprobante, '/'));
    exit;
}

$_SESSION['error_pago'] = 'No se pudo localizar el comprobante adjunto.';
header('Location: ver.php?id=' . $id);
exit;
