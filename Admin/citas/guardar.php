<?php
session_start();

require_once __DIR__ . '/_agenda.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$cliente = trim($_POST['cliente'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$servicioId = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
$empleado = trim($_POST['empleado'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$hora = trim($_POST['hora'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$estado = normalizar_estado_cita(trim($_POST['estado'] ?? 'Pendiente'));
$pago = normalizar_pago_cita(trim($_POST['pago'] ?? 'No aplica'));

$errores = [];

if ($cliente === '') {
    $errores[] = 'El nombre del cliente es obligatorio.';
}
if ($telefono === '') {
    $errores[] = 'El telefono es obligatorio.';
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo no es valido.';
}
if (!fecha_valida_ymd($fecha)) {
    $errores[] = 'La fecha no es valida.';
}
if (!hora_valida_hm($hora)) {
    $errores[] = 'La hora no es valida.';
}

$servicio = servicio_por_id($servicioId, $serviciosIndex);
if (!$servicio) {
    $errores[] = 'Debes seleccionar un servicio valido.';
}

$duracion = (int)($servicio['duracion'] ?? 0);
if ($duracion <= 0) {
    $errores[] = 'La duracion del servicio no es valida.';
}

if (!$errores && !hora_disponible_para_cita($fecha, $hora, $duracion, $citas)) {
    $errores[] = 'La hora elegida no esta disponible para ese servicio.';
}

if ($errores) {
    $_SESSION['errores_cita'] = $errores;
    $_SESSION['old_cita'] = [
        'cliente' => $cliente,
        'telefono' => $telefono,
        'correo' => $correo,
        'servicio_id' => (string)$servicioId,
        'empleado' => $empleado,
        'fecha' => $fecha,
        'hora' => $hora,
        'observaciones' => $observaciones,
        'estado' => $estado,
        'pago' => $pago,
    ];
    header('Location: agregar.php?servicio_id=' . $servicioId . '&fecha=' . urlencode($fecha));
    exit;
}

$citas[] = [
    'id' => siguiente_cita_id($citas),
    'cliente' => $cliente,
    'telefono' => $telefono,
    'correo' => $correo,
    'servicio_id' => (int)$servicio['id'],
    'servicio' => (string)$servicio['nombre'],
    'duracion' => $duracion,
    'empleado' => $empleado,
    'fecha' => $fecha,
    'hora' => $hora,
    'observaciones' => $observaciones,
    'estado' => $estado,
    'pago' => $pago,
    'created_at' => date('c'),
];

guardar_citas($citas);

$_SESSION['ok_cita'] = 'Cita guardada correctamente.';
header('Location: listar.php');
exit;
