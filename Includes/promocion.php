<?php

require_once __DIR__ . '/conexion.php';

function promocion_por_defecto(): array
{
	return [
		'activa' => true,
		'titulo' => 'Promoción del Mes',
		'subtitulo' => '20% de descuento en Balayage',
		'texto' => 'Agenda tu cita antes de finalizar el mes.',
		'boton_texto' => 'Reservar Ahora',
		'boton_enlace' => 'cita.php',
		'imagen' => '/Assets/img/promociones/promociones.jpg',
	];
}

function promocion_asegurar_tabla(PDO $pdo): void
{
	$pdo->exec(
		'CREATE TABLE IF NOT EXISTS promocion_mes (
			id INTEGER PRIMARY KEY,
			activa BOOLEAN NOT NULL DEFAULT TRUE,
			titulo VARCHAR(160) NOT NULL,
			subtitulo VARCHAR(200) NOT NULL DEFAULT \'\',
			texto VARCHAR(400) NOT NULL DEFAULT \'\',
			boton_texto VARCHAR(60) NOT NULL DEFAULT \'\',
			boton_enlace VARCHAR(255) NOT NULL DEFAULT \'\',
			imagen VARCHAR(255) NOT NULL DEFAULT \'\'
		)'
	);
}

function promocion_obtener(): array
{
	$defecto = promocion_por_defecto();
	$pdo = obtenerConexionSalon();
	if (!$pdo) {
		return $defecto;
	}

	try {
		promocion_asegurar_tabla($pdo);
		$fila = $pdo->query('SELECT * FROM promocion_mes WHERE id = 1')->fetch();
	} catch (Throwable $e) {
		return $defecto;
	}

	if (!$fila) {
		return $defecto;
	}

	return [
		'activa' => !empty($fila['activa']),
		'titulo' => corregirMojibake((string)$fila['titulo']),
		'subtitulo' => corregirMojibake((string)$fila['subtitulo']),
		'texto' => corregirMojibake((string)$fila['texto']),
		'boton_texto' => corregirMojibake((string)$fila['boton_texto']),
		'boton_enlace' => (string)$fila['boton_enlace'],
		'imagen' => (string)$fila['imagen'] !== '' ? (string)$fila['imagen'] : $defecto['imagen'],
	];
}

function promocion_guardar(array $datos): bool
{
	$pdo = obtenerConexionSalon();
	if (!$pdo) {
		return false;
	}

	try {
		promocion_asegurar_tabla($pdo);
		$sql = 'INSERT INTO promocion_mes (id, activa, titulo, subtitulo, texto, boton_texto, boton_enlace, imagen)
			VALUES (1, :activa, :titulo, :subtitulo, :texto, :boton_texto, :boton_enlace, :imagen)
			ON CONFLICT (id) DO UPDATE SET activa = EXCLUDED.activa, titulo = EXCLUDED.titulo, subtitulo = EXCLUDED.subtitulo,
				texto = EXCLUDED.texto, boton_texto = EXCLUDED.boton_texto, boton_enlace = EXCLUDED.boton_enlace, imagen = EXCLUDED.imagen';
		$pdo->prepare($sql)->execute([
			'activa' => !empty($datos['activa']) ? 'true' : 'false',
			'titulo' => $datos['titulo'],
			'subtitulo' => $datos['subtitulo'],
			'texto' => $datos['texto'],
			'boton_texto' => $datos['boton_texto'],
			'boton_enlace' => $datos['boton_enlace'],
			'imagen' => $datos['imagen'],
		]);
		return true;
	} catch (Throwable $e) {
		return false;
	}
}

/** Acepta rutas internas (cita.php, /cita.php) y enlaces http(s); rechaza otros esquemas como javascript:. */
function promocion_enlace_valido(string $enlace): bool
{
	if ($enlace === '') {
		return true;
	}

	if (preg_match('#^https?://[^\s]+$#i', $enlace)) {
		return true;
	}

	return !str_starts_with($enlace, '//')
		&& (bool)preg_match('#^/?[A-Za-z0-9_\-./?=&\#]+$#', $enlace)
		&& !preg_match('#^[A-Za-z][A-Za-z0-9+.\-]*:#', $enlace);
}
