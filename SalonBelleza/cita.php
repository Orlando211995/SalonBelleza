<?php
session_start();

require_once __DIR__ . '/../Admin/citas/_agenda.php';

$servicios = cargar_servicios_citas();
$serviciosIndex = indexar_servicios_por_id($servicios);
$citas = cargar_citas($serviciosIndex);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$cliente = trim($_POST['cliente'] ?? '');
	$telefono = trim($_POST['telefono'] ?? '');
	$correo = trim($_POST['correo'] ?? '');
	$servicioId = isset($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : 0;
	$fecha = trim($_POST['fecha'] ?? '');
	$hora = trim($_POST['hora'] ?? '');
	$observaciones = trim($_POST['observaciones'] ?? '');

	$errores = [];

	if ($cliente === '') {
		$errores[] = 'El nombre es obligatorio.';
	}
	if ($telefono === '') {
		$errores[] = 'El telefono es obligatorio.';
	}
	if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
		$errores[] = 'El correo no es valido.';
	}
	if (!fecha_valida_ymd($fecha)) {
		$errores[] = 'La fecha no es valida.';
	}
	if (!hora_valida_hm($hora)) {
		$errores[] = 'La hora no es valida.';
	}

	$servicio = servicio_por_id($servicioId, $serviciosIndex);
	if (!$servicio) {
		$errores[] = 'Debes seleccionar un servicio valido.';
	}

	$duracion = (int)($servicio['duracion'] ?? 0);
	if ($duracion <= 0) {
		$errores[] = 'El servicio seleccionado no tiene una duracion valida.';
	}

	if (!$errores && !hora_disponible_para_cita($fecha, $hora, $duracion, $citas)) {
		$errores[] = 'La hora seleccionada ya no esta disponible.';
	}

	if ($errores) {
		$_SESSION['errores_cita_publica'] = $errores;
		$_SESSION['old_cita_publica'] = [
			'cliente' => $cliente,
			'telefono' => $telefono,
			'correo' => $correo,
			'servicio_id' => (string)$servicioId,
			'fecha' => $fecha,
			'hora' => $hora,
			'observaciones' => $observaciones,
		];
		header('Location: cita.php');
		exit;
	}

	$citas[] = [
		'id' => siguiente_cita_id($citas),
		'cliente' => $cliente,
		'telefono' => $telefono,
		'correo' => $correo,
		'servicio_id' => (int)$servicio['id'],
		'servicio' => (string)$servicio['nombre'],
		'duracion' => $duracion,
		'empleado' => '',
		'fecha' => $fecha,
		'hora' => $hora,
		'observaciones' => $observaciones,
		'estado' => 'Pendiente',
		'pago' => 'No aplica',
		'created_at' => date('c'),
	];

	guardar_citas($citas);
	$_SESSION['ok_cita_publica'] = 'Tu cita fue registrada y quedo Pendiente de confirmacion.';
	header('Location: cita.php');
	exit;
}

$errores = $_SESSION['errores_cita_publica'] ?? [];
$old = $_SESSION['old_cita_publica'] ?? [];
$ok = $_SESSION['ok_cita_publica'] ?? '';
unset($_SESSION['errores_cita_publica'], $_SESSION['old_cita_publica'], $_SESSION['ok_cita_publica']);

$servicioIdSeleccionado = (int)($old['servicio_id'] ?? ($_GET['servicio_id'] ?? ($servicios[0]['id'] ?? 0)));
$fechaSeleccionada = (string)($old['fecha'] ?? ($_GET['fecha'] ?? date('Y-m-d')));

$servicioSeleccionado = servicio_por_id($servicioIdSeleccionado, $serviciosIndex);
$duracionSeleccionada = (int)($servicioSeleccionado['duracion'] ?? 30);
$horasDisponibles = fecha_valida_ymd($fechaSeleccionada) ? generar_horas_disponibles($fechaSeleccionada, $duracionSeleccionada, $citas) : [];

include(__DIR__ . '/../Includes/header.php');
include(__DIR__ . '/../Includes/menu.php');
?>

<section class="hero" style="background: url('/Assets/img/banner/bannercitas.jpg') center/cover no-repeat;"></section>

<section class="servicio-titulo">
	<div class="contenedor-servicio-titulo">
		<h2>Citas</h2>
	</div>
</section>

<section class="contacto-home">
	<div class="container">
		<div class="contacto-grid">
			<div class="contacto-info">
				<h3>Reserva tu cita</h3>
				<p>Agenda tu cita en linea. El sistema te muestra solo horas disponibles segun el servicio.</p>
				<p>WhatsApp: 8910-2422</p>
				<p>Horario del salon: Lunes a Viernes 08:00 AM - 06:00 PM, Sabado 08:00 AM - 04:00 PM, Domingo cerrado.</p>

				<a href="https://wa.me/50689102422?text=Hola%20quiero%20reservar%20una%20cita." class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
					<i class="bi bi-whatsapp"></i> Reservar por WhatsApp
				</a>
			</div>

			<div class="contacto-form-card">
				<h3>Nueva cita</h3>

				<?php if ($ok): ?>
					<div class="contacto-alert exito"><p><?php echo htmlspecialchars($ok, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($errores): ?>
					<div class="contacto-alert error">
						<?php foreach ($errores as $error): ?>
							<p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form action="cita.php" method="post" class="contacto-form" id="citaPublicaForm" data-disponibilidad-url="/Admin/citas/disponibilidad.php">
					<div class="contacto-campo">
						<label for="cliente">Nombre del cliente</label>
						<input type="text" id="cliente" name="cliente" maxlength="140" required value="<?php echo htmlspecialchars((string)($old['cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
					</div>

					<div class="contacto-campo">
						<label for="telefono">Telefono</label>
						<input type="text" id="telefono" name="telefono" maxlength="40" required value="<?php echo htmlspecialchars((string)($old['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
					</div>

					<div class="contacto-campo">
						<label for="correo">Correo</label>
						<input type="email" id="correo" name="correo" maxlength="160" required value="<?php echo htmlspecialchars((string)($old['correo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
					</div>

					<div class="contacto-campo">
						<label for="servicio_id">Servicio</label>
						<select id="servicio_id" name="servicio_id" required>
							<?php foreach ($servicios as $servicio): ?>
								<option value="<?php echo (int)$servicio['id']; ?>" data-duracion="<?php echo (int)($servicio['duracion'] ?? 0); ?>" <?php echo $servicioIdSeleccionado === (int)$servicio['id'] ? 'selected' : ''; ?>>
									<?php echo htmlspecialchars((string)$servicio['nombre'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo (int)($servicio['duracion'] ?? 0); ?> min)
								</option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="contacto-campo">
						<label for="fecha">Fecha</label>
						<input type="date" id="fecha" name="fecha" required value="<?php echo htmlspecialchars($fechaSeleccionada, ENT_QUOTES, 'UTF-8'); ?>">
					</div>

					<div class="contacto-campo">
						<label for="hora">Hora disponible</label>
						<select id="hora" name="hora" required>
							<option value="">Seleccionar hora</option>
							<?php $horaOld = (string)($old['hora'] ?? ''); ?>
							<?php foreach ($horasDisponibles as $hora): ?>
								<option value="<?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $horaOld === $hora ? 'selected' : ''; ?>><?php echo htmlspecialchars($hora, ENT_QUOTES, 'UTF-8'); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="contacto-campo">
						<label for="observaciones">Observaciones (opcional)</label>
						<textarea id="observaciones" name="observaciones" rows="4" maxlength="1200"><?php echo htmlspecialchars((string)($old['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
					</div>

					<button type="submit" class="btn-whatsapp" style="border:0; cursor:pointer; justify-content:center;">
						<i class="bi bi-calendar-check"></i> Guardar cita
					</button>
				</form>
			</div>
		</div>
	</div>
</section>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
