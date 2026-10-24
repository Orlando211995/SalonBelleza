<?php
require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/_reporte.php';
require_once __DIR__ . '/_pdf.php';

ob_start();
$reporte = reporte_desde_peticion();
$pdf = reporte_pdf($reporte);
ob_end_clean();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . reporte_nombre_archivo($reporte, 'pdf') . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
exit;
