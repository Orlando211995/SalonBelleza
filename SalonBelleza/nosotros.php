<?php

include(__DIR__ . '/../Includes/header.php');

include(__DIR__ . '/../Includes/menu.php');

?>

<!-- Banner de Nosotros -->

<section class="hero" style="background: url('/Assets/img/banner/bannernosotros.jpg') center/cover no-repeat;">

</section>

<!-- Título de Nosotros -->

<section class="servicio-titulo">

	<div class="contenedor-servicio-titulo">

		<h2>Nosotros</h2>

	</div>

</section>

<!-- Contenido Nosotros -->

<section class="servicio-detalle">

	<div class="contenedor-servicio-detalle">

		<div class="servicio-imagen">

			<div class="nosotros-video-wrapper" aria-label="Video de Alfredo Salon Estudio CR">
				<video class="nosotros-video" controls autoplay muted loop playsinline preload="metadata" poster="/Assets/img/nosotros/salon1.jpg">
					<source src="/Assets/img/nosotros/video.mp4" type="video/mp4">
					Tu navegador no soporta el video.
				</video>
			</div>

		</div>

		<div class="servicio-texto">

			<h3>Pasión por la belleza y atención personalizada</h3>

			<p>
				En Alfredo Salon Estudio CR combinamos experiencia, tecnica y creatividad para
				ofrecer servicios que realzan tu estilo y tu confianza. Nuestro equipo trabaja
				con dedicacion en cada detalle para brindarte una experiencia comoda y profesional.
			</p>

			<p>
				Nos enfocamos en escuchar lo que necesitas para recomendarte cortes, color,
				barberia y cuidado integral que se adapten a tu imagen. Queremos que cada visita
				sea un espacio de bienestar, renovacion y resultados que superen tus expectativas.
			</p>

			<a href="cita.php" class="btn-vermas">Reservar cita</a>

		</div>

	</div>

</section>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>
