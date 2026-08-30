<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $accion = trim((string)$_GET['accion']);

    $map = [
        'confirmar' => 'Confirmada',
        'proceso' => 'En proceso',
        'finalizar' => 'Finalizada',
        'cancelar' => 'Cancelada',
        'noasistio' => 'No asistio',
    ];

    if (!isset($map[$accion])) {
        $_SESSION['error_cita'] = 'Accion no valida.';
        header('Location: listar.php');
        exit;
    }

    $ok = false;
    foreach ($citas as $i => $cita) {
        if ((int)($cita['id'] ?? 0) !== $id) {
            continue;
        }
        $citas[$i]['estado'] = $map[$accion];
        $ok = true;
        break;
    }

    if (!$ok) {
        $_SESSION['error_cita'] = 'Cita no encontrada.';
        header('Location: listar.php');
        exit;
    }

    guardar_citas($citas);
    $_SESSION['ok_cita'] = 'Estado de cita actualizado.';
    header('Location: listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$indice = null;
foreach ($citas as $i => $item) {
    if ((int)($item['id'] ?? 0) === $id) {
        $indice = $i;
        break;
    }
}

if ($indice === null) {
    $_SESSION['error_cita'] = 'Cita no encontrada.';
    header('Location: listar.php');
    exit;
}

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

if (!$errores && !hora_disponible_para_cita($fecha, $hora, $duracion, $citas, $id)) {
    $errores[] = 'La hora elegida no esta disponible para ese servicio.';
}

if ($errores) {
    $_SESSION['errores_cita_editar'] = $errores;
    $_SESSION['old_cita_editar'] = [
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
    header('Location: editar.php?id=' . $id);
    exit;
}

$citas[$indice]['cliente'] = $cliente;
$citas[$indice]['telefono'] = $telefono;
$citas[$indice]['correo'] = $correo;
$citas[$indice]['servicio_id'] = (int)$servicio['id'];
$citas[$indice]['servicio'] = (string)$servicio['nombre'];
$citas[$indice]['duracion'] = $duracion;
$citas[$indice]['empleado'] = $empleado;
$citas[$indice]['fecha'] = $fecha;
$citas[$indice]['hora'] = $hora;
$citas[$indice]['observaciones'] = $observaciones;
$citas[$indice]['estado'] = $estado;
$citas[$indice]['pago'] = $pago;

guardar_citas($citas);

$_SESSION['ok_cita'] = 'Cita actualizada correctamente.';
header('Location: listar.php');
exit;
