<?php

function obtenerConexionSalon(): ?PDO
{
	static $pdoCache = false;

	if ($pdoCache !== false) {
		return $pdoCache;
	}

	$archivoEntorno = __DIR__ . '/../.env';
	if (is_file($archivoEntorno)) {
		foreach (file($archivoEntorno, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $linea) {
			$linea = trim($linea);
			if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
				continue;
			}

			[$clave, $valor] = array_map('trim', explode('=', $linea, 2));
			$valor = trim($valor, "\\\"'");
			if ($clave !== '') {
				putenv($clave . '=' . $valor);
			}
		}
	}

	$databaseUrl = trim((string)(getenv('DATABASE_URL') ?: ''));
	$driver = strtolower(getenv('DB_DRIVER') ?: 'mysql');
	$host = getenv('DB_HOST') ?: '127.0.0.1';
	$db = getenv('DB_NAME') ?: 'salon_belleza';
	$user = getenv('DB_USER') ?: 'root';
	$pass = getenv('DB_PASS') ?: '';
	$port = (int)(getenv('DB_PORT') ?: ($driver === 'pgsql' ? 5432 : 3306));

	if ($databaseUrl !== '') {
		$url = parse_url($databaseUrl);
		$driver = in_array(strtolower((string)($url['scheme'] ?? '')), ['postgres', 'postgresql'], true) ? 'pgsql' : $driver;
		$host = (string)($url['host'] ?? $host);
		$port = (int)($url['port'] ?? $port);
		$db = ltrim((string)($url['path'] ?? $db), '/');
		$user = rawurldecode((string)($url['user'] ?? $user));
		$pass = rawurldecode((string)($url['pass'] ?? $pass));
	}

	$dsn = $driver === 'pgsql'
		? sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=require', $host, $port, $db)
		: sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);

	try {
		$pdoCache = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		]);
	} catch (Throwable $e) {
		$pdoCache = null;
	}

	return $pdoCache;
}
