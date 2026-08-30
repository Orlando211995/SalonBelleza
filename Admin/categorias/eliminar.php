<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$categoria = categorias_buscar_por_id($id);

if (!$categoria) {
    $_SESSION['error_categoria'] = 'Categoria no encontrada.';
    header('Location: listar.php');
    exit;
}

if (!categorias_eliminar($id)) {
    $_SESSION['error_categoria'] = 'No se pudo eliminar la categoria. Puede estar en uso por productos.';
    header('Location: listar.php');
    exit;
}

$_SESSION['ok_categoria'] = 'Categoria eliminada correctamente.';
header('Location: listar.php');
exit;
