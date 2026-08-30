<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

include(__DIR__ . '/_data.php');

$mensajeOk = $_SESSION['ok_producto'] ?? '';
$mensajeError = $_SESSION['error_producto'] ?? '';
unset($_SESSION['ok_producto']);
unset($_SESSION['error_producto']);

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Listar productos</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success">
						<p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error">
						<p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
				<?php endif; ?>

				<div class="admin-toolbar">
					<a href="agregar.php" class="admin-btn">➕ Agregar producto</a>

					<input
						type="search"
						id="productoBuscar"
						class="admin-input"
						placeholder="🔍 Buscar producto..."
						aria-label="Buscar producto">

					<select id="productoCategoria" class="admin-select" aria-label="Filtrar por categoria">
						<option value="">📂 Filtrar por categoria</option>
						<option value="Shampoo">Shampoo</option>
						<option value="Acondicionadores">Acondicionadores</option>
						<option value="Mascarillas">Mascarillas</option>
						<option value="Tintes">Tintes</option>
					</select>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaProductos">
						<thead>
							<tr>
								<th>Imagen</th>
								<th>Producto</th>
								<th>Categoria</th>
								<th>Precio</th>
								<th>Stock</th>
								<th>Oferta</th>
								<th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($productosData as $producto): ?>
								<tr
									class="js-product-row"
									data-nombre="<?php echo htmlspecialchars(strtolower($producto['nombre']), ENT_QUOTES, 'UTF-8'); ?>"
									data-categoria="<?php echo htmlspecialchars($producto['categoria'], ENT_QUOTES, 'UTF-8'); ?>">
									<td>
										<img
											src="<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
											alt="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>"
											class="admin-product-img">
									</td>
									<td><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($producto['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td>₡<?php echo number_format((float)$producto['precio'], 0, ',', '.'); ?></td>
									<td><?php echo (int)$producto['stock']; ?></td>
									<td><?php echo $producto['oferta'] ? 'Si' : 'No'; ?></td>
									<td><?php echo htmlspecialchars($producto['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="editar.php?id=<?php echo (int)$producto['id']; ?>" class="admin-action edit" title="Editar">✏️ Editar</a>
										<a href="eliminar.php?id=<?php echo (int)$producto['id']; ?>" class="admin-action delete" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">🗑️ Eliminar</a>
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
