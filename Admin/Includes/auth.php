<?php

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

if (empty($_SESSION['admin_auth'])) {
	$destino = '/Admin/login/login.php';
	$actual = $_SERVER['REQUEST_URI'] ?? '';
	if ($actual !== '') {
		$destino .= '?next=' . urlencode($actual);
	}

	if (!headers_sent()) {
		header('Location: ' . $destino);
	} else {
		echo '<script>window.location.href=' . json_encode($destino) . ';</script>';
		echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') . '"></noscript>';
	}
	exit;
}
