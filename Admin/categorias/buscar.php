<?php

$q = trim($_GET['q'] ?? '');
$tipo = trim($_GET['tipo'] ?? '');

$params = [];
if ($q !== '') {
    $params['q'] = $q;
}
if ($tipo !== '') {
    $params['tipo'] = $tipo;
}

$destino = 'listar.php';
if ($params) {
    $destino .= '?' . http_build_query($params);
}

header('Location: ' . $destino);
exit;
