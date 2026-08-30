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

if (!productos_actualizar($id, [
    'nombre' => (string)($producto['nombre'] ?? ''),
    'categoria' => (string)($producto['categoria'] ?? ''),
    'precio' => (float)($producto['precio'] ?? 0),
    'stock' => (int)($producto['stock'] ?? 0),
    'oferta' => !empty($producto['oferta']),
    'estado' => (string)($producto['estado'] ?? 'Activo'),
    'imagen' => '/Assets/img/productos/sin-imagen.jpg',
])) {
    $_SESSION['error_producto'] = 'No se pudo actualizar la imagen del producto.';
    header('Location: listar.php');
    exit;
}

$_SESSION['ok_producto'] = 'Imagen del producto eliminada correctamente.';
header('Location: editar.php?id=' . $id);
exit;
