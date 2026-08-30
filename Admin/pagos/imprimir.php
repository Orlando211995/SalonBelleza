<?php

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

header('Location: ver.php?id=' . $id . '&print=1');
exit;
