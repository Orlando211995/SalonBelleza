<?php
require_once __DIR__ . '/../pedidos/_data.php';
require_once __DIR__ . '/../productos/_data.php';
require_once __DIR__ . '/../servicios/_data.php';
require_once __DIR__ . '/../citas/_agenda.php';
require_once __DIR__ . '/../pagos/_data.php';

function reporte_titulos(): array
{
	return [
		'ventas' => 'Reporte de Ventas',
		'productos' => 'Reporte de Productos',
		'servicios' => 'Reporte de Servicios',
		'citas' => 'Reporte de Citas',
		'pagos' => 'Reporte de Pagos',
		'inventario' => 'Reporte de Inventario',
	];
}

function reporte_valido(string $reporte): bool
{
	return array_key_exists($reporte, reporte_titulos());
}

function reporte_formatear($valor, string $tipo, string $simbolo = '₡'): string
{
	if ($valor === '' || $valor === null) {
		return '';
	}
	if ($tipo === 'moneda') {
		return $simbolo . number_format((float)$valor, 0, ',', '.');
	}
	if ($tipo === 'entero') {
		return number_format((int)$valor, 0, ',', '.');
	}
	return (string)$valor;
}

function reporte_fecha_texto(string $texto): ?DateTimeImmutable
{
	$texto = trim($texto);
	if ($texto === '') {
		return null;
	}
	try {
		return new DateTimeImmutable($texto);
	} catch (Throwable $e) {
		return null;
	}
}

function reporte_seccion(string $titulo, array $columnas, array $filas, ?array $total = null, string $vacio = 'Sin datos.'): array
{
	return [
		'titulo' => $titulo,
		'columnas' => $columnas,
		'filas' => $filas,
		'total' => $total,
		'vacio' => $vacio,
	];
}

function reporte_construir(string $reporte, array $filtros = []): array
{
	$constructor = 'reporte_construir_' . $reporte;
	$datos = $constructor($filtros);
	$datos['reporte'] = $reporte;
	$datos['titulo'] = reporte_titulos()[$reporte];
	$datos['generado'] = date('d/m/Y H:i');
	return $datos;
}

function reporte_construir_ventas(array $f): array
{
	$pedidos = pedidos_cargar();

	$hoy = new DateTimeImmutable('today');
	$inicioSemana = $hoy->modify('monday this week');
	$finSemana = $inicioSemana->modify('+6 days')->setTime(23, 59, 59);
	$inicioMes = $hoy->modify('first day of this month');
	$finMes = $hoy->modify('last day of this month')->setTime(23, 59, 59);

	$inicioTexto = trim((string)($f['inicio'] ?? ''));
	$finTexto = trim((string)($f['fin'] ?? ''));
	$inicio = DateTimeImmutable::createFromFormat('!Y-m-d', $inicioTexto) ?: null;
	$fin = DateTimeImmutable::createFromFormat('!Y-m-d', $finTexto) ?: null;
	$hayRango = $inicio !== null && $fin !== null;
	$finRango = $hayRango ? $fin->setTime(23, 59, 59) : null;

	$dia = $semana = $mes = $rango = $ingresos = 0.0;
	$filas = [];
	$totalFiltrado = 0.0;

	foreach ($pedidos as $pedido) {
		$fecha = reporte_fecha_texto((string)($pedido['fecha'] ?? ''));
		if ($fecha === null) {
			continue;
		}
		$total = (float)($pedido['total'] ?? 0);
		$ingresos += $total;

		if ($fecha->format('Y-m-d') === $hoy->format('Y-m-d')) {
			$dia += $total;
		}
		if ($fecha >= $inicioSemana && $fecha <= $finSemana) {
			$semana += $total;
		}
		if ($fecha >= $inicioMes && $fecha <= $finMes) {
			$mes += $total;
		}

		$enRango = $hayRango && $fecha >= $inicio && $fecha <= $finRango;
		if ($enRango) {
			$rango += $total;
		}
		if ($hayRango && !$enRango) {
			continue;
		}

		$totalFiltrado += $total;
		$filas[] = [
			(string)($pedido['numero_pedido'] ?? ''),
			(string)($pedido['cliente'] ?? ''),
			(string)($pedido['metodo_pago'] ?? ''),
			(string)($pedido['estado'] ?? ''),
			$fecha->format('Y-m-d H:i'),
			$total,
		];
	}

	$resumen = [
		['Ventas del dia', $dia, 'moneda'],
		['Ventas de la semana', $semana, 'moneda'],
		['Ventas del mes', $mes, 'moneda'],
		['Total de ingresos', $ingresos, 'moneda'],
	];
	if ($hayRango) {
		$resumen[] = ['Ventas en el rango', $rango, 'moneda'];
	}

	return [
		'filtros' => $hayRango ? 'Rango: ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y') : 'Todas las ventas',
		'resumen' => $resumen,
		'secciones' => [
			reporte_seccion(
				'Detalle de ventas',
				[['Pedido', 'texto'], ['Cliente', 'texto'], ['Metodo de pago', 'texto'], ['Estado', 'texto'], ['Fecha', 'texto'], ['Total', 'moneda']],
				$filas,
				['Total', '', '', '', '', $totalFiltrado],
				'No hay ventas para el filtro actual.'
			),
		],
	];
}

function reporte_ventas_por_producto(): array
{
	$conteo = [];
	foreach (pedidos_cargar() as $pedido) {
		foreach (($pedido['items'] ?? []) as $item) {
			$nombre = trim((string)($item['producto'] ?? ''));
			if ($nombre === '') {
				continue;
			}
			$conteo[$nombre] = ($conteo[$nombre] ?? 0) + (int)($item['cantidad'] ?? 0);
		}
	}
	return $conteo;
}

function reporte_construir_productos(array $f): array
{
	$productos = productos_cargar();
	$conteo = reporte_ventas_por_producto();

	arsort($conteo);
	$masVendidos = array_slice($conteo, 0, 5, true);
	$menosVendidos = $conteo;
	asort($menosVendidos);
	$menosVendidos = array_slice($menosVendidos, 0, 5, true);

	$sinExistencias = [];
	$inventarioBajo = [];
	foreach ($productos as $producto) {
		$stock = (int)($producto['stock'] ?? 0);
		$fila = [(string)($producto['nombre'] ?? ''), (string)($producto['categoria'] ?? ''), $stock];
		if ($stock <= 0) {
			$sinExistencias[] = $fila;
		} elseif ($stock <= 10) {
			$inventarioBajo[] = $fila;
		}
	}

	$aFilas = static function (array $mapa): array {
		$filas = [];
		foreach ($mapa as $nombre => $cantidad) {
			$filas[] = [(string)$nombre, (int)$cantidad];
		}
		return $filas;
	};
	$colStock = [['Producto', 'texto'], ['Categoria', 'texto'], ['Stock', 'entero']];
	$colVentas = [['Producto', 'texto'], ['Cantidad vendida', 'entero']];

	return [
		'filtros' => 'Inventario y rotacion actual',
		'resumen' => [
			['Productos registrados', count($productos), 'entero'],
			['Productos con baja rotacion', count($menosVendidos), 'entero'],
			['Productos sin stock', count($sinExistencias), 'entero'],
			['Inventario bajo', count($inventarioBajo), 'entero'],
		],
		'secciones' => [
			reporte_seccion('Productos mas vendidos', $colVentas, $aFilas($masVendidos), null, 'Sin ventas registradas.'),
			reporte_seccion('Productos menos vendidos', $colVentas, $aFilas($menosVendidos), null, 'Sin ventas registradas.'),
			reporte_seccion('Productos sin existencias', $colStock, $sinExistencias, null, 'No hay productos agotados.'),
			reporte_seccion('Productos con inventario bajo', $colStock, $inventarioBajo, null, 'No hay productos con inventario bajo.'),
		],
	];
}

function reporte_construir_servicios(array $f): array
{
	$servicios = servicios_cargar();
	$citas = cargar_citas(indexar_servicios_por_id(cargar_servicios_citas()));

	$porServicio = [];
	$precios = [];
	foreach ($servicios as $servicio) {
		$nombre = trim((string)($servicio['nombre'] ?? ''));
		if ($nombre === '') {
			continue;
		}
		$porServicio[$nombre] = 0;
		$precios[$nombre] = (float)($servicio['precio'] ?? 0);
	}
	foreach ($citas as $cita) {
		$nombre = trim((string)($cita['servicio'] ?? ''));
		if ($nombre === '') {
			continue;
		}
		$porServicio[$nombre] = ($porServicio[$nombre] ?? 0) + 1;
	}
	arsort($porServicio);

	$totalCitas = array_sum($porServicio);
	$ingresoTotal = 0.0;
	$filas = [];
	foreach ($porServicio as $nombre => $cantidad) {
		$precio = (float)($precios[$nombre] ?? 0);
		$ingreso = $precio * (int)$cantidad;
		$ingresoTotal += $ingreso;
		$filas[] = [(string)$nombre, (int)$cantidad, $precio, $ingreso];
	}

	return [
		'filtros' => 'Todas las citas registradas',
		'resumen' => [
			['Servicios registrados', count($servicios), 'entero'],
			['Servicio mas solicitado', $totalCitas > 0 ? (string)array_key_first($porServicio) : 'Sin datos', 'texto'],
			['Total de citas', $totalCitas, 'entero'],
			['Ingreso estimado', $ingresoTotal, 'moneda'],
		],
		'secciones' => [
			reporte_seccion(
				'Demanda por servicio',
				[['Servicio', 'texto'], ['Citas', 'entero'], ['Precio referencia', 'moneda'], ['Ingreso estimado', 'moneda']],
				$filas,
				['Total', (int)$totalCitas, '', $ingresoTotal],
				'No hay datos de servicios.'
			),
		],
	];
}

function reporte_construir_citas(array $f): array
{
	$citas = cargar_citas(indexar_servicios_por_id(cargar_servicios_citas()));

	$periodo = trim((string)($f['periodo'] ?? 'mes'));
	$estado = trim((string)($f['estado'] ?? ''));
	$vista = trim((string)($f['vista'] ?? 'citas'));

	$hoy = new DateTimeImmutable('today');
	$inicioSemana = $hoy->modify('monday this week');
	$finSemana = $inicioSemana->modify('+6 days')->setTime(23, 59, 59);
	$inicioMes = $hoy->modify('first day of this month');
	$finMes = $hoy->modify('last day of this month')->setTime(23, 59, 59);

	$filtradas = [];
	foreach ($citas as $cita) {
		$fecha = DateTimeImmutable::createFromFormat('!Y-m-d', trim((string)($cita['fecha'] ?? '')));
		if (!$fecha) {
			continue;
		}
		if ($estado !== '' && strcasecmp(trim((string)($cita['estado'] ?? '')), $estado) !== 0) {
			continue;
		}

		$incluir = true;
		if ($periodo === 'dia') {
			$incluir = $fecha->format('Y-m-d') === $hoy->format('Y-m-d');
		} elseif ($periodo === 'semana') {
			$incluir = $fecha >= $inicioSemana && $fecha <= $finSemana;
		} elseif ($periodo === 'mes') {
			$incluir = $fecha >= $inicioMes && $fecha <= $finMes;
		}
		if ($incluir) {
			$filtradas[] = $cita;
		}
	}

	$estados = [];
	$clientes = [];
	$filas = [];
	foreach ($filtradas as $cita) {
		$nombreEstado = trim((string)($cita['estado'] ?? '')) ?: 'Sin estado';
		$estados[$nombreEstado] = ($estados[$nombreEstado] ?? 0) + 1;

		$cliente = trim((string)($cita['cliente'] ?? ''));
		if ($cliente !== '') {
			$clientes[$cliente] = ($clientes[$cliente] ?? 0) + 1;
		}

		$filas[] = [
			(string)($cita['cliente'] ?? ''),
			(string)($cita['servicio'] ?? ''),
			(string)($cita['fecha'] ?? ''),
			(string)($cita['hora'] ?? ''),
			(string)($cita['estado'] ?? ''),
			(string)($cita['pago'] ?? ''),
		];
	}
	arsort($clientes);

	$aFilas = static function (array $mapa): array {
		$salida = [];
		foreach ($mapa as $nombre => $cantidad) {
			$salida[] = [(string)$nombre, (int)$cantidad];
		}
		return $salida;
	};

	$nombresPeriodo = ['dia' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'todo' => 'Todo el historial'];
	$secciones = [
		reporte_seccion('Estado de citas', [['Estado', 'texto'], ['Cantidad', 'entero']], $aFilas($estados), ['Total', count($filtradas)], 'Sin datos para este filtro.'),
	];
	if ($vista === 'clientes') {
		$secciones[] = reporte_seccion('Clientes mas frecuentes', [['Cliente', 'texto'], ['Citas', 'entero']], $aFilas($clientes), null, 'No hay clientes en este filtro.');
	}
	$secciones[] = reporte_seccion(
		'Detalle de citas',
		[['Cliente', 'texto'], ['Servicio', 'texto'], ['Fecha', 'texto'], ['Hora', 'texto'], ['Estado', 'texto'], ['Pago', 'texto']],
		$filas,
		null,
		'No hay citas registradas.'
	);

	return [
		'filtros' => 'Periodo: ' . ($nombresPeriodo[$periodo] ?? 'Todo el historial') . ' | Estado: ' . ($estado !== '' ? $estado : 'Todos'),
		'resumen' => [
			['Citas en el filtro', count($filtradas), 'entero'],
			['Clientes unicos', count($clientes), 'entero'],
		],
		'secciones' => $secciones,
	];
}

function reporte_construir_pagos(array $f): array
{
	$pagos = pagos_cargar();
	$metodoFiltro = trim((string)($f['metodo'] ?? ''));
	$estadoFiltro = trim((string)($f['estado'] ?? ''));

	$conteo = ['sinpe' => 0, 'efectivo' => 0, 'tarjeta' => 0, 'pendiente' => 0, 'rechazado' => 0];
	$filas = [];
	$totalMonto = 0.0;

	foreach ($pagos as $pago) {
		$metodo = strtolower((string)($pago['metodo'] ?? ''));
		$estado = strtolower((string)($pago['estado'] ?? ''));

		foreach (['sinpe', 'efectivo', 'tarjeta'] as $clave) {
			if (strpos($metodo, $clave) !== false) {
				$conteo[$clave]++;
			}
		}
		if (strpos($estado, 'pendiente') !== false) {
			$conteo['pendiente']++;
		}
		if (strpos($estado, 'rechaz') !== false) {
			$conteo['rechazado']++;
		}

		if ($metodoFiltro !== '' && stripos($metodo, $metodoFiltro) === false) {
			continue;
		}
		if ($estadoFiltro !== '' && stripos($estado, $estadoFiltro) === false) {
			continue;
		}

		$monto = (float)($pago['monto'] ?? 0);
		$totalMonto += $monto;
		$filas[] = [
			(string)($pago['numero_pago'] ?? ''),
			(string)($pago['cliente'] ?? ''),
			(string)($pago['metodo'] ?? ''),
			(string)($pago['estado'] ?? ''),
			(string)($pago['fecha'] ?? ''),
			$monto,
		];
	}

	return [
		'filtros' => 'Metodo: ' . ($metodoFiltro !== '' ? ucfirst($metodoFiltro) : 'Todos') . ' | Estado: ' . ($estadoFiltro !== '' ? ucfirst($estadoFiltro) : 'Todos'),
		'resumen' => [
			['Pagos por SINPE', $conteo['sinpe'], 'entero'],
			['Pagos en efectivo', $conteo['efectivo'], 'entero'],
			['Pagos con tarjeta', $conteo['tarjeta'], 'entero'],
			['Pagos pendientes', $conteo['pendiente'], 'entero'],
			['Pagos rechazados', $conteo['rechazado'], 'entero'],
		],
		'secciones' => [
			reporte_seccion(
				'Detalle de pagos',
				[['N pago', 'texto'], ['Cliente', 'texto'], ['Metodo', 'texto'], ['Estado', 'texto'], ['Fecha', 'texto'], ['Monto', 'moneda']],
				$filas,
				['Total', '', '', '', '', $totalMonto],
				'No hay pagos para el filtro actual.'
			),
		],
	];
}

function reporte_construir_inventario(array $f): array
{
	$productos = productos_cargar();

	$sinStock = 0;
	$stockBajo = 0;
	$valorTotal = 0.0;
	$unidades = 0;
	$filas = [];

	foreach ($productos as $producto) {
		$stock = (int)($producto['stock'] ?? 0);
		$precio = (float)($producto['precio'] ?? 0);
		if ($stock <= 0) {
			$sinStock++;
		} elseif ($stock <= 10) {
			$stockBajo++;
		}
		$valor = $stock * $precio;
		$valorTotal += $valor;
		$unidades += $stock;
		$filas[] = [(string)($producto['nombre'] ?? ''), (string)($producto['categoria'] ?? ''), $precio, $stock, $valor];
	}

	return [
		'filtros' => 'Existencias actuales',
		'resumen' => [
			['Total de productos', count($productos), 'entero'],
			['Sin existencias', $sinStock, 'entero'],
			['Inventario bajo', $stockBajo, 'entero'],
			['Valor total inventario', $valorTotal, 'moneda'],
		],
		'secciones' => [
			reporte_seccion(
				'Detalle de inventario',
				[['Producto', 'texto'], ['Categoria', 'texto'], ['Precio', 'moneda'], ['Stock', 'entero'], ['Valor', 'moneda']],
				$filas,
				['Total', '', '', $unidades, $valorTotal],
				'No hay productos registrados.'
			),
		],
	];
}

// Lee reporte y filtros desde $_GET; responde 400 si el reporte no existe.
function reporte_desde_peticion(): array
{
	$reporte = trim((string)($_GET['reporte'] ?? 'ventas'));
	if (!reporte_valido($reporte)) {
		http_response_code(400);
		echo 'Reporte no permitido';
		exit;
	}
	return reporte_construir($reporte, $_GET);
}

function reporte_nombre_archivo(array $reporte, string $extension): string
{
	return 'reporte_' . $reporte['reporte'] . '_' . date('Ymd_His') . '.' . $extension;
}
