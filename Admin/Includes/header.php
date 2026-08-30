<?php
$scriptActual = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$rutasPublicas = [
	'/Admin/login/login.php',
	'/Admin/login/logout.php',
	'/Admin/login/generar_hash.php',
	'/Admin/login/forgot_password.php',
	'/Admin/login/reset_password.php',
];

if (!in_array($scriptActual, $rutasPublicas, true)) {
	require_once(__DIR__ . '/auth.php');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Panel Administrador | Alfredo Salon Estudio CR</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="/Assets/css/admin.css">
</head>

<body>
