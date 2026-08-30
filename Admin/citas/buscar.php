<?php

$q = trim($_GET['q'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$fecha = trim($_GET['fecha'] ?? '');

$params = [];
if ($q !== '') {
    $params['q'] = $q;
}
if ($estado !== '') {
    $params['estado'] = $estado;
}
if ($fecha !== '') {
    $params['fecha'] = $fecha;
}

$destino = 'listar.php';
if ($params) {
    $destino .= '?' . http_build_query($params);
}

header('Location: ' . $destino);
exit;
