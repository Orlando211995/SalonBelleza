<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: listar.php');
	exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$productoActual = productos_buscar_por_id($id);

if (!$productoActual) {
	$_SESSION['error_producto'] = 'Producto no encontrado.';
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

$categoriasValidas = ['Shampoo', 'Acondicionadores', 'Mascarillas', 'Tintes'];
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

$imagenUrl = $productoActual['imagen'] ?? '';

if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
	$tmpName = $_FILES['imagen']['tmp_name'];
	$nombreOriginal = $_FILES['imagen']['name'] ?? '';
	$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
	$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

	if (!in_array($extension, $permitidas, true)) {
		$errores[] = 'La imagen debe ser JPG, JPEG, PNG o WEBP.';
	} else {
		$carpetaDestino = __DIR__ . '/../../uploads/productos';
		if (!is_dir($carpetaDestino)) {
			mkdir($carpetaDestino, 0777, true);
		}

		$nombreArchivo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
		$rutaDestino = $carpetaDestino . '/' . $nombreArchivo;

		if (!move_uploaded_file($tmpName, $rutaDestino)) {
			$errores[] = 'No se pudo guardar la nueva imagen.';
		} else {
			$imagenAnterior = $productoActual['imagen'] ?? '';
			if (is_string($imagenAnterior) && strpos($imagenAnterior, '/uploads/productos/') === 0) {
				$rutaAnterior = __DIR__ . '/../../' . ltrim($imagenAnterior, '/');
				if (is_file($rutaAnterior)) {
					unlink($rutaAnterior);
				}
			}

			$imagenUrl = '/uploads/productos/' . $nombreArchivo;
		}
	}
}

if ($errores) {
	$_SESSION['errores_producto_editar'] = $errores;
	$_SESSION['old_producto_editar'] = [
		'nombre' => $nombre,
		'descripcion' => $descripcion,
		'categoria' => $categoria,
		'precio' => $precio >= 0 ? (string)$precio : '',
		'stock' => $stock >= 0 ? (string)$stock : '',
		'oferta' => $oferta,
		'estado' => $estado,
	];
	header('Location: editar.php?id=' . $id);
	exit;
}

if (!productos_actualizar($id, [
	'nombre' => $nombre,
	'descripcion' => $descripcion,
	'categoria' => $categoria,
	'precio' => $precio,
	'stock' => $stock,
	'oferta' => $oferta === 'Si',
	'estado' => $estado,
	'imagen' => $imagenUrl,
])) {
	$_SESSION['error_producto'] = 'No se pudo actualizar el producto en la base de datos.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_producto'] = 'Producto actualizado correctamente.';
header('Location: listar.php');
exit;
