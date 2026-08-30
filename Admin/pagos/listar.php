<?php
session_start();

require_once __DIR__ . '/_data.php';

$pagos = pagos_cargar();

$mensajeOk = $_SESSION['ok_pago'] ?? '';
$mensajeError = $_SESSION['error_pago'] ?? '';
unset($_SESSION['ok_pago'], $_SESSION['error_pago']);

$filtroQ = trim($_GET['q'] ?? '');
$filtroEstado = trim($_GET['estado'] ?? '');
$filtroMetodo = trim($_GET['metodo'] ?? '');
$filtroFecha = trim($_GET['fecha'] ?? '');

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Listar pagos</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error"><p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<div class="admin-toolbar" style="grid-template-columns: 1fr 180px 210px 170px auto;">
					<input type="search" id="pagoBuscar" class="admin-input" placeholder="🔍 Buscar pago, cliente o numero" value="<?php echo htmlspecialchars($filtroQ, ENT_QUOTES, 'UTF-8'); ?>">

					<select id="pagoEstado" class="admin-select">
						<option value="">Estado</option>
						<?php foreach (pagos_estados_validos() as $estado): ?>
							<option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroEstado === $estado ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></option>
						<?php endforeach; ?>
					</select>

					<select id="pagoMetodo" class="admin-select">
						<option value="">Metodo</option>
						<?php foreach (pagos_metodos_validos() as $metodo): ?>
							<option value="<?php echo htmlspecialchars($metodo, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroMetodo === $metodo ? 'selected' : ''; ?>><?php echo htmlspecialchars($metodo, ENT_QUOTES, 'UTF-8'); ?></option>
						<?php endforeach; ?>
					</select>

					<input type="date" id="pagoFecha" class="admin-input" value="<?php echo htmlspecialchars($filtroFecha, ENT_QUOTES, 'UTF-8'); ?>">

					<a href="buscar.php" class="admin-action">Buscar avanzada</a>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaPagos">
						<thead>
							<tr>
								<th># Pago</th>
								<th>Fecha</th>
								<th>Cliente</th>
								<th>Tipo</th>
								<th>Numero</th>
								<th>Metodo</th>
								<th>Monto</th>
								<th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($pagos as $pago): ?>
								<?php
								$texto = strtolower((string)($pago['numero_pago'] ?? '') . ' ' . (string)($pago['cliente'] ?? '') . ' ' . (string)($pago['numero'] ?? ''));
								$fechaSolo = substr((string)($pago['fecha'] ?? ''), 0, 10);

								if ($filtroQ !== '' && strpos($texto, strtolower($filtroQ)) === false) {
									continue;
								}
								if ($filtroEstado !== '' && ($pago['estado'] ?? '') !== $filtroEstado) {
									continue;
								}
								if ($filtroMetodo !== '' && ($pago['metodo'] ?? '') !== $filtroMetodo) {
									continue;
								}
								if ($filtroFecha !== '' && $fechaSolo !== $filtroFecha) {
									continue;
								}
								?>
								<tr class="js-payment-row"
									data-busqueda="<?php echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); ?>"
									data-estado="<?php echo htmlspecialchars((string)($pago['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-metodo="<?php echo htmlspecialchars((string)($pago['metodo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-fecha="<?php echo htmlspecialchars($fechaSolo, ENT_QUOTES, 'UTF-8'); ?>">
									<td><?php echo htmlspecialchars((string)($pago['numero_pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['tipo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['numero'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['metodo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td>₡<?php echo number_format((float)($pago['monto'] ?? 0), 0, ',', '.'); ?></td>
									<td><?php echo htmlspecialchars((string)($pago['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="ver.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action" title="Ver">👁️</a>
										<a href="editar.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action edit" title="Editar">✏️</a>
										<a href="aprobar.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action" title="Aprobar">✅</a>
										<a href="rechazar.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action delete" title="Rechazar">❌</a>
										<a href="comprobante.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action" title="Comprobante">📄</a>
										<a href="imprimir.php?id=<?php echo (int)($pago['id'] ?? 0); ?>" class="admin-action" title="Imprimir">🖨️</a>
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

