<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$mensajeOk = $_SESSION['ok_cita'] ?? '';
$mensajeError = $_SESSION['error_cita'] ?? '';
unset($_SESSION['ok_cita'], $_SESSION['error_cita']);

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
				<h2>Listar citas</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error"><p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<div class="admin-toolbar" style="grid-template-columns: auto 1fr 180px 170px auto;">
					<a href="agregar.php" class="admin-btn">➕ Nueva cita</a>

					<input type="search" id="citaBuscar" class="admin-input" placeholder="🔍 Cliente o servicio" aria-label="Buscar cita" value="<?php echo htmlspecialchars($filtroQ, ENT_QUOTES, 'UTF-8'); ?>">

					<input type="date" id="citaFecha" class="admin-input" value="<?php echo htmlspecialchars($filtroFecha, ENT_QUOTES, 'UTF-8'); ?>">

					<select id="citaEstado" class="admin-select" aria-label="Filtrar por estado">
						<option value="">Estado</option>
						<?php foreach (estados_cita_validos() as $estado): ?>
							<option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filtroEstado === $estado ? 'selected' : ''; ?>><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></option>
						<?php endforeach; ?>
					</select>

					<a href="calendario.php" class="admin-action">📅 Calendario</a>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaCitas">
						<thead>
							<tr>
								<th>Fecha</th>
								<th>Hora</th>
								<th>Cliente</th>
								<th>Servicio</th>
								<th>Duracion</th>
								<th>Estado</th>
								<th>Pago</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($citas as $cita): ?>
								<?php
								$texto = strtolower((string)($cita['cliente'] ?? '') . ' ' . (string)($cita['servicio'] ?? ''));
								if ($filtroQ !== '' && strpos($texto, strtolower($filtroQ)) === false) {
									continue;
								}
								if ($filtroEstado !== '' && ($cita['estado'] ?? '') !== $filtroEstado) {
									continue;
								}
								if ($filtroFecha !== '' && ($cita['fecha'] ?? '') !== $filtroFecha) {
									continue;
								}
								?>
								<tr class="js-cita-row"
									data-busqueda="<?php echo htmlspecialchars($texto, ENT_QUOTES, 'UTF-8'); ?>"
									data-estado="<?php echo htmlspecialchars((string)($cita['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-fecha="<?php echo htmlspecialchars((string)($cita['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
									<td><?php echo htmlspecialchars((string)($cita['fecha'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['hora'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['servicio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><span class="admin-duration"><?php echo (int)($cita['duracion'] ?? 0); ?> min</span></td>
									<td><?php echo htmlspecialchars((string)($cita['estado'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars((string)($cita['pago'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="ver.php?id=<?php echo (int)($cita['id'] ?? 0); ?>" class="admin-action" title="Ver">👁️</a>
										<a href="editar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>" class="admin-action edit" title="Editar">✏️</a>
										<a href="actualizar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>&accion=confirmar" class="admin-action" title="Confirmar">✅</a>
										<a href="actualizar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>&accion=proceso" class="admin-action" title="En proceso">🟡</a>
										<a href="actualizar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>&accion=finalizar" class="admin-action" title="Finalizar">✔️</a>
										<a href="actualizar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>&accion=cancelar" class="admin-action delete" title="Cancelar">❌</a>
										<a href="eliminar.php?id=<?php echo (int)($cita['id'] ?? 0); ?>" class="admin-action delete" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta cita?');">🗑️</a>
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

