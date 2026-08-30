<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$producto = productos_buscar_por_id($id);

if (!$producto) {
	$_SESSION['error_producto'] = 'Producto no encontrado.';
	header('Location: listar.php');
	exit;
}

$imagen = $producto['imagen'] ?? '';
if (is_string($imagen) && strpos($imagen, '/uploads/productos/') === 0) {
	$rutaImagen = __DIR__ . '/../../' . ltrim($imagen, '/');
	if (is_file($rutaImagen)) {
		unlink($rutaImagen);
	}
}

if (!productos_eliminar($id)) {
	$_SESSION['error_producto'] = 'No se pudo eliminar el producto en la base de datos.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_producto'] = 'Producto eliminado correctamente.';
header('Location: listar.php');
exit;
