<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: listar.php');
	exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$servicioActual = servicios_buscar_por_id($id);

if (!$servicioActual) {
	$_SESSION['error_servicio'] = 'Servicio no encontrado.';
	header('Location: listar.php');
	exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$duracion = isset($_POST['duracion']) ? (int)$_POST['duracion'] : 0;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : -1;
$estado = trim($_POST['estado'] ?? 'Activo');

$categoriasValidas = ['Cortes', 'Coloracion', 'Tratamientos', 'Barberia', 'Manicure', 'Pedicure', 'Peinados', 'Maquillaje', 'Faciales', 'Depilacion'];
$errores = [];

if ($nombre === '') {
	$errores[] = 'El nombre del servicio es obligatorio.';
}

if (!in_array($categoria, $categoriasValidas, true)) {
	$errores[] = 'Debes seleccionar una categoria valida.';
}

if ($descripcion === '') {
	$errores[] = 'La descripcion es obligatoria.';
}

if ($duracion <= 0) {
	$errores[] = 'La duracion debe ser mayor a 0 minutos.';
}

if ($precio < 0) {
	$errores[] = 'El precio debe ser un numero mayor o igual a 0.';
}

if ($estado !== 'Activo' && $estado !== 'Inactivo') {
	$errores[] = 'El estado no es valido.';
}

$imagenUrl = $servicioActual['imagen'] ?? '';

if (isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
	require_once __DIR__ . '/../../Includes/imagenes.php';
	$resultado = procesarImagenSubida($_FILES['imagen'], __DIR__ . '/../../uploads/servicios', 'srv', opcionesImagenServicio());
	$nombreArchivo = $resultado['archivo'] ?? '';

	if (!$resultado['ok']) {
		$errores[] = $resultado['error'];
	} else {
		$anterior = $servicioActual['imagen'] ?? '';
		if (is_string($anterior) && strpos($anterior, '/uploads/servicios/') === 0) {
			$rutaAnterior = __DIR__ . '/../../' . ltrim($anterior, '/');
			if (is_file($rutaAnterior)) {
				unlink($rutaAnterior);
			}
		}
		$imagenUrl = '/uploads/servicios/' . $nombreArchivo;
	}
}

if ($errores) {
	$_SESSION['errores_servicio_editar'] = $errores;
	$_SESSION['old_servicio_editar'] = [
		'nombre' => $nombre,
		'categoria' => $categoria,
		'descripcion' => $descripcion,
		'duracion' => (string)$duracion,
		'precio' => $precio >= 0 ? (string)$precio : '',
		'estado' => $estado,
	];
	header('Location: editar.php?id=' . $id);
	exit;
}

if (!servicios_actualizar($id, [
	'nombre' => $nombre,
	'categoria' => $categoria,
	'descripcion' => $descripcion,
	'duracion' => $duracion,
	'precio' => $precio,
	'imagen' => $imagenUrl,
	'estado' => $estado,
])) {
	$_SESSION['error_servicio'] = 'No se pudo actualizar el servicio en la base de datos.';
	header('Location: listar.php');
	exit;
}

$_SESSION['ok_servicio'] = 'Servicio actualizado correctamente.';
header('Location: listar.php');
exit;
