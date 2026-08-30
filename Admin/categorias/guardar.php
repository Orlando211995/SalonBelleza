<?php
session_start();

require_once __DIR__ . '/_data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$tipo = trim($_POST['tipo'] ?? '');
$estado = trim($_POST['estado'] ?? 'Activo');

$tiposValidos = ['Producto', 'Servicio', 'Ambos'];
$errores = [];

if ($nombre === '') {
    $errores[] = 'El nombre de la categoria es obligatorio.';
}

if ($descripcion === '') {
    $errores[] = 'La descripcion es obligatoria.';
}

if (!in_array($tipo, $tiposValidos, true)) {
    $errores[] = 'Debes seleccionar un tipo valido.';
}

if ($estado !== 'Activo' && $estado !== 'Inactivo') {
    $errores[] = 'El estado no es valido.';
}

if (categorias_existe_nombre($nombre)) {
    $errores[] = 'Ya existe una categoria con ese nombre.';
}

if ($errores) {
    $_SESSION['errores_categoria'] = $errores;
    $_SESSION['old_categoria'] = [
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'tipo' => $tipo,
        'estado' => $estado,
    ];
    header('Location: agregar.php');
    exit;
}

if (!categorias_insertar([
    'nombre' => $nombre,
    'descripcion' => $descripcion,
    'tipo' => $tipo,
    'estado' => $estado,
])) {
    $_SESSION['error_categoria'] = 'No se pudo guardar la categoria en la base de datos.';
    header('Location: listar.php');
    exit;
}

$_SESSION['ok_categoria'] = 'Categoria agregada correctamente.';
header('Location: listar.php');
exit;
