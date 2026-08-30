<?php
$reporte = trim($_GET['reporte'] ?? 'ventas');
$permitidos = ['ventas', 'productos', 'servicios', 'citas', 'pagos', 'inventario'];

if (!in_array($reporte, $permitidos, true)) {
    http_response_code(400);
    echo 'Reporte no permitido';
    exit;
}

$query = $_GET;
$query['reporte'] = $reporte;
$query['auto_print'] = '1';
$destino = 'exportar_pdf.php?' . http_build_query($query);
header('Location: ' . $destino);
exit;
