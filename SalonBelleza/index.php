<?php

require_once __DIR__ . '/../Admin/productos/_data.php';

include(__DIR__ . '/../Includes/header.php');
include(__DIR__ . '/../Includes/menu.php');

$productosDestacados = productos_cargar();
if (!$productosDestacados) {
    $productosDestacados = [
        [
            'id' => 1,
            'nombre' => 'Shampoo Profesional',
            'precio' => 8500,
            'imagen' => '/Assets/img/productos/shampoo.jpg',
            'categoria' => 'Shampoo',
        ],
        [
            'id' => 2,
            'nombre' => 'Cera para Cabello',
            'precio' => 5000,
            'imagen' => '/Assets/img/productos/cera.jpg',
            'categoria' => 'Acondicionadores',
        ],
        [
            'id' => 3,
            'nombre' => 'Aceite para Barba',
            'precio' => 7500,
            'imagen' => '/Assets/img/productos/aceite.jpg',
            'categoria' => 'Mascarillas',
        ],
        [
            'id' => 4,
            'nombre' => 'Mascarilla Capilar',
            'precio' => 12000,
            'imagen' => '/Assets/img/productos/mascarilla.jpg',
            'categoria' => 'Tintes',
        ],
    ];
}

$productosDestacados = array_slice($productosDestacados, 0, 4);

?>

<!-- Banner Principal -->

<section class="hero">

    <div class="overlay">

        <div class="hero-contenido">

            <h1>Realza tu belleza con nosotros</h1>

            <p>
                Cortes, color, manicure, pedicure,
                barbería y tratamientos profesionales.
            </p>

            <a href="cita.php" class="btn-principal">

                Reservar Cita

            </a>

        </div>

    </div>

</section>

<!-- Servicios -->

<section class="servicios">

    <h2>Nuestros Servicios</h2>

    <div class="contenedor-servicios">

        <div class="card-servicio">

            <img src="/Assets/img/servicios/corte.jpg" alt="Corte de Cabello">

            <h3>Corte de Cabello</h3>

            <p>Estilo moderno para hombres y mujeres.</p>

        </div>

        <div class="card-servicio">

            <img src="/Assets/img/servicios/manicure.jpg" alt="Manicure">

            <h3>Manicure</h3>

            <p>Cuidado profesional de tus manos.</p>

        </div>

        <div class="card-servicio">

            <img src="/Assets/img/servicios/pedicure.jpg" alt="Pedicure">

            <h3>Pedicure</h3>

            <p>Relajación y belleza para tus pies.</p>

        </div>

        <div class="card-servicio">

            <img src="/Assets/img/servicios/barba.jpg" alt="Barbería">

            <h3>Barbería</h3>

            <p>Cortes clásicos y modernos.</p>

        </div>

    </div>

</section>

<!-- Productos -->

<section class="productos">

<h2>Productos Destacados</h2>

<div class="contenedor-productos">

<?php foreach ($productosDestacados as $producto): ?>
    <?php $slugProducto = 'producto-' . (int)($producto['id'] ?? 1); ?>
    <div class="card-producto">
        <a href="producto.php?producto=<?php echo urlencode($slugProducto); ?>" class="card-producto-link">
            <img src="<?php echo htmlspecialchars((string)($producto['imagen'] ?? '/Assets/img/productos/default.jpg')); ?>" alt="<?php echo htmlspecialchars((string)($producto['nombre'] ?? 'Producto')); ?>">
            <h3><?php echo htmlspecialchars((string)($producto['nombre'] ?? 'Producto')); ?></h3>
            <p>₡<?php echo number_format((float)($producto['precio'] ?? 0), 0, ',', '.'); ?></p>
        </a>
    </div>
<?php endforeach; ?>

</div>

</section>

<!-- Promoción -->

<section class="promo">

    <img src="/Assets/img/promociones/promociones.jpg" alt="Promoción del Mes">

    <div class="promo-contenido">

        <h2>Promoción del Mes</h2>

        <h3>20% de descuento en Balayage</h3>

        <p>

            Agenda tu cita antes de finalizar el mes.

        </p>

        <a href="cita.php" class="btn-principal">

            Reservar Ahora

        </a>

    </div>

</section>

<!-- Elegirnos -->

<section class="elegirnos">

<h2>¿Por qué elegirnos?</h2>

<div class="beneficios">

<div>

    <p>Personal certificado</p>

</div>

<div>

    <p>Productos Premium</p>

</div>

<div>

    <p>Atención personalizada</p>

</div>

<div>

    <p>Ambiente moderno</p>

</div>

</div>

</section>

<!-- Opiniones -->

<section class="opiniones">

<h2>Opiniones</h2>

<div class="opinion">

    <span>★★★★★</span>

    <p>

        Excelente atención y calidad.

    </p>

    <strong>

        María Gómez

    </strong>

</div>

<div class="opinion">

    <span>★★★★★</span>

    <p>

        Mi barbería favorita.

    </p>

    <strong>

        Carlos Rojas

    </strong>

</div>

</section>

<!-- Contacto -->

<section class="contacto-home">

    <h2>Visítanos</h2>

    <div class="contacto-info">

        <p>San Pedro, San José Costa Rica</p>

        <p>2 piso, Mall San Pedro</p>

        <p>WhatsApp: 8910-2422</p>

        <p>Email: info@salon.com</p>

    </div>

</section>







<?php

include(__DIR__ . '/../Includes/footer.php');

?>