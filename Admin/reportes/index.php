<?php include(__DIR__ . '/../Includes/header.php'); ?>

<div class="admin-layout">
	<?php include(__DIR__ . '/../Includes/sidebar.php'); ?>

	<main class="admin-main">
		<?php include(__DIR__ . '/../Includes/topbar.php'); ?>

		<section class="admin-content">
			<section class="admin-panel">
				<h2>Reportes</h2>
				<p>Selecciona un reporte para ver su detalle.</p>
			</section>

			<div class="admin-cards">
				<a class="admin-card" href="/Admin/reportes/ventas.php" style="text-decoration:none;color:inherit;">
					<h3>📈 Ventas</h3>
					<p>Ver ingresos y periodos</p>
				</a>

				<a class="admin-card" href="/Admin/reportes/productos.php" style="text-decoration:none;color:inherit;">
					<h3>📦 Productos</h3>
					<p>Mas vendidos e inventario</p>
				</a>

				<a class="admin-card" href="/Admin/reportes/servicios.php" style="text-decoration:none;color:inherit;">
					<h3>✂️ Servicios</h3>
					<p>Demanda de servicios</p>
				</a>

				<a class="admin-card" href="/Admin/reportes/citas.php" style="text-decoration:none;color:inherit;">
					<h3>📅 Citas</h3>
					<p>Flujo por estado y fecha</p>
				</a>

				<a class="admin-card" href="/Admin/reportes/pagos.php" style="text-decoration:none;color:inherit;">
					<h3>💳 Pagos</h3>
					<p>Metodos y estados</p>
				</a>

				<a class="admin-card" href="/Admin/reportes/inventario.php" style="text-decoration:none;color:inherit;">
					<h3>📊 Inventario</h3>
					<p>Stock y alertas</p>
				</a>
			</div>
		</section>
	</main>
</div>

<?php include(__DIR__ . '/../Includes/footer.php'); ?>
