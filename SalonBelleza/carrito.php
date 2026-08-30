<?php

include(__DIR__ . '/../Includes/header.php');

include(__DIR__ . '/../Includes/menu.php');

?>

<section class="servicio-titulo">

	<div class="contenedor-servicio-titulo">

		<h2>Carrito</h2>

	</div>

</section>

<section class="carrito-page">

	<div class="carrito-wrapper">

		<div id="carrito-contenido"></div>

		<div class="carrito-resumen">

			<h3>Total</h3>

			<p id="carrito-total">₡0,00</p>

			<div class="carrito-acciones">

				<a href="productos.php" class="carrito-btn carrito-btn-secundario">Seguir comprando</a>

				<a href="checkout.php" class="carrito-btn">Finalizar compra</a>

			</div>

		</div>

	</div>

</section>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>
