<?php

require_once __DIR__ . '/_agenda.php';

$servicioId = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : 0;
$fecha = trim($_GET['fecha'] ?? date('Y-m-d'));
$format = trim($_GET['format'] ?? 'html');

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$servicio = servicio_por_id($servicioId, $serviciosIndex);
$duracion = (int)($servicio['duracion'] ?? 30);
$horas = [];

if ($servicio && fecha_valida_ymd($fecha)) {
    $horas = generar_horas_disponibles($fecha, $duracion, $citas);
}

$payload = [
    'fecha' => $fecha,
    'servicio_id' => $servicioId,
    'servicio' => $servicio['nombre'] ?? null,
    'duracion' => $duracion,
    'horas_disponibles' => $horas,
];

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disponibilidad</title>
    <style>
        body { font-family: Poppins, sans-serif; padding: 16px; background: #111722; color: #eff4ff; }
        .chip { display:inline-block; padding:8px 10px; border:1px solid #304560; border-radius:999px; margin:4px; background:#162133; }
    </style>
</head>
<body>
    <h2>Disponibilidad del <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Servicio: <?php echo htmlspecialchars((string)($servicio['nombre'] ?? 'No valido'), ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)$duracion; ?> min)</p>
    <?php if (!$horas): ?>
        <p>No hay horas disponibles.</p>
    <?php else: ?>
        <?php foreach ($horas as $hora): ?>
            <span class="chip"><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
