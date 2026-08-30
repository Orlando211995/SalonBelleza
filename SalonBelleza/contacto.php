<?php

include(__DIR__ . '/../Includes/header.php');

include(__DIR__ . '/../Includes/menu.php');

?>

<!-- Banner de Contacto -->

<section class="hero" style="background: url('/Assets/img/banner/bannercontacto.jpg') center/cover no-repeat;">

</section>

<!-- Titulo de Contacto -->

<section class="servicio-titulo">

	<div class="contenedor-servicio-titulo">

		<h2>Contacto</h2>

	</div>

</section>

<!-- Informacion de Contacto -->

<section class="contacto-home">

	<div class="container">

		<div class="contacto-grid">

			<div class="contacto-info">

				<h3>Hablemos</h3>

				<p>
					Estamos para ayudarte. Escribenos o visitanos para reservar tu cita y recibir atencion personalizada.
				</p>

				<p>
					Telefono: 8910-2422
				</p>

				<p>
					Horario: Lunes a Sabado, 9:00 AM - 7:00 PM
				</p>

				<a href="https://wa.me/50689102422?text=Hola%20Alfredo%20Salon%20Estudio%20CR%2C%20quiero%20informacion." class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
					<i class="bi bi-whatsapp"></i> Escribir por WhatsApp
				</a>

				<a href="cita.php" class="btn-vermas">Reservar cita</a>

			</div>

			<div class="contacto-form-card">

				<h3>Formulario de contacto</h3>

				<div id="contactoMensaje"></div>

				<form id="contactoFormulario" class="contacto-form" novalidate>

					<div class="contacto-campo">
						<label for="nombre">Nombre completo</label>
						<input type="text" id="nombre" name="nombre" maxlength="120" required>
					</div>

					<div class="contacto-campo">
						<label for="telefono">Telefono</label>
						<input type="text" id="telefono" name="telefono" maxlength="30" required>
					</div>

					<div class="contacto-campo">
						<label for="correo">Correo</label>
						<input type="email" id="correo" name="correo" maxlength="120" required>
					</div>

					<div class="contacto-campo">
						<label for="asunto">Asunto</label>
						<input type="text" id="asunto" name="asunto" maxlength="150" required>
					</div>

					<div class="contacto-campo">
						<label for="mensaje">Mensaje</label>
						<textarea id="mensaje" name="mensaje" rows="5" maxlength="1200" required></textarea>
					</div>

					<button type="submit" class="btn-vermas" id="btnEnviar">Enviar mensaje</button>

				</form>

			</div>

		</div>

	</div>

</section>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

<script>
// Configura Email.js - REEMPLAZA ESTAS CREDENCIALES
const EMAILJS_PUBLIC_KEY = '23_0Tw-O7ELm5-G_y';
const EMAILJS_SERVICE_ID = 'service_o1n0och';
const EMAILJS_TEMPLATE_ID = 'template_sqyt62v';

// Inicializar Email.js
emailjs.init(EMAILJS_PUBLIC_KEY);

// Elementos del formulario
const formulario = document.getElementById('contactoFormulario');
const btnEnviar = document.getElementById('btnEnviar');
const divMensaje = document.getElementById('contactoMensaje');

// Manejar envío del formulario
formulario.addEventListener('submit', async (e) => {
	e.preventDefault();

	// Obtener valores
	const nombre = document.getElementById('nombre').value.trim();
	const telefono = document.getElementById('telefono').value.trim();
	const correo = document.getElementById('correo').value.trim();
	const asunto = document.getElementById('asunto').value.trim();
	const mensaje = document.getElementById('mensaje').value.trim();

	// Validar campos
	const errores = [];
	if (!nombre) errores.push('El nombre es obligatorio.');
	if (!telefono) errores.push('El teléfono es obligatorio.');
	if (!correo || !validarEmail(correo)) errores.push('Debes ingresar un correo válido.');
	if (!asunto) errores.push('El asunto es obligatorio.');
	if (!mensaje) errores.push('El mensaje es obligatorio.');

	if (errores.length > 0) {
		mostrarError(errores);
		return;
	}

	// Desactivar botón mientras se envía
	btnEnviar.disabled = true;
	btnEnviar.textContent = 'Enviando...';
	divMensaje.innerHTML = '';

	try {
		// Enviar con Email.js
		await emailjs.send(
			EMAILJS_SERVICE_ID,
			EMAILJS_TEMPLATE_ID,
			{
				from_name: nombre,
				from_email: correo,
				phone: telefono,
				subject: asunto,
				message: mensaje,
				to_email: 'alfredosaloncr@gmail.com',
			}
		);

		// Mostrar éxito
		mostrarExito('Tu mensaje fue enviado correctamente. Pronto te contactaremos.');
		formulario.reset();

	} catch (error) {
		console.error('Error:', error);
		mostrarError(['No se pudo enviar el mensaje. Intenta nuevamente.']);
	} finally {
		btnEnviar.disabled = false;
		btnEnviar.textContent = 'Enviar mensaje';
	}
});

// Función para validar email
function validarEmail(email) {
	const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	return regex.test(email);
}

// Mostrar mensaje de éxito
function mostrarExito(mensaje) {
	divMensaje.innerHTML = `<div class="contacto-alert exito"><p>${mensaje}</p></div>`;
}

// Mostrar mensaje de error
function mostrarError(listaErrores) {
	const erroresHTML = listaErrores.map(e => `<p>${e}</p>`).join('');
	divMensaje.innerHTML = `<div class="contacto-alert error">${erroresHTML}</div>`;
}
</script>
