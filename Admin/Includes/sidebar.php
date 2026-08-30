<?php
$uriActual = $_SERVER['REQUEST_URI'] ?? '';
$activeDashboard = strpos($uriActual, '/Admin/login/dashboard.php') !== false;
$activeProductos = strpos($uriActual, '/Admin/productos/') !== false;
$activeServicios = strpos($uriActual, '/Admin/servicios/') !== false;
$activeCategorias = strpos($uriActual, '/Admin/categorias/') !== false;
$activeCitas = strpos($uriActual, '/Admin/citas/') !== false;
$activePedidos = strpos($uriActual, '/Admin/pedidos/') !== false;
$activePagos = strpos($uriActual, '/Admin/pagos/') !== false;
$activeReportes = strpos($uriActual, '/Admin/reportes/') !== false;
?>

<aside class="admin-sidebar" id="adminSidebar">
	<div class="admin-logo">
		<img src="/Assets/img/logo/logodashboard.jpg" alt="Logo Alfredo Salon Estudio CR">
	</div>

	<nav class="admin-nav" aria-label="Menu administrador">
		<a class="admin-nav-link <?php echo $activeDashboard ? 'active' : ''; ?>" href="/Admin/login/dashboard.php">📊 Dashboard</a>
		<a class="admin-nav-link <?php echo $activeProductos ? 'active' : ''; ?>" href="/Admin/productos/listar.php">📦 Productos</a>
		<a class="admin-nav-link <?php echo $activeServicios ? 'active' : ''; ?>" href="/Admin/servicios/listar.php">✂️ Servicios</a>
		<a class="admin-nav-link <?php echo $activeCategorias ? 'active' : ''; ?>" href="/Admin/categorias/listar.php">🏷️ Categorias</a>
		<a class="admin-nav-link <?php echo $activeCitas ? 'active' : ''; ?>" href="/Admin/citas/listar.php">📅 Citas</a>
		<a class="admin-nav-link <?php echo $activePedidos ? 'active' : ''; ?>" href="/Admin/pedidos/listar.php">🛒 Pedidos</a>
		<a class="admin-nav-link <?php echo $activePagos ? 'active' : ''; ?>" href="/Admin/pagos/listar.php">💳 Pagos</a>
		<a class="admin-nav-link <?php echo $activeReportes ? 'active' : ''; ?>" href="/Admin/reportes/index.php">📈 Reportes</a>
		<a class="admin-nav-link" href="/Admin/login/logout.php">🚪 Cerrar sesion</a>
	</nav>
</aside>
