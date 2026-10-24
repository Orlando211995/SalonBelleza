<?php
require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/_reporte.php';
require_once __DIR__ . '/_xlsx.php';

ob_start();
$reporte = reporte_desde_peticion();
$xlsx = reporte_xlsx($reporte);
ob_end_clean();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . reporte_nombre_archivo($reporte, 'xlsx') . '"');
header('Content-Length: ' . strlen($xlsx));
header('Cache-Control: private, no-store');
echo $xlsx;
exit;
