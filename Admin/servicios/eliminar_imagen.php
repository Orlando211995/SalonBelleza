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

if (!servicios_actualizar($id, [
	'nombre' => (string)($servicio['nombre'] ?? ''),
	'categoria' => (string)($servicio['categoria'] ?? ''),
	'descripcion' => (string)($servicio['descripcion'] ?? ''),
	'duracion' => (int)($servicio['duracion'] ?? 0),
	'precio' => (float)($servicio['precio'] ?? 0),
	'imagen' => '/Assets/img/servicios/cortes.jpg',
	'estado' => (string)($servicio['estado'] ?? 'Activo'),
])) {
	$_SESSION['error_servicio'] = 'No se pudo actualizar la imagen del servicio.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_servicio'] = 'Imagen del servicio eliminada correctamente.';
header('Location: editar.php?id=' . $id);
exit;
