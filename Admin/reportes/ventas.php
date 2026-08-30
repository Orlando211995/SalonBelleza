<?php
require_once __DIR__ . '/../pedidos/_data.php';

function dineroVentas(float $monto): string
{
	return '₡' . number_format($monto, 0, ',', '.');
}

$pedidos = pedidos_cargar();

$hoy = new DateTimeImmutable('today');
$inicioSemana = $hoy->modify('monday this week');
$finSemana = $inicioSemana->modify('+6 days');
$inicioMes = $hoy->modify('first day of this month');
$finMes = $hoy->modify('last day of this month');

$inicioRango = trim($_GET['inicio'] ?? '');
$finRango = trim($_GET['fin'] ?? '');

$ventasDia = 0.0;
$ventasSemana = 0.0;
$ventasMes = 0.0;
$ventasRango = 0.0;
$ingresosTotales = 0.0;

$ventasFiltradas = [];

foreach ($pedidos as $pedido) {
	$fechaPedido = $pedido['fecha'] ?? '';
	if ($fechaPedido === '') {
		continue;
	}

	$fecha = new DateTimeImmutable($fechaPedido);
	$total = (float)($pedido['total'] ?? 0);

	$ingresosTotales += $total;

	if ($fecha->format('Y-m-d') === $hoy->format('Y-m-d')) {
		$ventasDia += $total;
	}

	if ($fecha >= $inicioSemana && $fecha <= $finSemana->setTime(23, 59, 59)) {
		$ventasSemana += $total;
	}

	if ($fecha >= $inicioMes && $fecha <= $finMes->setTime(23, 59, 59)) {
		$ventasMes += $total;
	}

	if ($inicioRango !== '' && $finRango !== '') {
		$inicioObj = DateTimeImmutable::createFromFormat('Y-m-d', $inicioRango);
		$finObj = DateTimeImmutable::createFromFormat('Y-m-d', $finRango);
		if ($inicioObj && $finObj && $fecha >= $inicioObj && $fecha <= $finObj->setTime(23, 59, 59)) {
			$ventasRango += $total;
			$ventasFiltradas[] = $pedido;
		}
	}
}

if ($inicioRango === '' || $finRango === '') {
	$ventasFiltradas = $pedidos;
}

$queryBase = [
	'reporte' => 'ventas',
	'inicio' => $inicioRango,
	'fin' => $finRango,
];

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Reporte de Ventas</h2>
				<p>Resumen por dia, semana, mes y rango de fechas.</p>

				<form class="admin-toolbar" method="get" action="ventas.php" style="grid-template-columns:1fr 1fr auto;">
					<input type="date" name="inicio" class="admin-input" value="<?php echo htmlspecialchars($inicioRango, ENT_QUOTES, 'UTF-8'); ?>">
					<input type="date" name="fin" class="admin-input" value="<?php echo htmlspecialchars($finRango, ENT_QUOTES, 'UTF-8'); ?>">
					<button type="submit" class="admin-btn">Filtrar rango</button>
				</form>

				<div class="admin-cards" style="margin-bottom:14px;">
					<article class="admin-card"><h3>Ventas del dia</h3><p><?php echo dineroVentas($ventasDia); ?></p></article>
					<article class="admin-card"><h3>Ventas de la semana</h3><p><?php echo dineroVentas($ventasSemana); ?></p></article>
					<article class="admin-card"><h3>Ventas del mes</h3><p><?php echo dineroVentas($ventasMes); ?></p></article>
					<article class="admin-card"><h3>Total de ingresos</h3><p><?php echo dineroVentas($ingresosTotales); ?></p></article>
				</div>

				<?php if ($inicioRango !== '' && $finRango !== ''): ?>
					<div class="admin-alert success">
						<p>Total de ventas en rango: <?php echo dineroVentas($ventasRango); ?></p>
					</div>
				<?php endif; ?>

				<div class="admin-actions-row" style="margin:12px 0 14px; flex-wrap: wrap;">
					<a class="admin-btn" href="exportar_pdf.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📄 Exportar a PDF</a>
					<a class="admin-btn" href="exportar_excel.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">📊 Exportar a Excel</a>
					<a class="admin-btn" href="imprimir.php?<?php echo htmlspecialchars(http_build_query($queryBase), ENT_QUOTES, 'UTF-8'); ?>">🖨 Imprimir</a>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table">
						<thead>
							<tr>
								<th>Pedido</th>
								<th>Cliente</th>
								<th>Metodo pago</th>
								<th>Estado</th>
								<th>Fecha</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!$ventasFiltradas): ?>
								<tr><td colspan="6">No hay ventas para el filtro actual.</td></tr>
							<?php endif; ?>
							<?php foreach ($ventasFiltradas as $venta): ?>
								<tr>
									<td><?php echo htmlspecialchars($venta['numero_pedido'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($venta['cliente'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($venta['metodo_pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($venta['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($venta['fecha'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo dineroVentas((float)($venta['total'] ?? 0)); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
