<?php

require_once __DIR__ . '/../Admin/productos/_data.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

include(__DIR__ . '/../Includes/header.php');
include(__DIR__ . '/../Includes/menu.php');

function separarDescripcionYBeneficios(string $descripcion): array
{
    $descripcion = trim($descripcion);
    if ($descripcion === '') {
        return [
            'descripcion' => 'Producto profesional de la más alta calidad para un cuidado excepcional.',
            'caracteristicas' => ['Calidad premium', 'Producto profesional'],
        ];
    }

    $descripcionFinal = $descripcion;
    $caracteristicas = [];

    $posicionInicioBeneficios = stripos($descripcion, 'Sin ');
    if ($posicionInicioBeneficios !== false) {
        $descripcionFinal = trim(substr($descripcion, 0, $posicionInicioBeneficios));
        $resto = trim(substr($descripcion, $posicionInicioBeneficios));

        if ($resto !== '') {
            $beneficios = preg_split('/\s+(?=(?:Sin|sin)\s+)/i', $resto);
            $caracteristicas = array_values(array_filter(array_map('trim', $beneficios), fn($item) => $item !== ''));
        }
    }

    $lineas = preg_split('/\r\n|\n|\r/', $descripcionFinal);
    $lineas = array_values(array_filter(array_map('trim', $lineas), fn($linea) => $linea !== ''));
    if (!empty($lineas)) {
        $descripcionFinal = trim(implode(' ', $lineas));
    }

    if (!$caracteristicas) {
        $caracteristicas = ['Producto profesional', 'Calidad premium'];
    }

    return [
        'descripcion' => $descripcionFinal !== '' ? $descripcionFinal : 'Producto profesional de la más alta calidad para un cuidado excepcional.',
        'caracteristicas' => array_values($caracteristicas),
    ];
}

$productos = [];
foreach (productos_cargar() as $producto) {
    $slug = 'producto-' . (int)($producto['id'] ?? 0);
    $descripcion = trim((string)($producto['descripcion'] ?? ''));
    $datosProducto = separarDescripcionYBeneficios($descripcion);

    $productos[$slug] = [
        'nombre' => (string)($producto['nombre'] ?? 'Producto'),
        'precio' => '₡' . number_format((float)($producto['precio'] ?? 0), 0, ',', '.'),
        'imagen' => (string)($producto['imagen'] ?? '/Assets/img/productos/default.jpg'),
        'alt' => (string)($producto['nombre'] ?? 'Producto'),
        'descripcion' => $datosProducto['descripcion'],
        'caracteristicas' => $datosProducto['caracteristicas'],
    ];
}

if (!$productos) {
    $productos = [
        'producto-1' => [
            'nombre' => 'ARGAN OIL SHAMPOO',
            'precio' => '₡8.190,00',
            'imagen' => '/Assets/img/productos/ArganShampoo.jpg',
            'alt' => 'Argan Oil Shampoo',
            'descripcion' => 'Shampoo con aceite de argán, rico en ácidos grasos que mantienen el cabello con una hidratación óptima. Su elevada cantidad de vitaminas antioxidantes como la vitamina C y E nutren intensamente.',
            'caracteristicas' => ['Sin sales - salt free', 'Sin sulfatos - sulfate free', 'Sin gluten - gluten free', 'Sin parabenos - paraben free'],
        ],
        'producto-2' => [
            'nombre' => 'ARGAN OIL CONDITIONER',
            'precio' => '₡21.420,00',
            'imagen' => '/Assets/img/productos/ArganConditioner.jpg',
            'alt' => 'Argan Oil Conditioner',
            'descripcion' => 'Acondicionador con aceite de argán que ayuda a desenredar, suavizar y fortalecer el cabello. Ideal para recuperar hidratación, brillo y manejabilidad en cabellos secos o maltratados.',
            'caracteristicas' => ['Hidratación profunda', 'Mayor suavidad y brillo', 'Ayuda a reducir el frizz', 'Uso diario'],
        ],
        'producto-3' => [
            'nombre' => 'ARGAN OIL MASK',
            'precio' => '₡16.170,00',
            'imagen' => '/Assets/img/productos/ArganMask.jpg',
            'alt' => 'Argan Oil Mask',
            'descripcion' => 'Mascarilla capilar nutritiva que repara y revitaliza la fibra desde la raíz hasta las puntas. Su fórmula aporta hidratación intensa para un cabello más fuerte, sedoso y brillante.',
            'caracteristicas' => ['Nutrición intensiva', 'Reparación de puntas', 'Cabello más resistente', 'Acabado sedoso'],
        ],
        'producto-4' => [
            'nombre' => '# KRAY SEMI-PERM LIGHT BLUE',
            'precio' => '₡8.050,00',
            'imagen' => '/Assets/img/productos/TinteLIGHT%20BLUE.jpg',
            'alt' => 'Kray Semi-Perm Light Blue',
            'descripcion' => 'El color semipermanente L3VEL3 KRAY está enriquecido con ingredientes nutritivos como aloe vera, aceite de argán orgánico y aceite de semilla de girasol.',
            'caracteristicas' => ['Formato semipermanente', 'Con aloe vera y aceite de argán orgánico', 'Protección UV y anti decoloración', 'Tecnología BondFusion'],
        ],
    ];
}

$slugProducto = $_GET['producto'] ?? array_key_first($productos);
if (!isset($productos[$slugProducto])) {
    $slugProducto = array_key_first($productos);
}

$productoActual = $productos[$slugProducto];
$claseImagenProducto = $slugProducto === 'producto-4' ? 'img-tinte-light-blue-detalle' : '';

?>

<!-- Detalle de Producto -->

<section class="producto-detalle">

    <div class="contenedor-producto">

        <!-- Imagen del Producto -->

        <div class="producto-imagen">

            <img src="<?php echo htmlspecialchars($productoActual['imagen']); ?>" alt="<?php echo htmlspecialchars($productoActual['alt']); ?>" class="<?php echo $claseImagenProducto; ?>">

        </div>

        <!-- Información del Producto -->

        <div class="producto-info">

            <h1><?php echo htmlspecialchars($productoActual['nombre']); ?></h1>

            <div class="producto-precio">

                <span class="precio"><?php echo htmlspecialchars($productoActual['precio']); ?></span>

                <span class="corazon">♡</span>

            </div>

            <!-- Selector de cantidad y botón -->

            <div class="producto-acciones">

                <input type="number" min="1" value="1" class="cantidad-input">

                <button class="btn-agregar" data-producto-id="<?php echo htmlspecialchars($slugProducto); ?>" data-producto-nombre="<?php echo htmlspecialchars($productoActual['nombre']); ?>" data-producto-precio="<?php echo htmlspecialchars($productoActual['precio']); ?>" data-producto-imagen="<?php echo htmlspecialchars($productoActual['imagen']); ?>" data-producto-url="producto.php?producto=<?php echo urlencode($slugProducto); ?>">AGREGAR AL CARRITO</button>

            </div>

            <!-- Descripción del Producto -->

            <div class="producto-descripcion">

                <p><?php echo htmlspecialchars($productoActual['descripcion']); ?></p>

                <?php if (!empty($productoActual['caracteristicas'])): ?>
                    <ul class="caracteristicas">
                        <?php foreach ($productoActual['caracteristicas'] as $caracteristica): ?>
                            <li><?php echo htmlspecialchars($caracteristica); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            </div>

            <!-- Compra Fácil sin Riesgos -->

            <div class="compra-facil-inline">

                <h3>COMPRA FÁCIL Y SIN RIESGOS</h3>

                <p class="compra-segura">🔒 Elige tus productos favoritos y paga tranquilamente.</p>

                <div class="metodos-pago">

                    <img src="/Assets/img/logo/sinpemovil.jpg" alt="Simple Mobil" class="logo-pago">

                </div>

            </div>

        </div>

    </div>

</section>

<!-- Sobre Envios -->

<section class="sobre-envios">

    <div class="contenedor-envios">

        <h3>Sobre envios</h3>

        <p>¡Llevamos la belleza hasta tu puerta! Nos encargamos de que tus favoritos lleguen seguros con Correos de Costa Rica. El costo del envío es de solo <strong>₡3.500</strong> y tendrás tu pedido contigo en un plazo de <strong>2 a 4 días hábiles</strong>. ¡Así de fácil!</p>

    </div>

</section>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>
