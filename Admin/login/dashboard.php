<?php
require_once __DIR__ . '/../../Includes/conexion.php';

$pdo = obtenerConexionSalon();
$totalProductos = 0;
$totalServicios = 0;
$citasHoy = 0;
$totalVentas = 0.0;

if ($pdo) {
	$totalProductos = (int)$pdo->query('SELECT COUNT(*) FROM productos')->fetchColumn();
	$totalServicios = (int)$pdo->query('SELECT COUNT(*) FROM servicios')->fetchColumn();
	$citasHoy = (int)$pdo->query('SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()')->fetchColumn();
	$totalVentas = (float)$pdo->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'Aprobado'")->fetchColumn();
}

include(__DIR__ . '/../Includes/header.php');
?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<div class="admin-cards">
				<article class="admin-card">
					<h3>Productos</h3>
					<p><?php echo $totalProductos; ?></p>
				</article>

				<article class="admin-card">
					<h3>Servicios</h3>
					<p><?php echo $totalServicios; ?></p>
				</article>

				<article class="admin-card">
					<h3>Citas hoy</h3>
					<p><?php echo $citasHoy; ?></p>
				</article>

				<article class="admin-card">
					<h3>Ventas</h3>
					<p>₡<?php echo number_format($totalVentas, 0, ',', '.'); ?></p>
				</article>
			</div>

			<section class="admin-panel">
				<h2>Ultimos pedidos</h2>
				<p>Aqui se mostraran los pedidos recientes del sistema.</p>
			</section>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
