<?php
require_once __DIR__ . '/../pedidos/_data.php';
require_once __DIR__ . '/../productos/_data.php';
require_once __DIR__ . '/../servicios/_data.php';
require_once __DIR__ . '/../citas/_agenda.php';
require_once __DIR__ . '/../pagos/_data.php';

$reporte = trim($_GET['reporte'] ?? 'ventas');
$permitidos = ['ventas', 'productos', 'servicios', 'citas', 'pagos', 'inventario'];
if (!in_array($reporte, $permitidos, true)) {
    http_response_code(400);
    echo 'Reporte no permitido';
    exit;
}

$lineas = [];

if ($reporte === 'ventas') {
    $pedidos = pedidos_cargar();
    $lineas[] = ['Pedido', 'Cliente', 'Metodo', 'Estado', 'Fecha', 'Total'];
    foreach ($pedidos as $pedido) {
        $lineas[] = [
            (string)($pedido['numero_pedido'] ?? ''),
            (string)($pedido['cliente'] ?? ''),
            (string)($pedido['metodo_pago'] ?? ''),
            (string)($pedido['estado'] ?? ''),
            (string)($pedido['fecha'] ?? ''),
            (string)($pedido['total'] ?? '0'),
        ];
    }
}

if ($reporte === 'productos') {
    $productos = productos_cargar();
    $lineas[] = ['Producto', 'Categoria', 'Precio', 'Stock', 'Estado'];
    foreach ($productos as $producto) {
        $lineas[] = [
            (string)($producto['nombre'] ?? ''),
            (string)($producto['categoria'] ?? ''),
            (string)($producto['precio'] ?? '0'),
            (string)($producto['stock'] ?? '0'),
            (string)($producto['estado'] ?? ''),
        ];
    }
}

if ($reporte === 'servicios') {
    $servicios = servicios_cargar();
    $lineas[] = ['Servicio', 'Categoria', 'Duracion', 'Precio', 'Estado'];
    foreach ($servicios as $servicio) {
        $lineas[] = [
            (string)($servicio['nombre'] ?? ''),
            (string)($servicio['categoria'] ?? ''),
            (string)($servicio['duracion'] ?? '0'),
            (string)($servicio['precio'] ?? '0'),
            (string)($servicio['estado'] ?? ''),
        ];
    }
}

if ($reporte === 'citas') {
    $serviciosCitas = cargar_servicios_citas();
    $serviciosIndexCitas = indexar_servicios_por_id($serviciosCitas);
    $citas = cargar_citas($serviciosIndexCitas);
    $lineas[] = ['Cliente', 'Servicio', 'Fecha', 'Hora', 'Estado', 'Pago'];
    foreach ($citas as $cita) {
        $lineas[] = [
            (string)($cita['cliente'] ?? ''),
            (string)($cita['servicio'] ?? ''),
            (string)($cita['fecha'] ?? ''),
            (string)($cita['hora'] ?? ''),
            (string)($cita['estado'] ?? ''),
            (string)($cita['pago'] ?? ''),
        ];
    }
}

if ($reporte === 'pagos') {
    $pagos = pagos_cargar();
    $lineas[] = ['Numero pago', 'Cliente', 'Metodo', 'Estado', 'Fecha', 'Monto'];
    foreach ($pagos as $pago) {
        $lineas[] = [
            (string)($pago['numero_pago'] ?? ''),
            (string)($pago['cliente'] ?? ''),
            (string)($pago['metodo'] ?? ''),
            (string)($pago['estado'] ?? ''),
            (string)($pago['fecha'] ?? ''),
            (string)($pago['monto'] ?? '0'),
        ];
    }
}

if ($reporte === 'inventario') {
    $productos = productos_cargar();
    $lineas[] = ['Producto', 'Categoria', 'Precio', 'Stock'];
    foreach ($productos as $producto) {
        $lineas[] = [
            (string)($producto['nombre'] ?? ''),
            (string)($producto['categoria'] ?? ''),
            (string)($producto['precio'] ?? '0'),
            (string)($producto['stock'] ?? '0'),
        ];
    }
}

$nombre = 'reporte_' . $reporte . '_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $nombre);

echo "\xEF\xBB\xBF";
$salida = fopen('php://output', 'wb');
foreach ($lineas as $linea) {
    fputcsv($salida, $linea, ';');
}
fclose($salida);
exit;
