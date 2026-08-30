<?php
session_start();

require_once __DIR__ . '/_agenda.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

$indice = null;
foreach ($citas as $i => $item) {
	if ((int)($item['id'] ?? 0) === $id) {
		$indice = $i;
		break;
	}
}

if ($indice === null) {
	$_SESSION['error_cita'] = 'Cita no encontrada.';
	header('Location: listar.php');
	exit;
}

$cita = $citas[$indice];

$errores = $_SESSION['errores_cita_editar'] ?? [];
$old = $_SESSION['old_cita_editar'] ?? [];
unset($_SESSION['errores_cita_editar'], $_SESSION['old_cita_editar']);

$servicioId = (int)($old['servicio_id'] ?? ($cita['servicio_id'] ?? 0));
$fecha = (string)($old['fecha'] ?? ($cita['fecha'] ?? date('Y-m-d')));
$servicio = servicio_por_id($servicioId, $serviciosIndex);
$duracion = (int)($servicio['duracion'] ?? 30);

$horasDisponibles = fecha_valida_ymd($fecha) ? generar_horas_disponibles($fecha, $duracion, $citas, $id) : [];
$horaActual = (string)($old['hora'] ?? ($cita['hora'] ?? ''));
if ($horaActual !== '' && !in_array($horaActual, $horasDisponibles, true)) {
	$horasDisponibles[] = $horaActual;
	sort($horasDisponibles);
}

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Editar cita</h2>

				<?php if ($errores): ?>
					<div class="admin-alert error">
						<?php foreach ($errores as $error): ?>
							<p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form action="actualizar.php" method="post" class="admin-form">
					<input type="hidden" name="id" value="<?php echo (int)$id; ?>">

					<div class="admin-form-grid">
						<div class="admin-field">
							<label for="cliente">Nombre del cliente</label>
							<input type="text" id="cliente" name="cliente" maxlength="140" required value="<?php echo htmlspecialchars((string)($old['cliente'] ?? ($cita['cliente'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="telefono">Telefono</label>
							<input type="text" id="telefono" name="telefono" maxlength="40" required value="<?php echo htmlspecialchars((string)($old['telefono'] ?? ($cita['telefono'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="correo">Correo</label>
							<input type="email" id="correo" name="correo" maxlength="160" required value="<?php echo htmlspecialchars((string)($old['correo'] ?? ($cita['correo'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="servicio_id">Servicio</label>
							<select id="servicio_id" name="servicio_id" required>
								<?php foreach ($servicios as $item): ?>
									<option value="<?php echo (int)$item['id']; ?>" <?php echo $servicioId === (int)$item['id'] ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars((string)$item['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)($item['duracion'] ?? 0); ?> min)
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="admin-field">
							<label for="empleado">Empleado (opcional)</label>
							<input type="text" id="empleado" name="empleado" maxlength="140" value="<?php echo htmlspecialchars((string)($old['empleado'] ?? ($cita['empleado'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="fecha">Fecha</label>
							<input type="date" id="fecha" name="fecha" required value="<?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="hora">Hora</label>
							<select id="hora" name="hora" required>
								<option value="">Seleccionar hora</option>
								<?php foreach ($horasDisponibles as $hora): ?>
									<option value="<?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $horaActual === $hora ? 'selected' : ''; ?>><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="admin-field">
							<label for="pago">Pago</label>
							<?php $pago = (string)($old['pago'] ?? ($cita['pago'] ?? 'No aplica')); ?>
							<select id="pago" name="pago" required>
								<?php foreach (pagos_cita_validos() as $op): ?>
									<option value="<?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $pago === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="admin-field" style="grid-column: 1 / -1;">
							<label for="observaciones">Observaciones</label>
							<textarea id="observaciones" name="observaciones" maxlength="1200"><?php echo htmlspecialchars((string)($old['observaciones'] ?? ($cita['observaciones'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></textarea>
						</div>

						<div class="admin-field">
							<label for="estado">Estado</label>
							<?php $estado = (string)($old['estado'] ?? ($cita['estado'] ?? 'Pendiente')); ?>
							<select id="estado" name="estado" required>
								<?php foreach (estados_cita_validos() as $op): ?>
									<option value="<?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $estado === $op ? 'selected' : ''; ?>><?php echo htmlspecialchars($op, ENT_QUOTES, 'UTF-8'); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="admin-actions-row">
						<button type="submit" class="admin-btn">Actualizar cita</button>
						<a href="listar.php" class="admin-action">Cancelar</a>
					</div>
				</form>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>

