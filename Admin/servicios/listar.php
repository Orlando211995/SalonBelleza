<?php
session_start();

include(__DIR__ . '/_data.php');

$mensajeOk = $_SESSION['ok_servicio'] ?? '';
$mensajeError = $_SESSION['error_servicio'] ?? '';
unset($_SESSION['ok_servicio'], $_SESSION['error_servicio']);

$filtroQ = trim($_GET['q'] ?? '');
$filtroCategoria = trim($_GET['categoria'] ?? '');

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Listar servicios</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error"><p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<div class="admin-toolbar">
					<a href="agregar.php" class="admin-btn">➕ Agregar servicio</a>

					<input type="search" id="servicioBuscar" class="admin-input" placeholder="🔍 Buscar servicio..." aria-label="Buscar servicio" value="<?php echo htmlspecialchars($filtroQ, ENT_QUOTES, 'UTF-8'); ?>">

					<select id="servicioCategoria" class="admin-select" aria-label="Filtrar por categoria">
						<option value="">📂 Filtrar por categoria</option>
						<option value="Cortes" <?php echo $filtroCategoria === 'Cortes' ? 'selected' : ''; ?>>Cortes</option>
						<option value="Coloracion" <?php echo $filtroCategoria === 'Coloracion' ? 'selected' : ''; ?>>Coloracion</option>
						<option value="Tratamientos" <?php echo $filtroCategoria === 'Tratamientos' ? 'selected' : ''; ?>>Tratamientos</option>
						<option value="Barberia" <?php echo $filtroCategoria === 'Barberia' ? 'selected' : ''; ?>>Barberia</option>
						<option value="Manicure" <?php echo $filtroCategoria === 'Manicure' ? 'selected' : ''; ?>>Manicure</option>
						<option value="Pedicure" <?php echo $filtroCategoria === 'Pedicure' ? 'selected' : ''; ?>>Pedicure</option>
						<option value="Peinados" <?php echo $filtroCategoria === 'Peinados' ? 'selected' : ''; ?>>Peinados</option>
						<option value="Maquillaje" <?php echo $filtroCategoria === 'Maquillaje' ? 'selected' : ''; ?>>Maquillaje</option>
						<option value="Faciales" <?php echo $filtroCategoria === 'Faciales' ? 'selected' : ''; ?>>Faciales</option>
						<option value="Depilacion" <?php echo $filtroCategoria === 'Depilacion' ? 'selected' : ''; ?>>Depilacion</option>
					</select>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaServicios">
						<thead>
							<tr>
								<th>Imagen</th>
								<th>Servicio</th>
								<th>Categoria</th>
								<th>Duracion</th>
								<th>Precio</th>
								<th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($serviciosData as $servicio): ?>
								<?php
								$nombreLower = strtolower((string)($servicio['nombre'] ?? ''));
								if ($filtroQ !== '' && strpos($nombreLower, strtolower($filtroQ)) === false) {
									continue;
								}
								if ($filtroCategoria !== '' && ($servicio['categoria'] ?? '') !== $filtroCategoria) {
									continue;
								}
								?>
								<tr class="js-service-row"
									data-nombre="<?php echo htmlspecialchars(strtolower($servicio['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-categoria="<?php echo htmlspecialchars($servicio['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
									<td>
										<img src="<?php echo htmlspecialchars($servicio['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
											alt="<?php echo htmlspecialchars($servicio['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
											class="admin-product-img">
									</td>
									<td><?php echo htmlspecialchars($servicio['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($servicio['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><span class="admin-duration"><?php echo (int)($servicio['duracion'] ?? 0); ?> min</span></td>
									<td>₡<?php echo number_format((float)($servicio['precio'] ?? 0), 0, ',', '.'); ?></td>
									<td><?php echo htmlspecialchars($servicio['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="editar.php?id=<?php echo (int)($servicio['id'] ?? 0); ?>" class="admin-action edit" title="Editar">✏️</a>
										<a href="eliminar.php?id=<?php echo (int)($servicio['id'] ?? 0); ?>" class="admin-action delete" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar este servicio?');">🗑️</a>
										<a href="ver.php?id=<?php echo (int)($servicio['id'] ?? 0); ?>" class="admin-action" title="Ver">👁️</a>
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
