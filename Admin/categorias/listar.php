<?php
session_start();

include(__DIR__ . '/_data.php');

$mensajeOk = $_SESSION['ok_categoria'] ?? '';
$mensajeError = $_SESSION['error_categoria'] ?? '';
unset($_SESSION['ok_categoria'], $_SESSION['error_categoria']);

$filtroQ = trim($_GET['q'] ?? '');
$filtroTipo = trim($_GET['tipo'] ?? '');

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Listar categorias</h2>

				<?php if ($mensajeOk): ?>
					<div class="admin-alert success"><p><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<?php if ($mensajeError): ?>
					<div class="admin-alert error"><p><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p></div>
				<?php endif; ?>

				<div class="admin-toolbar">
					<a href="agregar.php" class="admin-btn">➕ Agregar categoria</a>

					<input type="search" id="categoriaBuscar" class="admin-input" placeholder="🔍 Buscar categoria..." aria-label="Buscar categoria" value="<?php echo htmlspecialchars($filtroQ, ENT_QUOTES, 'UTF-8'); ?>">

					<select id="categoriaTipo" class="admin-select" aria-label="Filtrar por tipo">
						<option value="">📂 Filtrar por tipo</option>
						<option value="Producto" <?php echo $filtroTipo === 'Producto' ? 'selected' : ''; ?>>Producto</option>
						<option value="Servicio" <?php echo $filtroTipo === 'Servicio' ? 'selected' : ''; ?>>Servicio</option>
						<option value="Ambos" <?php echo $filtroTipo === 'Ambos' ? 'selected' : ''; ?>>Ambos</option>
					</select>
				</div>

				<div class="admin-table-wrap">
					<table class="admin-table" id="tablaCategorias">
						<thead>
							<tr>
								<th>Categoria</th>
								<th>Descripcion</th>
								<th>Tipo</th>
								<th>Estado</th>
								<th>Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($categoriasData as $categoria): ?>
								<?php
								$nombreLower = strtolower((string)($categoria['nombre'] ?? ''));
								if ($filtroQ !== '' && strpos($nombreLower, strtolower($filtroQ)) === false) {
									continue;
								}
								if ($filtroTipo !== '' && ($categoria['tipo'] ?? '') !== $filtroTipo) {
									continue;
								}
								?>
								<tr class="js-category-row"
									data-nombre="<?php echo htmlspecialchars(strtolower($categoria['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
									data-tipo="<?php echo htmlspecialchars($categoria['tipo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
									<td><?php echo htmlspecialchars($categoria['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($categoria['descripcion'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($categoria['tipo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td><?php echo htmlspecialchars($categoria['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
									<td class="admin-actions">
										<a href="editar.php?id=<?php echo (int)($categoria['id'] ?? 0); ?>" class="admin-action edit" title="Editar">✏️</a>
										<a href="eliminar.php?id=<?php echo (int)($categoria['id'] ?? 0); ?>" class="admin-action delete" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta categoria?');">🗑️</a>
										<a href="ver.php?id=<?php echo (int)($categoria['id'] ?? 0); ?>" class="admin-action" title="Ver">👁️</a>
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
