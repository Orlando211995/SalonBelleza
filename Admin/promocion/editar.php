<?php
session_start();

require_once __DIR__ . '/../Includes/auth.php';
require_once __DIR__ . '/../../Includes/promocion.php';

$errores = $_SESSION['errores_promocion'] ?? [];
$mensajeOk = $_SESSION['ok_promocion'] ?? '';
$promo = $_SESSION['old_promocion'] ?? promocion_obtener();
unset($_SESSION['errores_promocion'], $_SESSION['ok_promocion'], $_SESSION['old_promocion']);

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Promoción del mes</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo $e($mensajeOk); ?></p></div>
				<?php endif; ?>

				<?php if ($errores): ?>
					<div class="admin-alert error">
						<?php foreach ($errores as $error): ?>
							<p><?php echo $e($error); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form action="guardar.php" method="post" enctype="multipart/form-data" class="admin-form">
					<div class="admin-form-grid">
						<div class="admin-field">
							<label for="titulo">Título</label>
							<input type="text" id="titulo" name="titulo" maxlength="160" required value="<?php echo $e($promo['titulo']); ?>">
						</div>

						<div class="admin-field">
							<label for="subtitulo">Oferta (ej. 20% de descuento en Balayage)</label>
							<input type="text" id="subtitulo" name="subtitulo" maxlength="200" required value="<?php echo $e($promo['subtitulo']); ?>">
						</div>

						<div class="admin-field admin-field-full">
							<label for="texto">Texto</label>
							<input type="text" id="texto" name="texto" maxlength="400" value="<?php echo $e($promo['texto']); ?>">
						</div>

						<div class="admin-field">
							<label for="boton_texto">Texto del botón</label>
							<input type="text" id="boton_texto" name="boton_texto" maxlength="60" value="<?php echo $e($promo['boton_texto']); ?>">
						</div>

						<div class="admin-field">
							<label for="boton_enlace">Enlace del botón</label>
							<input type="text" id="boton_enlace" name="boton_enlace" maxlength="255" value="<?php echo $e($promo['boton_enlace']); ?>">
							<small>Por ejemplo: cita.php</small>
						</div>

						<div class="admin-field">
							<label for="imagen">Imagen</label>
							<input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
							<small>Mínimo 600x300 px. JPG, PNG o WEBP, máximo 5 MB. Si no subes una, se conserva la actual.</small>
						</div>

						<div class="admin-field">
							<label>Mostrar en el inicio</label>
							<div class="admin-radios">
								<label><input type="radio" name="activa" value="1" <?php echo !empty($promo['activa']) ? 'checked' : ''; ?>> Sí</label>
								<label><input type="radio" name="activa" value="0" <?php echo empty($promo['activa']) ? 'checked' : ''; ?>> No</label>
							</div>
						</div>

						<div class="admin-field admin-field-full">
							<label>Imagen actual</label>
							<img src="<?php echo $e($promo['imagen']); ?>" alt="Imagen actual de la promoción" style="max-width:320px;width:100%;border-radius:12px;">
						</div>
					</div>

					<div class="admin-actions-row">
						<button type="submit" class="admin-btn">Guardar promoción</button>
						<a href="/SalonBelleza/index.php" class="admin-action" target="_blank" rel="noopener">Ver inicio</a>
					</div>
				</form>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
