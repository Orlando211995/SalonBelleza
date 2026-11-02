<?php

const IMG_MAX_BYTES = 5 * 1024 * 1024;

/**
 * Valida y normaliza una imagen subida desde el admin.
 *
 * $opciones: min_ancho, min_alto, max_ancho, max_alto, proporcion (ancho/alto, null = conservar).
 * Devuelve ['ok' => true, 'archivo' => nombre] o ['ok' => false, 'error' => mensaje].
 */
function procesarImagenSubida(array $archivo, string $carpetaDestino, string $prefijo, array $opciones): array
{
	$tmp = (string)($archivo['tmp_name'] ?? '');
	if ($tmp === '' || !is_uploaded_file($tmp)) {
		return ['ok' => false, 'error' => 'No se recibio la imagen correctamente.'];
	}

	if ((int)($archivo['size'] ?? 0) > IMG_MAX_BYTES) {
		return ['ok' => false, 'error' => 'La imagen no debe pesar mas de 5 MB.'];
	}

	$info = @getimagesize($tmp);
	$tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
	if (!$info || !isset($tipos[$info[2]])) {
		return ['ok' => false, 'error' => 'La imagen debe ser JPG, PNG o WEBP valida.'];
	}

	[$ancho, $alto, $tipo] = [$info[0], $info[1], $info[2]];
	$minAncho = (int)$opciones['min_ancho'];
	$minAlto = (int)$opciones['min_alto'];
	if ($ancho < $minAncho || $alto < $minAlto) {
		return ['ok' => false, 'error' => "La imagen es muy pequena ({$ancho}x{$alto}px). Minimo {$minAncho}x{$minAlto}px."];
	}

	if (!is_dir($carpetaDestino)) {
		mkdir($carpetaDestino, 0775, true);
	}

	$extension = $tipos[$tipo];
	$nombre = $prefijo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
	$destino = rtrim($carpetaDestino, '/\\') . '/' . $nombre;

	$proporcion = $opciones['proporcion'] ?? null;
	$maxAncho = (int)$opciones['max_ancho'];
	$maxAlto = (int)$opciones['max_alto'];

	$necesitaAjuste = $ancho > $maxAncho || $alto > $maxAlto;
	if ($proporcion !== null && abs(($ancho / $alto) - $proporcion) > 0.02) {
		$necesitaAjuste = true;
	}

	if (!$necesitaAjuste || !function_exists('imagecreatetruecolor')) {
		return move_uploaded_file($tmp, $destino)
			? ['ok' => true, 'archivo' => $nombre]
			: ['ok' => false, 'error' => 'No se pudo guardar la imagen subida.'];
	}

	$origen = match ($tipo) {
		IMAGETYPE_JPEG => @imagecreatefromjpeg($tmp),
		IMAGETYPE_PNG => @imagecreatefrompng($tmp),
		default => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmp) : false,
	};
	if (!$origen) {
		return move_uploaded_file($tmp, $destino)
			? ['ok' => true, 'archivo' => $nombre]
			: ['ok' => false, 'error' => 'No se pudo guardar la imagen subida.'];
	}

	// Recorte centrado a la proporcion pedida (si aplica).
	$sx = 0;
	$sy = 0;
	$sw = $ancho;
	$sh = $alto;
	if ($proporcion !== null) {
		if (($ancho / $alto) > $proporcion) {
			$sw = (int)round($alto * $proporcion);
			$sx = (int)(($ancho - $sw) / 2);
		} else {
			$sh = (int)round($ancho / $proporcion);
			$sy = (int)(($alto - $sh) / 2);
		}
	}

	$escala = min(1, $maxAncho / $sw, $maxAlto / $sh);
	$nw = max(1, (int)round($sw * $escala));
	$nh = max(1, (int)round($sh * $escala));

	$lienzo = imagecreatetruecolor($nw, $nh);
	if ($tipo !== IMAGETYPE_JPEG) {
		imagealphablending($lienzo, false);
		imagesavealpha($lienzo, true);
		imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
	}
	imagecopyresampled($lienzo, $origen, 0, 0, $sx, $sy, $nw, $nh, $sw, $sh);

	$guardado = match ($tipo) {
		IMAGETYPE_JPEG => imagejpeg($lienzo, $destino, 88),
		IMAGETYPE_PNG => imagepng($lienzo, $destino, 6),
		default => imagewebp($lienzo, $destino, 88),
	};
	imagedestroy($origen);
	imagedestroy($lienzo);

	return $guardado
		? ['ok' => true, 'archivo' => $nombre]
		: ['ok' => false, 'error' => 'No se pudo procesar la imagen.'];
}

function opcionesImagenProducto(): array
{
	return ['min_ancho' => 400, 'min_alto' => 400, 'max_ancho' => 800, 'max_alto' => 800, 'proporcion' => null];
}

function opcionesImagenServicio(): array
{
	return ['min_ancho' => 600, 'min_alto' => 450, 'max_ancho' => 1200, 'max_alto' => 900, 'proporcion' => 4 / 3];
}
