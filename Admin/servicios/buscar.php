<?php

$q = trim($_GET['q'] ?? '');
$categoria = trim($_GET['categoria'] ?? '');

$params = [];
if ($q !== '') {
	$params['q'] = $q;
}
if ($categoria !== '') {
	$params['categoria'] = $categoria;
}

$destino = 'listar.php';
if ($params) {
	$destino .= '?' . http_build_query($params);
}

header('Location: ' . $destino);
exit;
<?php

// TODO: Implementar busqueda de servicios.
