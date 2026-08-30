<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicio = servicios_buscar_por_id($id);

if (!$servicio) {
	$_SESSION['error_servicio'] = 'Servicio no encontrado.';
	header('Location: listar.php');
	exit;
}

$imagen = $servicio['imagen'] ?? '';
if (is_string($imagen) && strpos($imagen, '/uploads/servicios/') === 0) {
	$rutaImagen = __DIR__ . '/../../' . ltrim($imagen, '/');
	if (is_file($rutaImagen)) {
		unlink($rutaImagen);
	}
}

if (!servicios_eliminar($id)) {
	$_SESSION['error_servicio'] = 'No se pudo eliminar el servicio en la base de datos.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_servicio'] = 'Servicio eliminado correctamente.';
header('Location: listar.php');
exit;
