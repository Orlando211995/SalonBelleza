<?php
session_start();

require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/../../Includes/promocion.php';
require_once __DIR__ . '/../../Includes/imagenes.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	header('Location: editar.php');
	exit;
}

$actual = promocion_obtener();

$datos = [
	'activa' => ($_POST['activa'] ?? '1') === '1',
	'titulo' => trim((string)($_POST['titulo'] ?? '')),
	'subtitulo' => trim((string)($_POST['subtitulo'] ?? '')),
	'texto' => trim((string)($_POST['texto'] ?? '')),
	'boton_texto' => trim((string)($_POST['boton_texto'] ?? '')),
	'boton_enlace' => trim((string)($_POST['boton_enlace'] ?? '')),
	'imagen' => $actual['imagen'],
];

$errores = [];

if ($datos['titulo'] === '') {
	$errores[] = 'El título es obligatorio.';
}

if ($datos['subtitulo'] === '') {
	$errores[] = 'La oferta es obligatoria.';
}

if (!promocion_enlace_valido($datos['boton_enlace'])) {
	$errores[] = 'El enlace del botón no es válido. Usa por ejemplo cita.php o una dirección que empiece con https://.';
}

if (!$errores && isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
	$resultado = procesarImagenSubida(
		$_FILES['imagen'],
		__DIR__ . '/../../uploads/promocion',
		'promo',
		['min_ancho' => 600, 'min_alto' => 300, 'max_ancho' => 1400, 'max_alto' => 1400, 'proporcion' => null]
	);

	if ($resultado['ok']) {
		$datos['imagen'] = '/uploads/promocion/' . $resultado['archivo'];
	} else {
		$errores[] = $resultado['error'];
	}
}

if ($errores) {
	$_SESSION['errores_promocion'] = $errores;
	$_SESSION['old_promocion'] = $datos;
	header('Location: editar.php');
	exit;
}

if (!promocion_guardar($datos)) {
	$_SESSION['errores_promocion'] = ['No se pudo guardar la promoción en la base de datos.'];
	$_SESSION['old_promocion'] = $datos;
	header('Location: editar.php');
	exit;
}

if ($datos['imagen'] !== $actual['imagen'] && strpos($actual['imagen'], '/uploads/promocion/') === 0) {
	$rutaAnterior = __DIR__ . '/../../' . ltrim($actual['imagen'], '/');
	if (is_file($rutaAnterior)) {
		unlink($rutaAnterior);
	}
}

$_SESSION['ok_promocion'] = 'Promoción actualizada correctamente.';
header('Location: editar.php');
exit;
