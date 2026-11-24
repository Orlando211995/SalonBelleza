<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$encontrada = false;
foreach ($citas as $i => $cita) {
    if ((int)($cita['id'] ?? 0) !== $id) {
        continue;
    }
    unset($citas[$i]);
    $encontrada = true;
    break;
}

if (!$encontrada) {
    $_SESSION['error_cita'] = 'Cita no encontrada.';
    header('Location: listar.php');
    exit;
}

if (!guardar_citas(array_values($citas))) {
    $_SESSION['error_cita'] = 'No se pudo eliminar la cita. Intentalo de nuevo.';
    header('Location: listar.php');
    exit;
}

$_SESSION['ok_cita'] = 'Cita eliminada correctamente.';
header('Location: listar.php');
exit;
