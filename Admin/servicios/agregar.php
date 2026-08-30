<?php
session_start();

$errores = $_SESSION['errores_servicio'] ?? [];
$old = $_SESSION['old_servicio'] ?? [];
unset($_SESSION['errores_servicio'], $_SESSION['old_servicio']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Agregar servicio</h2>

				<?php if ($errores): ?>
					<div class="admin-alert error">
						<?php foreach ($errores as $error): ?>
							<p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form action="guardar.php" method="post" enctype="multipart/form-data" class="admin-form">
					<div class="admin-form-grid">
						<div class="admin-field">
							<label for="nombre">Nombre del servicio</label>
							<input type="text" id="nombre" name="nombre" maxlength="160" required value="<?php echo htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="categoria">Categoria</label>
							<?php $cat = $old['categoria'] ?? ''; ?>
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
							<textarea id="descripcion" name="descripcion" maxlength="1200" required><?php echo htmlspecialchars($old['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
						</div>

						<div class="admin-field">
							<label for="duracion">Duracion (minutos)</label>
							<input type="number" id="duracion" name="duracion" min="1" step="1" required value="<?php echo htmlspecialchars($old['duracion'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="precio">Precio</label>
							<input type="number" id="precio" name="precio" min="0" step="1" required value="<?php echo htmlspecialchars($old['precio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="imagen">Imagen</label>
							<input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp" required>
						</div>

						<div class="admin-field">
							<label>Estado</label>
							<?php $estado = $old['estado'] ?? 'Activo'; ?>
							<div class="admin-radios">
								<label><input type="radio" name="estado" value="Activo" <?php echo $estado === 'Activo' ? 'checked' : ''; ?>> Activo</label>
								<label><input type="radio" name="estado" value="Inactivo" <?php echo $estado === 'Inactivo' ? 'checked' : ''; ?>> Inactivo</label>
							</div>
						</div>
					</div>

					<div class="admin-actions-row">
						<button type="submit" class="admin-btn">Guardar servicio</button>
						<a href="listar.php" class="admin-action">Cancelar</a>
					</div>
				</form>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
