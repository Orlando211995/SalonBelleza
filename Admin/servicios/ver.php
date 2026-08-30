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

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Detalle del servicio</h2>

				<div class="admin-form-grid">
					<div class="admin-field">
						<label>Imagen</label>
						<img src="<?php echo htmlspecialchars($servicio['imagen'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Servicio" class="admin-product-img">
					</div>
					<div class="admin-field">
						<label>Nombre</label>
						<p><?php echo htmlspecialchars($servicio['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
					<div class="admin-field">
						<label>Categoria</label>
						<p><?php echo htmlspecialchars($servicio['categoria'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
					<div class="admin-field">
						<label>Duracion</label>
						<p><?php echo (int)($servicio['duracion'] ?? 0); ?> min</p>
					</div>
					<div class="admin-field">
						<label>Precio</label>
						<p>₡<?php echo number_format((float)($servicio['precio'] ?? 0), 0, ',', '.'); ?></p>
					</div>
					<div class="admin-field">
						<label>Estado</label>
						<p><?php echo htmlspecialchars($servicio['estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
					</div>
					<div class="admin-field" style="grid-column: 1 / -1;">
						<label>Descripcion</label>
						<p><?php echo nl2br(htmlspecialchars($servicio['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
					</div>
				</div>

				<div class="admin-actions-row" style="margin-top: 14px;">
					<a href="editar.php?id=<?php echo (int)($servicio['id'] ?? 0); ?>" class="admin-btn">Editar servicio</a>
					<a href="listar.php" class="admin-action">Volver</a>
				</div>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
