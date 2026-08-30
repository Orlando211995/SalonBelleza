<?php
session_start();

require_once __DIR__ . '/../Admin/pedidos/_data.php';
require_once __DIR__ . '/../Admin/pagos/_data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nombre = trim($_POST['nombre'] ?? '');
	$apellido = trim($_POST['apellido'] ?? '');
	$telefono = trim($_POST['telefono'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$direccion = trim($_POST['direccion'] ?? '');
	$tipoEntrega = trim($_POST['tipo_entrega'] ?? 'envio');
	$metodoPago = 'SINPE';
	$carritoJson = trim($_POST['carrito_json'] ?? '[]');
	$observaciones = trim($_POST['observaciones'] ?? '');

	$errores = [];

	if ($nombre === '') {
		$errores[] = 'El nombre es obligatorio.';
	}
	if ($apellido === '') {
		$errores[] = 'El apellido es obligatorio.';
	}
	if ($telefono === '') {
		$errores[] = 'El telefono es obligatorio.';
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errores[] = 'El correo electronico no es valido.';
	}
	if ($tipoEntrega !== 'envio' && $tipoEntrega !== 'retiro') {
		$errores[] = 'Debes seleccionar un tipo de entrega valido.';
	}
	if ($tipoEntrega === 'envio' && $direccion === '') {
		$errores[] = 'La direccion es obligatoria para envio por Correos.';
	}

	$carrito = json_decode($carritoJson, true);
	if (!is_array($carrito) || !$carrito) {
		$errores[] = 'Tu carrito esta vacio.';
	}

	if (!isset($_FILES['comprobante']) || ($_FILES['comprobante']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
		$errores[] = 'Debes adjuntar el comprobante de pago.';
	}

	$itemsPedido = [];
	$subtotal = 0;

	if (!$errores) {
		foreach ($carrito as $item) {
			$producto = trim((string)($item['nombre'] ?? 'Producto'));
			$cantidad = max(1, (int)($item['cantidad'] ?? 1));
			$precio = (float)($item['precio'] ?? 0);

			if ($precio < 0) {
				$precio = 0;
			}

			$itemsPedido[] = [
				'producto' => $producto,
				'cantidad' => $cantidad,
				'precio' => $precio,
			];

			$subtotal += $cantidad * $precio;
		}
	}

	$comprobanteUrl = '';
	if (!$errores) {
		$nombreOriginal = $_FILES['comprobante']['name'] ?? '';
		$extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
		$permitidas = ['jpg', 'jpeg', 'png', 'pdf'];

		if (!in_array($extension, $permitidas, true)) {
			$errores[] = 'El comprobante debe ser JPG, JPEG, PNG o PDF.';
		} else {
			$destinoDir = __DIR__ . '/../uploads/comprobantes';
			if (!is_dir($destinoDir)) {
				mkdir($destinoDir, 0777, true);
			}

			$archivo = 'cmp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
			$rutaDestino = $destinoDir . '/' . $archivo;
			if (!move_uploaded_file($_FILES['comprobante']['tmp_name'], $rutaDestino)) {
				$errores[] = 'No se pudo guardar el comprobante.';
			} else {
				$comprobanteUrl = '/uploads/comprobantes/' . $archivo;
			}
		}
	}

	if ($errores) {
		$_SESSION['errores_checkout'] = $errores;
		$_SESSION['old_checkout'] = [
			'nombre' => $nombre,
			'apellido' => $apellido,
			'telefono' => $telefono,
			'email' => $email,
			'tipo_entrega' => $tipoEntrega,
			'direccion' => $direccion,
			'observaciones' => $observaciones,
		];
		header('Location: checkout.php');
		exit;
	}

	if ($tipoEntrega === 'retiro') {
		$direccion = 'Retiro en salon';
	}

	$envio = ($itemsPedido && $tipoEntrega === 'envio') ? 3500 : 0;
	$total = $subtotal + $envio;

	$pedidos = pedidos_cargar();
	$maxId = 0;
	foreach ($pedidos as $p) {
		$idP = (int)($p['id'] ?? 0);
		if ($idP > $maxId) {
			$maxId = $idP;
		}
	}

	$nuevoId = $maxId + 1;
	$numeroPedido = 'PED-' . str_pad((string)(1000 + $nuevoId), 4, '0', STR_PAD_LEFT);

	$pedidos[] = [
		'id' => $nuevoId,
		'numero_pedido' => $numeroPedido,
		'cliente' => trim($nombre . ' ' . $apellido),
		'telefono' => $telefono,
		'correo' => $email,
		'direccion' => $direccion,
		'metodo_pago' => $metodoPago,
		'estado' => 'Pendiente',
		'tipo_entrega' => $tipoEntrega,
		'costo_envio' => $envio,
		'total' => $total,
		'observaciones' => $observaciones,
		'fecha' => date('Y-m-d H:i:s'),
		'items' => $itemsPedido,
		'comprobante' => $comprobanteUrl,
	];

	pedidos_guardar($pedidos);

	$pagos = pagos_cargar();
	$maxPagoId = 0;
	foreach ($pagos as $pagoExistente) {
		$idPago = (int)($pagoExistente['id'] ?? 0);
		if ($idPago > $maxPagoId) {
			$maxPagoId = $idPago;
		}
	}

	$pagos[] = [
		'id' => $maxPagoId + 1,
		'numero_pago' => 'P' . str_pad((string)($maxPagoId + 1), 3, '0', STR_PAD_LEFT),
		'cliente' => trim($nombre . ' ' . $apellido),
		'telefono' => $telefono,
		'correo' => $email,
		'tipo' => 'Pedido',
		'numero' => $numeroPedido,
		'metodo' => 'SINPE Movil',
		'monto' => $total,
		'fecha' => date('Y-m-d H:i:s'),
		'estado' => 'Pendiente',
		'comprobante' => $comprobanteUrl,
		'observaciones' => $observaciones,
	];

	pagos_guardar($pagos);

	$_SESSION['ok_checkout'] = 'Pedido recibido correctamente. Numero: ' . $numeroPedido;
	header('Location: checkout.php?pedido_ok=1');
	exit;
}

$erroresCheckout = $_SESSION['errores_checkout'] ?? [];
$oldCheckout = $_SESSION['old_checkout'] ?? [];
$okCheckout = $_SESSION['ok_checkout'] ?? '';
unset($_SESSION['errores_checkout'], $_SESSION['old_checkout'], $_SESSION['ok_checkout']);

include(__DIR__ . '/../Includes/header.php');

include(__DIR__ . '/../Includes/menu.php');

?>

<section class="servicio-titulo">

	<div class="contenedor-servicio-titulo">

		<h2>Checkout</h2>

	</div>

</section>

<section class="checkout-page">

	<div class="checkout-wrapper">

		<div class="checkout-formulario">

			<h3>Detalles de facturacion</h3>

			<?php if ($okCheckout): ?>
				<div class="contacto-alert exito"><p><?php echo htmlspecialchars($okCheckout, ENT_QUOTES, 'UTF-8'); ?></p></div>
			<?php endif; ?>

			<?php if ($erroresCheckout): ?>
				<div class="contacto-alert error">
					<?php foreach ($erroresCheckout as $error): ?>
						<p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<form id="checkout-form" action="checkout.php" method="post" enctype="multipart/form-data">

				<input type="hidden" id="checkout-carrito-json" name="carrito_json" value="[]">

				<div class="checkout-grid-2">

					<div class="checkout-field">

						<label for="nombre">Nombre</label>

						<input id="nombre" name="nombre" type="text" required value="<?php echo htmlspecialchars((string)($oldCheckout['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

					</div>

					<div class="checkout-field">

						<label for="apellido">Apellido</label>

						<input id="apellido" name="apellido" type="text" required value="<?php echo htmlspecialchars((string)($oldCheckout['apellido'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

					</div>

				</div>

				<div class="checkout-field">

					<label for="telefono">Telefono</label>

					<input id="telefono" name="telefono" type="tel" required value="<?php echo htmlspecialchars((string)($oldCheckout['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

				</div>

				<div class="checkout-field">

					<label for="email">Correo electronico</label>

					<input id="email" name="email" type="email" required value="<?php echo htmlspecialchars((string)($oldCheckout['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">

				</div>

				<div class="checkout-field">

					<label for="tipo_entrega">Tipo de entrega</label>

					<?php $tipoEntregaOld = (string)($oldCheckout['tipo_entrega'] ?? 'envio'); ?>
					<select id="tipo_entrega" name="tipo_entrega" required>
						<option value="envio" <?php echo $tipoEntregaOld === 'envio' ? 'selected' : ''; ?>>Envio por Correos de Costa Rica (₡3.500)</option>
						<option value="retiro" <?php echo $tipoEntregaOld === 'retiro' ? 'selected' : ''; ?>>Recoger en salon (₡0)</option>
					</select>

				</div>

				<div class="checkout-field">

					<label for="direccion">Direccion completa</label>

					<textarea id="direccion" name="direccion" rows="3" <?php echo $tipoEntregaOld === 'envio' ? 'required' : ''; ?>><?php echo htmlspecialchars((string)($oldCheckout['direccion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>

				</div>

				<div class="checkout-field">

					<label for="observaciones">Observaciones (opcional)</label>

					<textarea id="observaciones" name="observaciones" rows="2"><?php echo htmlspecialchars((string)($oldCheckout['observaciones'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>

				</div>

				<div class="checkout-field checkout-pago-box">

					<div class="checkout-pago-titulo">

						<span>Paga con SINPE Movil</span>

						<img src="/Assets/img/logo/sinpemovil.jpg" alt="SINPE Movil" class="checkout-pago-logo">

					</div>

					<p><strong>Metodo de pago en compras web:</strong> SINPE Movil (unico metodo disponible).</p>

					<div class="checkout-comprobante-box">

						<p>
							Hace tu pago al <strong>8910-2422</strong> y adjunta el comprobante usando el boton de abajo.
							En cuanto lo validemos, procesaremos tu orden de inmediato.
						</p>

						<label for="comprobante">Adjuntar comprobante de pago</label>

						<input id="comprobante" name="comprobante" type="file" accept=".jpg,.jpeg,.png,.pdf" required>

					</div>

				</div>

				<button type="submit" class="checkout-btn">Realizar pedido</button>

				<p style="margin-top:14px; line-height:1.7; color:#333;">
					<strong>Sobre envios</strong><br>
					¡Llevamos la belleza hasta tu puerta! Nos encargamos de que tus favoritos lleguen seguros con Correos de Costa Rica.
					El costo del envio es de solo <strong>₡3.500</strong> y tendras tu pedido contigo en un plazo de
					<strong>2 a 4 dias habiles</strong>. ¡Asi de facil!
				</p>

			</form>

		</div>

		<aside class="checkout-resumen">

			<h3>Tu pedido</h3>

			<div id="checkout-items"></div>

			<div class="checkout-totales">

				<div>

					<span>Subtotal</span>

					<strong id="checkout-subtotal">₡0,00</strong>

				</div>

				<div>

					<span>Envio</span>

					<strong id="checkout-envio">₡0,00</strong>

				</div>

				<div class="checkout-total-final">

					<span>Total</span>

					<strong id="checkout-total">₡0,00</strong>

				</div>

			</div>

		</aside>

	</div>

</section>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>
