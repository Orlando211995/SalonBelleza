<?php

function xlsx_columna(int $indice): string
{
	$letras = '';
	$indice++;
	while ($indice > 0) {
		$resto = ($indice - 1) % 26;
		$letras = chr(65 + $resto) . $letras;
		$indice = intdiv($indice - 1, 26);
	}
	return $letras;
}

function xlsx_texto(string $valor): string
{
	$valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $valor) ?? '';
	return htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function xlsx_longitud(string $texto): int
{
	return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

// Empaqueta archivos en un ZIP sin compresion (suficiente para un .xlsx).
function xlsx_zip(array $archivos): string
{
	$datos = '';
	$central = '';
	$total = 0;

	foreach ($archivos as $nombre => $contenido) {
		$crc = crc32($contenido);
		$largo = strlen($contenido);
		$cabecera = pack('VvvvvvVVVvv', 0x04034b50, 20, 0x0800, 0, 0, 0x21, $crc, $largo, $largo, strlen($nombre), 0) . $nombre;
		$central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, 0, 0, 0x21, $crc, $largo, $largo, strlen($nombre), 0, 0, 0, 0, 0, strlen($datos)) . $nombre;
		$datos .= $cabecera . $contenido;
		$total++;
	}

	$fin = pack('VvvvvVVv', 0x06054b50, 0, 0, $total, $total, strlen($central), strlen($datos), 0);
	return $datos . $central . $fin;
}

// Estilos (indices usados por xlsx_celda):
// 0 normal, 1 titulo, 2 encabezado, 3 texto, 4 entero, 5 moneda, 6 etiqueta,
// 7 total texto, 8 total entero, 9 total moneda, 10 titulo de seccion, 11 nota
function xlsx_estilos(): string
{
	return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;₡&quot;#,##0"/></numFmts>'
		. '<fonts count="6">'
		. '<font><sz val="11"/><name val="Calibri"/></font>'
		. '<font><b/><sz val="16"/><name val="Calibri"/></font>'
		. '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
		. '<font><b/><sz val="11"/><name val="Calibri"/></font>'
		. '<font><b/><sz val="13"/><name val="Calibri"/></font>'
		. '<font><i/><sz val="10"/><color rgb="FF6B7280"/><name val="Calibri"/></font>'
		. '</fonts>'
		. '<fills count="4">'
		. '<fill><patternFill patternType="none"/></fill>'
		. '<fill><patternFill patternType="gray125"/></fill>'
		. '<fill><patternFill patternType="solid"><fgColor rgb="FF1F2937"/><bgColor indexed="64"/></patternFill></fill>'
		. '<fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/><bgColor indexed="64"/></patternFill></fill>'
		. '</fills>'
		. '<borders count="2">'
		. '<border><left/><right/><top/><bottom/><diagonal/></border>'
		. '<border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right><top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border>'
		. '</borders>'
		. '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
		. '<cellXfs count="12">'
		. '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
		. '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
		. '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
		. '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
		. '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
		. '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
		. '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
		. '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
		. '<xf numFmtId="3" fontId="3" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>'
		. '<xf numFmtId="164" fontId="3" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1"/>'
		. '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
		. '<xf numFmtId="0" fontId="5" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
		. '</cellXfs>'
		. '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
		. '</styleSheet>';
}

function xlsx_celda(int $columna, int $fila, $valor, int $estilo): string
{
	$ref = xlsx_columna($columna) . $fila;
	if (is_int($valor) || is_float($valor)) {
		return '<c r="' . $ref . '" s="' . $estilo . '"><v>' . (is_float($valor) ? rtrim(rtrim(sprintf('%.4F', $valor), '0'), '.') : $valor) . '</v></c>';
	}
	if ($valor === '' || $valor === null) {
		return '<c r="' . $ref . '" s="' . $estilo . '"/>';
	}
	return '<c r="' . $ref . '" s="' . $estilo . '" t="inlineStr"><is><t xml:space="preserve">' . xlsx_texto((string)$valor) . '</t></is></c>';
}

function reporte_xlsx(array $reporte): string
{
	$filas = [];
	$anchos = [];
	$fila = 1;

	$agregar = static function (array $celdas) use (&$filas, &$fila): void {
		$filas[$fila] = $celdas;
		$fila++;
	};
	$medir = static function (int $columna, $texto) use (&$anchos): void {
		$largo = xlsx_longitud((string)$texto);
		$anchos[$columna] = max($anchos[$columna] ?? 0, $largo);
	};

	$agregar([[0, $reporte['titulo'], 1]]);
	$agregar([[0, 'Alfredo Salon Estudio CR', 6]]);
	$agregar([[0, 'Generado: ' . $reporte['generado'] . '  |  ' . $reporte['filtros'], 11]]);
	$agregar([]);

	if (!empty($reporte['resumen'])) {
		$agregar([[0, 'Resumen', 10]]);
		foreach ($reporte['resumen'] as [$etiqueta, $valor, $tipo]) {
			$estiloValor = $tipo === 'moneda' ? 5 : ($tipo === 'entero' ? 4 : 3);
			$agregar([[0, $etiqueta, 3], [1, $tipo === 'texto' ? (string)$valor : ($tipo === 'moneda' ? (float)$valor : (int)$valor), $estiloValor]]);
			$medir(0, $etiqueta);
			$medir(1, $tipo === 'texto' ? $valor : reporte_formatear($valor, $tipo));
		}
		$agregar([]);
	}

	foreach ($reporte['secciones'] as $seccion) {
		$agregar([[0, $seccion['titulo'], 10]]);

		$encabezado = [];
		foreach ($seccion['columnas'] as $i => [$nombre]) {
			$encabezado[] = [$i, $nombre, 2];
			$medir($i, $nombre);
		}
		$agregar($encabezado);

		if (!$seccion['filas']) {
			$agregar([[0, $seccion['vacio'], 11]]);
		}

		foreach ($seccion['filas'] as $datos) {
			$celdas = [];
			foreach ($seccion['columnas'] as $i => [, $tipo]) {
				$valor = $datos[$i] ?? '';
				$estilo = $tipo === 'moneda' ? 5 : ($tipo === 'entero' ? 4 : 3);
				if ($tipo === 'moneda') {
					$valor = (float)$valor;
				} elseif ($tipo === 'entero') {
					$valor = (int)$valor;
				}
				$celdas[] = [$i, $valor, $estilo];
				$medir($i, $tipo === 'moneda' ? reporte_formatear($valor, 'moneda') : $valor);
			}
			$agregar($celdas);
		}

		if ($seccion['total'] !== null && $seccion['filas']) {
			$celdas = [];
			foreach ($seccion['columnas'] as $i => [, $tipo]) {
				$valor = $seccion['total'][$i] ?? '';
				if ($valor === '' || $valor === null) {
					$celdas[] = [$i, '', 7];
				} elseif ($tipo === 'moneda') {
					$celdas[] = [$i, (float)$valor, 9];
				} elseif ($tipo === 'entero') {
					$celdas[] = [$i, (int)$valor, 8];
				} else {
					$celdas[] = [$i, (string)$valor, 7];
				}
			}
			$agregar($celdas);
		}
		$agregar([]);
	}

	$xmlFilas = '';
	foreach ($filas as $numero => $celdas) {
		$xmlFilas .= '<row r="' . $numero . '">';
		foreach ($celdas as [$columna, $valor, $estilo]) {
			$xmlFilas .= xlsx_celda($columna, $numero, $valor, $estilo);
		}
		$xmlFilas .= '</row>';
	}

	$columnas = '';
	ksort($anchos);
	foreach ($anchos as $i => $largo) {
		$ancho = min(60, max(12, $largo + 4));
		$columnas .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $ancho . '" customWidth="1"/>';
	}

	$hoja = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
		. '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
		. '<sheetViews><sheetView workbookViewId="0" showGridLines="0"/></sheetViews>'
		. '<cols>' . $columnas . '</cols>'
		. '<sheetData>' . $xmlFilas . '</sheetData>'
		. '<pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
		. '<pageSetup orientation="landscape" fitToHeight="0"/>'
		. '</worksheet>';

	$nombreHoja = xlsx_texto(substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '', $reporte['titulo']) ?? 'Reporte', 0, 31));

	return xlsx_zip([
		'[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
			. '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
			. '<Default Extension="xml" ContentType="application/xml"/>'
			. '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
			. '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
			. '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
			. '</Types>',
		'_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
			. '</Relationships>',
		'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
			. '<sheets><sheet name="' . $nombreHoja . '" sheetId="1" r:id="rId1"/></sheets>'
			. '</workbook>',
		'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
			. '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
			. '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
			. '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
			. '</Relationships>',
		'xl/styles.xml' => xlsx_estilos(),
		'xl/worksheets/sheet1.xml' => $hoja,
	]);
}
