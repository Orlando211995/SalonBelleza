<?php
session_start();

require_once __DIR__ . '/_data.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$servicio = servicios_buscar_por_id($id);

if (!$servicio) {
	$_SESSION['error_servicio'] = 'Servicio no encontrado.';
	header('Location: listar.php');
	exit;
}

$errores = $_SESSION['errores_servicio_editar'] ?? [];
$old = $_SESSION['old_servicio_editar'] ?? [];
unset($_SESSION['errores_servicio_editar'], $_SESSION['old_servicio_editar']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Editar servicio</h2>

				<?php if ($errores): ?>
					<div class="admin-alert error">
						<?php foreach ($errores as $error): ?>
							<p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form action="actualizar.php" method="post" enctype="multipart/form-data" class="admin-form">
					<input type="hidden" name="id" value="<?php echo (int)$id; ?>">

					<div class="admin-form-grid">
						<div class="admin-field">
							<label>Imagen actual</label>
							<img src="<?php echo htmlspecialchars($servicio['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen del servicio" class="admin-product-img">
						</div>

						<div class="admin-field">
							<label for="imagen">Nueva imagen (opcional)</label>
							<input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp">
						</div>

						<div class="admin-field">
							<label for="nombre">Nombre del servicio</label>
							<input type="text" id="nombre" name="nombre" maxlength="160" required value="<?php echo htmlspecialchars($old['nombre'] ?? ($servicio['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="categoria">Categoria</label>
							<?php $cat = $old['categoria'] ?? ($servicio['categoria'] ?? ''); ?>
							<select id="categoria" name="categoria" required>
								<option value="">Seleccionar categoria</option>
								<option value="Cortes" <?php echo $cat === 'Cortes' ? 'selected' : ''; ?>>Cortes</option>
								<option value="Coloracion" <?php echo $cat === 'Coloracion' ? 'selected' : ''; ?>>Coloracion</option>
								<option value="Tratamientos" <?php echo $cat === 'Tratamientos' ? 'selected' : ''; ?>>Tratamientos</option>
								<option value="Barberia" <?php echo $cat === 'Barberia' ? 'selected' : ''; ?>>Barberia</option>
								<option value="Manicure" <?php echo $cat === 'Manicure' ? 'selected' : ''; ?>>Manicure</option>
								<option value="Pedicure" <?php echo $cat === 'Pedicure' ? 'selected' : ''; ?>>Pedicure</option>
								<option value="Peinados" <?php echo $cat === 'Peinados' ? 'selected' : ''; ?>>Peinados</option>
								<option value="Maquillaje" <?php echo $cat === 'Maquillaje' ? 'selected' : ''; ?>>Maquillaje</option>
								<option value="Faciales" <?php echo $cat === 'Faciales' ? 'selected' : ''; ?>>Faciales</option>
								<option value="Depilacion" <?php echo $cat === 'Depilacion' ? 'selected' : ''; ?>>Depilacion</option>
							</select>
						</div>

						<div class="admin-field" style="grid-column: 1 / -1;">
							<label for="descripcion">Descripcion</label>
							<textarea id="descripcion" name="descripcion" maxlength="1200" required><?php echo htmlspecialchars($old['descripcion'] ?? ($servicio['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
						</div>

						<div class="admin-field">
							<label for="duracion">Duracion (minutos)</label>
							<input type="number" id="duracion" name="duracion" min="1" step="1" required value="<?php echo htmlspecialchars((string)($old['duracion'] ?? ($servicio['duracion'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="precio">Precio</label>
							<input type="number" id="precio" name="precio" min="0" step="1" required value="<?php echo htmlspecialchars((string)($old['precio'] ?? ($servicio['precio'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label>Estado</label>
							<?php $estado = $old['estado'] ?? ($servicio['estado'] ?? 'Activo'); ?>
							<div class="admin-radios">
								<label><input type="radio" name="estado" value="Activo" <?php echo $estado === 'Activo' ? 'checked' : ''; ?>> Activo</label>
								<label><input type="radio" name="estado" value="Inactivo" <?php echo $estado === 'Inactivo' ? 'checked' : ''; ?>> Inactivo</label>
							</div>
						</div>
					</div>

					<div class="admin-actions-row">
						<button type="submit" class="admin-btn">Actualizar servicio</button>
						<a href="listar.php" class="admin-action">Cancelar</a>
					</div>
				</form>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
