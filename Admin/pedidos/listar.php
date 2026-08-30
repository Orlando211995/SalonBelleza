<?php
session_start();

require_once __DIR__ . '/_data.php';

$pedidos = pedidos_cargar();

$mensajeOk = $_SESSION['ok_pedido'] ?? '';
$mensajeError = $_SESSION['error_pedido'] ?? '';
unset($_SESSION['ok_pedido'], $_SESSION['error_pedido']);

$filtroQ = trim($_GET['q'] ?? '');
$filtroEstado = trim($_GET['estado'] ?? '');
$filtroFecha = trim($_GET['fecha'] ?? '');

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Listar pedidos</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error"><p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<div class="admin-toolbar" style="grid-template-columns: 1fr 190px 170px auto;">
					<input type="search" id="pedidoBuscar" class="admin-input" placeholder="🔍 Buscar por cliente, pedido, telefono o correo" aria-label="Buscar pedido" value="<?php echo htmlspecialchars($filtroQ, ENT_QUOTES, 'UTF-8'); ?>">

					<select id="pedidoEstado" class="admin-select" aria-label="Filtrar por estado">
						<option value="">Estado</option>
						<?php foreach (pedidos_estados_validos() as $estado): ?>
							<option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroEstado === $estado ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></option>
						<?php endforeach; ?>
					</select>

					<input type="date" id="pedidoFecha" class="admin-input" value="<?php echo htmlspecialchars($filtroFecha, ENT_QUOTES, 'UTF-8'); ?>">

					<a href="buscar.php" class="admin-action">Buscar avanzada</a>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaPedidos">
						<thead>
							<tr>
								<th># Pedido</th>
								<th>Fecha</th>
								<th>Cliente</th>
								<th>Telefono</th>
								<th>Correo</th>
								<th>Metodo pago</th>
								<th>Total</th>
								<th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($pedidos as $pedido): ?>
								<?php
								$texto = strtolower((string)($pedido['numero_pedido'] ?? '') . ' ' . (string)($pedido['cliente'] ?? '') . ' ' . (string)($pedido['correo'] ?? '') . ' ' . (string)($pedido['telefono'] ?? ''));
								$fechaSolo = substr((string)($pedido['fecha'] ?? ''), 0, 10);
								if ($filtroQ !== '' && strpos($texto, strtolower($filtroQ)) === false) {
									continue;
								}
								if ($filtroEstado !== '' && ($pedido['estado'] ?? '') !== $filtroEstado) {
									continue;
								}
								if ($filtroFecha !== '' && $fechaSolo !== $filtroFecha) {
									continue;
								}
								?>
								<tr class="js-order-row"
									data-busqueda="<?php echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); ?>"
									data-estado="<?php echo htmlspecialchars((string)($pedido['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-fecha="<?php echo htmlspecialchars($fechaSolo, ENT_QUOTES, 'UTF-8'); ?>">
									<td><?php echo htmlspecialchars((string)($pedido['numero_pedido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['metodo_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td>₡<?php echo number_format((float)($pedido['total'] ?? 0), 0, ',', '.'); ?></td>
									<td><?php echo htmlspecialchars((string)($pedido['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="ver.php?id=<?php echo (int)($pedido['id'] ?? 0); ?>" class="admin-action" title="Ver">👁️</a>
										<a href="editar.php?id=<?php echo (int)($pedido['id'] ?? 0); ?>" class="admin-action edit" title="Editar">✏️</a>
										<a href="factura.php?id=<?php echo (int)($pedido['id'] ?? 0); ?>" class="admin-action" title="Factura">🧾</a>
										<a href="imprimir.php?id=<?php echo (int)($pedido['id'] ?? 0); ?>" class="admin-action" title="Imprimir">🖨️</a>
									</td>
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

