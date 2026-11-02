<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: listar.php');
	exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : -1;
$stock = isset($_POST['stock']) ? (int)$_POST['stock'] : -1;
$oferta = trim($_POST['oferta'] ?? 'No');
$estado = trim($_POST['estado'] ?? 'Activo');

$errores = [];

if ($nombre === '') {
	$errores[] = 'El nombre del producto es obligatorio.';
}

if ($descripcion === '') {
	$errores[] = 'La descripción del producto es obligatoria.';
}

$categoriasValidas = productos_categorias_validas();
if (!in_array($categoria, $categoriasValidas, true)) {
	$errores[] = 'Debes seleccionar una categoria valida.';
}

if ($precio < 0) {
	$errores[] = 'El precio debe ser un numero mayor o igual a 0.';
}

if ($stock < 0) {
	$errores[] = 'El stock debe ser un numero mayor o igual a 0.';
}

if ($oferta !== 'Si' && $oferta !== 'No') {
	$errores[] = 'El campo oferta no es valido.';
}

if ($estado !== 'Activo' && $estado !== 'Inactivo') {
	$errores[] = 'El campo estado no es valido.';
}

if (!isset($_FILES['imagen']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
	$errores[] = 'Debes subir una imagen del producto.';
}

$imagenUrl = '';

if (!$errores) {
	require_once __DIR__ . '/../../Includes/imagenes.php';
	$resultado = procesarImagenSubida($_FILES['imagen'], __DIR__ . '/../../uploads/productos', 'prod', opcionesImagenProducto());
	if ($resultado['ok']) {
		$imagenUrl = '/uploads/productos/' . $resultado['archivo'];
	} else {
		$errores[] = $resultado['error'];
	}
}

if ($errores) {
	$_SESSION['errores_producto'] = $errores;
	$_SESSION['old_producto'] = [
		'nombre' => $nombre,
		'descripcion' => $descripcion,
		'categoria' => $categoria,
		'precio' => $precio >= 0 ? (string)$precio : '',
		'stock' => $stock >= 0 ? (string)$stock : '',
		'oferta' => $oferta,
		'estado' => $estado,
	];
	header('Location: agregar.php');
	exit;
}

if (!productos_insertar([
	'nombre' => $nombre,
	'descripcion' => $descripcion,
	'categoria' => $categoria,
	'precio' => $precio,
	'stock' => $stock,
	'oferta' => $oferta === 'Si',
	'estado' => $estado,
	'imagen' => $imagenUrl,
])) {
	$_SESSION['error_producto'] = 'No se pudo guardar el producto en la base de datos.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_producto'] = 'Producto agregado correctamente.';

header('Location: listar.php');
exit;
