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

$titulos = [
    'ventas' => 'Reporte de Ventas',
    'productos' => 'Reporte de Productos',
    'servicios' => 'Reporte de Servicios',
    'citas' => 'Reporte de Citas',
    'pagos' => 'Reporte de Pagos',
    'inventario' => 'Reporte de Inventario',
];

$filas = [];
$encabezados = [];

if ($reporte === 'ventas') {
    $datos = pedidos_cargar();
    $encabezados = ['Pedido', 'Cliente', 'Metodo', 'Estado', 'Fecha', 'Total'];
    foreach ($datos as $fila) {
        $filas[] = [
            (string)($fila['numero_pedido'] ?? ''),
            (string)($fila['cliente'] ?? ''),
            (string)($fila['metodo_pago'] ?? ''),
            (string)($fila['estado'] ?? ''),
            (string)($fila['fecha'] ?? ''),
            'CRC ' . number_format((float)($fila['total'] ?? 0), 0, ',', '.'),
        ];
    }
}

if ($reporte === 'productos' || $reporte === 'inventario') {
    $datos = productos_cargar();
    $encabezados = ['Producto', 'Categoria', 'Precio', 'Stock', 'Estado'];
    foreach ($datos as $fila) {
        $filas[] = [
            (string)($fila['nombre'] ?? ''),
            (string)($fila['categoria'] ?? ''),
            'CRC ' . number_format((float)($fila['precio'] ?? 0), 0, ',', '.'),
            (string)($fila['stock'] ?? '0'),
            (string)($fila['estado'] ?? ''),
        ];
    }
}

if ($reporte === 'servicios') {
    $datos = servicios_cargar();
    $encabezados = ['Servicio', 'Categoria', 'Duracion', 'Precio', 'Estado'];
    foreach ($datos as $fila) {
        $filas[] = [
            (string)($fila['nombre'] ?? ''),
            (string)($fila['categoria'] ?? ''),
            (string)($fila['duracion'] ?? '0') . ' min',
            'CRC ' . number_format((float)($fila['precio'] ?? 0), 0, ',', '.'),
            (string)($fila['estado'] ?? ''),
        ];
    }
}

if ($reporte === 'citas') {
    $serviciosCitas = cargar_servicios_citas();
    $serviciosIndexCitas = indexar_servicios_por_id($serviciosCitas);
    $datos = cargar_citas($serviciosIndexCitas);
    $encabezados = ['Cliente', 'Servicio', 'Fecha', 'Hora', 'Estado', 'Pago'];
    foreach ($datos as $fila) {
        $filas[] = [
            (string)($fila['cliente'] ?? ''),
            (string)($fila['servicio'] ?? ''),
            (string)($fila['fecha'] ?? ''),
            (string)($fila['hora'] ?? ''),
            (string)($fila['estado'] ?? ''),
            (string)($fila['pago'] ?? ''),
        ];
    }
}

if ($reporte === 'pagos') {
    $datos = pagos_cargar();
    $encabezados = ['Numero pago', 'Cliente', 'Metodo', 'Estado', 'Fecha', 'Monto'];
    foreach ($datos as $fila) {
        $filas[] = [
            (string)($fila['numero_pago'] ?? ''),
            (string)($fila['cliente'] ?? ''),
            (string)($fila['metodo'] ?? ''),
            (string)($fila['estado'] ?? ''),
            (string)($fila['fecha'] ?? ''),
            'CRC ' . number_format((float)($fila['monto'] ?? 0), 0, ',', '.'),
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulos[$reporte], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #111; }
        h1 { margin: 0 0 8px; }
        p { color: #444; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f1f1f1; }
        .top-actions { margin-bottom: 14px; }
        .btn { display: inline-block; padding: 8px 10px; border: 1px solid #444; text-decoration: none; color: #111; margin-right: 8px; }
        @media print { .top-actions { display: none; } }
    </style>
</head>
<body>
    <div class="top-actions">
        <a class="btn" href="imprimir.php?reporte=<?php echo urlencode($reporte); ?>">Imprimir</a>
        <a class="btn" href="index.php">Volver a reportes</a>
    </div>

    <h1><?php echo htmlspecialchars($titulos[$reporte], ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>Exportacion PDF base (lista para guardarse como PDF desde el navegador).</p>

    <table>
        <thead>
            <tr>
                <?php foreach ($encabezados as $encabezado): ?>
                    <th><?php echo htmlspecialchars($encabezado, ENT_QUOTES, 'UTF-8'); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (!$filas): ?>
                <tr><td colspan="<?php echo count($encabezados); ?>">Sin datos.</td></tr>
            <?php endif; ?>
            <?php foreach ($filas as $fila): ?>
                <tr>
                    <?php foreach ($fila as $valor): ?>
                        <td><?php echo htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        if (new URLSearchParams(window.location.search).get('auto_print') === '1') {
            window.print();
        }
    </script>
</body>
</html>
