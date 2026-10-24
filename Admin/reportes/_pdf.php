<?php

class ReportePdf
{
	private const ANCHO = 842.0;
	private const ALTO = 595.0;
	private const MARGEN = 36.0;
	private const ALTO_FILA = 15.0;
	private const TAM_TABLA = 8.5;

	// Anchos Helvetica (1/1000 em) para los caracteres 32..126.
	private const ANCHOS = [
		278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
		556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
		1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
		667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
		333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
		556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
	];

	private array $paginas = [];
	private string $contenido = '';
	private float $y = 0.0;
	private string $titulo;
	private string $marca;

	public function __construct(string $titulo, string $marca)
	{
		$this->titulo = $titulo;
		$this->marca = $marca;
		$this->nuevaPagina();
	}

	private static function codificar(string $texto): string
	{
		$texto = preg_replace('/[\x00-\x1F]+/', ' ', $texto) ?? '';
		if (function_exists('mb_convert_encoding')) {
			$convertido = @mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');
		} elseif (function_exists('iconv')) {
			$convertido = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $texto);
		} else {
			$convertido = utf8_decode($texto);
		}
		return $convertido === false || $convertido === null ? $texto : $convertido;
	}

	private static function escapar(string $texto): string
	{
		return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
	}

	private static function medir(string $codificado, float $tam, bool $negrita): float
	{
		$ancho = 0;
		$largo = strlen($codificado);
		for ($i = 0; $i < $largo; $i++) {
			$codigo = ord($codificado[$i]);
			$ancho += ($codigo >= 32 && $codigo <= 126) ? self::ANCHOS[$codigo - 32] : 556;
		}
		return $ancho * $tam / 1000 * ($negrita ? 1.06 : 1.0);
	}

	private static function recortar(string $codificado, float $maximo, float $tam, bool $negrita): string
	{
		if (self::medir($codificado, $tam, $negrita) <= $maximo) {
			return $codificado;
		}
		$puntos = '...';
		while ($codificado !== '' && self::medir($codificado . $puntos, $tam, $negrita) > $maximo) {
			$codificado = substr($codificado, 0, -1);
		}
		return $codificado . $puntos;
	}

	private function nuevaPagina(): void
	{
		if ($this->contenido !== '') {
			$this->paginas[] = $this->contenido;
		}
		$this->contenido = '';
		$this->y = self::MARGEN;

		$this->texto(self::MARGEN, $this->y + 8, $this->marca, 9, false, [107, 114, 128]);
		$this->texto(self::ANCHO - self::MARGEN, $this->y + 8, $this->titulo, 9, true, [107, 114, 128], 'der');
		$this->linea(self::MARGEN, $this->y + 14, self::ANCHO - self::MARGEN, $this->y + 14, [209, 213, 219]);
		$this->y += 28;
	}

	private function color(array $rgb): string
	{
		return sprintf('%.3F %.3F %.3F', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255);
	}

	private function texto(float $x, float $yArriba, string $texto, float $tam, bool $negrita = false, array $rgb = [17, 24, 39], string $alinear = 'izq'): void
	{
		$codificado = self::codificar($texto);
		if ($alinear === 'der') {
			$x -= self::medir($codificado, $tam, $negrita);
		}
		$this->contenido .= sprintf(
			"BT /%s %.2F Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
			$negrita ? 'F2' : 'F1',
			$tam,
			$this->color($rgb),
			$x,
			self::ALTO - $yArriba,
			self::escapar($codificado)
		);
	}

	private function textoRecortado(float $x, float $yArriba, string $texto, float $maximo, float $tam, bool $negrita, array $rgb, string $alinear = 'izq'): void
	{
		$codificado = self::recortar(self::codificar($texto), $maximo, $tam, $negrita);
		if ($alinear === 'der') {
			$x -= self::medir($codificado, $tam, $negrita);
		}
		$this->contenido .= sprintf(
			"BT /%s %.2F Tf %s rg %.2F %.2F Td (%s) Tj ET\n",
			$negrita ? 'F2' : 'F1',
			$tam,
			$this->color($rgb),
			$x,
			self::ALTO - $yArriba,
			self::escapar($codificado)
		);
	}

	private function rectangulo(float $x, float $yArriba, float $ancho, float $alto, array $relleno, ?array $borde = null): void
	{
		$this->contenido .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", $this->color($relleno), $x, self::ALTO - $yArriba - $alto, $ancho, $alto);
		if ($borde !== null) {
			$this->contenido .= sprintf("%s RG 0.5 w %.2F %.2F %.2F %.2F re S\n", $this->color($borde), $x, self::ALTO - $yArriba - $alto, $ancho, $alto);
		}
	}

	private function linea(float $x1, float $y1, float $x2, float $y2, array $rgb): void
	{
		$this->contenido .= sprintf("%s RG 0.5 w %.2F %.2F m %.2F %.2F l S\n", $this->color($rgb), $x1, self::ALTO - $y1, $x2, self::ALTO - $y2);
	}

	private function asegurarEspacio(float $alto): bool
	{
		if ($this->y + $alto > self::ALTO - 44) {
			$this->nuevaPagina();
			return true;
		}
		return false;
	}

	public function encabezado(string $titulo, string $subtitulo): void
	{
		$this->texto(self::MARGEN, $this->y + 14, $titulo, 18, true);
		$this->texto(self::MARGEN, $this->y + 30, $subtitulo, 9, false, [107, 114, 128]);
		$this->y += 44;
	}

	public function resumen(array $items): void
	{
		if (!$items) {
			return;
		}
		$porFila = 4;
		$separacion = 10.0;
		$usable = self::ANCHO - 2 * self::MARGEN;
		$ancho = ($usable - $separacion * ($porFila - 1)) / $porFila;
		$alto = 42.0;

		foreach (array_chunk($items, $porFila) as $grupo) {
			$this->asegurarEspacio($alto + 8);
			foreach ($grupo as $i => [$etiqueta, $valor, $tipo]) {
				$x = self::MARGEN + $i * ($ancho + $separacion);
				$this->rectangulo($x, $this->y, $ancho, $alto, [249, 250, 251], [209, 213, 219]);
				$this->textoRecortado($x + 8, $this->y + 15, $etiqueta, $ancho - 16, 8, false, [107, 114, 128]);
				$this->textoRecortado($x + 8, $this->y + 33, reporte_formatear($valor, $tipo, 'CRC '), $ancho - 16, 13, true, [17, 24, 39]);
			}
			$this->y += $alto + 8;
		}
		$this->y += 6;
	}

	private function anchosColumnas(array $seccion): array
	{
		$usable = self::ANCHO - 2 * self::MARGEN;
		$naturales = [];
		foreach ($seccion['columnas'] as $i => [$nombre]) {
			$maximo = self::medir(self::codificar($nombre), self::TAM_TABLA, true);
			foreach (array_merge($seccion['filas'], $seccion['total'] !== null ? [$seccion['total']] : []) as $fila) {
				$texto = reporte_formatear($fila[$i] ?? '', $seccion['columnas'][$i][1], 'CRC ');
				$maximo = max($maximo, self::medir(self::codificar($texto), self::TAM_TABLA, false));
			}
			$naturales[$i] = min($maximo, 260.0) + 14;
		}

		$suma = array_sum($naturales);
		$objetivo = $suma > $usable ? $usable : min($usable, max($suma * 1.25, 280.0));
		$factor = $objetivo / $suma;
		return array_map(static fn($ancho) => $ancho * $factor, $naturales);
	}

	private function filaTabla(array $seccion, array $anchos, array $datos, array $estilo): void
	{
		$x = self::MARGEN;
		$tam = self::TAM_TABLA;
		foreach ($seccion['columnas'] as $i => [, $tipo]) {
			$ancho = $anchos[$i];
			$this->rectangulo($x, $this->y, $ancho, self::ALTO_FILA, $estilo['fondo'], [209, 213, 219]);
			$texto = $estilo['encabezado'] ? (string)$datos[$i] : reporte_formatear($datos[$i] ?? '', $tipo, 'CRC ');
			$derecha = $tipo !== 'texto';
			$this->textoRecortado(
				$derecha ? $x + $ancho - 6 : $x + 6,
				$this->y + 10.5,
				$texto,
				$ancho - 12,
				$tam,
				$estilo['negrita'],
				$estilo['color'],
				$derecha ? 'der' : 'izq'
			);
			$x += $ancho;
		}
		$this->y += self::ALTO_FILA;
	}

	public function seccion(array $seccion): void
	{
		$this->asegurarEspacio(self::ALTO_FILA * 3 + 20);
		$this->texto(self::MARGEN, $this->y + 10, $seccion['titulo'], 11, true);
		$this->y += 18;

		$anchos = $this->anchosColumnas($seccion);
		$encabezado = array_map(static fn($c) => $c[0], $seccion['columnas']);
		$estiloEncabezado = ['fondo' => [31, 41, 55], 'color' => [255, 255, 255], 'negrita' => true, 'encabezado' => true];

		$this->filaTabla($seccion, $anchos, $encabezado, $estiloEncabezado);

		if (!$seccion['filas']) {
			$this->texto(self::MARGEN + 6, $this->y + 14, $seccion['vacio'], 9, false, [107, 114, 128]);
			$this->y += 26;
			return;
		}

		foreach ($seccion['filas'] as $n => $fila) {
			if ($this->asegurarEspacio(self::ALTO_FILA * 2)) {
				$this->filaTabla($seccion, $anchos, $encabezado, $estiloEncabezado);
			}
			$fondo = $n % 2 === 0 ? [255, 255, 255] : [249, 250, 251];
			$this->filaTabla($seccion, $anchos, $fila, ['fondo' => $fondo, 'color' => [17, 24, 39], 'negrita' => false, 'encabezado' => false]);
		}

		if ($seccion['total'] !== null) {
			$this->asegurarEspacio(self::ALTO_FILA * 2);
			$this->filaTabla($seccion, $anchos, $seccion['total'], ['fondo' => [243, 244, 246], 'color' => [17, 24, 39], 'negrita' => true, 'encabezado' => false]);
		}
		$this->y += 18;
	}

	public function generar(string $pie): string
	{
		$paginas = $this->paginas;
		$paginas[] = $this->contenido;
		$total = count($paginas);

		foreach ($paginas as $i => $contenido) {
			$this->contenido = '';
			$this->texto(self::MARGEN, self::ALTO - 24, $pie, 8, false, [107, 114, 128]);
			$this->texto(self::ANCHO - self::MARGEN, self::ALTO - 24, 'Pagina ' . ($i + 1) . ' de ' . $total, 8, false, [107, 114, 128], 'der');
			$paginas[$i] = $contenido . $this->contenido;
		}

		$objetos = [
			1 => '<< /Type /Catalog /Pages 2 0 R >>',
			3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
			4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
		];
		$hijos = [];
		foreach ($paginas as $i => $contenido) {
			$idContenido = 5 + $i * 2;
			$idPagina = 6 + $i * 2;
			$objetos[$idContenido] = "<< /Length " . strlen($contenido) . " >>\nstream\n" . $contenido . "endstream";
			$objetos[$idPagina] = sprintf(
				'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
				self::ANCHO,
				self::ALTO,
				$idContenido
			);
			$hijos[] = $idPagina . ' 0 R';
		}
		$objetos[2] = '<< /Type /Pages /Kids [' . implode(' ', $hijos) . '] /Count ' . $total . ' >>';
		ksort($objetos);

		$salida = "%PDF-1.4\n";
		$posiciones = [];
		foreach ($objetos as $id => $cuerpo) {
			$posiciones[$id] = strlen($salida);
			$salida .= $id . " 0 obj\n" . $cuerpo . "\nendobj\n";
		}

		$inicioXref = strlen($salida);
		$ultimo = max(array_keys($objetos));
		$salida .= "xref\n0 " . ($ultimo + 1) . "\n0000000000 65535 f \n";
		for ($id = 1; $id <= $ultimo; $id++) {
			$salida .= sprintf("%010d 00000 n \n", $posiciones[$id]);
		}
		$salida .= "trailer\n<< /Size " . ($ultimo + 1) . " /Root 1 0 R >>\nstartxref\n" . $inicioXref . "\n%%EOF";
		return $salida;
	}
}

function reporte_pdf(array $reporte): string
{
	$pdf = new ReportePdf($reporte['titulo'], 'Alfredo Salon Estudio CR');
	$pdf->encabezado($reporte['titulo'], 'Generado: ' . $reporte['generado'] . '   |   ' . $reporte['filtros']);
	$pdf->resumen($reporte['resumen']);
	foreach ($reporte['secciones'] as $seccion) {
		$pdf->seccion($seccion);
	}
	return $pdf->generar('Generado el ' . $reporte['generado']);
}
