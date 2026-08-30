<?php
session_start();

$errores = $_SESSION['errores_producto'] ?? [];
$old = $_SESSION['old_producto'] ?? [];

unset($_SESSION['errores_producto'], $_SESSION['old_producto']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Agregar producto</h2>

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
							<label for="imagen">Imagen</label>
							<input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.webp" required>
						</div>

						<div class="admin-field">
							<label for="nombre">Producto</label>
							<input type="text" id="nombre" name="nombre" maxlength="160" required
								value="<?php echo htmlspecialchars($old['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field admin-field-full">
							<label for="descripcion">Descripción</label>
							<textarea id="descripcion" name="descripcion" rows="5" maxlength="2000" required><?php echo htmlspecialchars($old['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
						</div>

						<div class="admin-field">
							<label for="categoria">Categoria</label>
							<select id="categoria" name="categoria" required>
								<?php $cat = $old['categoria'] ?? ''; ?>
								<option value="">Seleccionar categoria</option>
								<option value="Shampoo" <?php echo $cat === 'Shampoo' ? 'selected' : ''; ?>>Shampoo</option>
								<option value="Acondicionadores" <?php echo $cat === 'Acondicionadores' ? 'selected' : ''; ?>>Acondicionadores</option>
								<option value="Mascarillas" <?php echo $cat === 'Mascarillas' ? 'selected' : ''; ?>>Mascarillas</option>
								<option value="Tintes" <?php echo $cat === 'Tintes' ? 'selected' : ''; ?>>Tintes</option>
							</select>
						</div>

						<div class="admin-field">
							<label for="precio">Precio</label>
							<input type="number" id="precio" name="precio" min="0" step="1" required
								value="<?php echo htmlspecialchars($old['precio'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="stock">Stock</label>
							<input type="number" id="stock" name="stock" min="0" step="1" required
								value="<?php echo htmlspecialchars($old['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
						</div>

						<div class="admin-field">
							<label for="oferta">Oferta</label>
							<?php $oferta = $old['oferta'] ?? 'No'; ?>
							<select id="oferta" name="oferta" required>
								<option value="Si" <?php echo $oferta === 'Si' ? 'selected' : ''; ?>>Si</option>
								<option value="No" <?php echo $oferta === 'No' ? 'selected' : ''; ?>>No</option>
							</select>
						</div>

						<div class="admin-field">
							<label for="estado">Estado</label>
							<?php $estado = $old['estado'] ?? 'Activo'; ?>
							<select id="estado" name="estado" required>
								<option value="Activo" <?php echo $estado === 'Activo' ? 'selected' : ''; ?>>Activo</option>
								<option value="Inactivo" <?php echo $estado === 'Inactivo' ? 'selected' : ''; ?>>Inactivo</option>
							</select>
						</div>
					</div>

					<div class="admin-actions-row">
						<button type="submit" class="admin-btn">Guardar producto</button>
						<a href="listar.php" class="admin-action">Cancelar</a>
					</div>
				</form>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
