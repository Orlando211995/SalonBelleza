<?php

require_once __DIR__ . '/../Admin/productos/_data.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

include(__DIR__ . '/../Includes/header.php');
include(__DIR__ . '/../Includes/menu.php');

$productosCatalogo = productos_cargar();
if (!$productosCatalogo) {
    $productosCatalogo = [
        [
            'id' => 1,
            'nombre' => 'ARGAN OIL SHAMPOO',
            'precio' => 8190,
            'categoria' => 'Shampoo',
            'imagen' => '/Assets/img/productos/ArganShampoo.jpg',
        ],
        [
            'id' => 2,
            'nombre' => 'ARGAN OIL CONDITIONER',
            'precio' => 21420,
            'categoria' => 'Acondicionadores',
            'imagen' => '/Assets/img/productos/ArganConditioner.jpg',
        ],
        [
            'id' => 3,
            'nombre' => 'ARGAN OIL MASK',
            'precio' => 16170,
            'categoria' => 'Mascarillas',
            'imagen' => '/Assets/img/productos/ArganMask.jpg',
        ],
        [
            'id' => 4,
            'nombre' => '# KRAY SEMI-PERM LIGHT BLUE',
            'precio' => 8050,
            'categoria' => 'Tintes',
            'imagen' => '/Assets/img/productos/TinteLIGHT%20BLUE.jpg',
        ],
    ];
}

$categorias = [
    'shampoos' => 'Shampoo',
    'acondicionadores' => 'Acondicionadores',
    'mascarillas' => 'Mascarillas',
    'tintes' => 'Tintes',
];

$categoriasOrdenadas = ['shampoos', 'acondicionadores', 'mascarillas', 'tintes'];

?>

<!-- Banner de Productos -->

<section class="hero" style="background: url('/Assets/img/banner/bannerproductos.jpg') center/cover no-repeat;">

</section>

<!-- Título de Productos -->

<section class="servicio-titulo">

    <div class="contenedor-servicio-titulo">

        <h2>Productos</h2>

    </div>

</section>

<!-- Productos -->

<section class="productos">

    <div class="productos-wrapper">

        <aside class="productos-sidebar">

            <h3 class="categoria-title">CATEGORÍAS</h3>

            <ul class="categorias-list">
                <?php foreach ($categoriasOrdenadas as $key => $categoriaKey): ?>
                    <li>
                        <a href="#<?php echo htmlspecialchars($categoriaKey); ?>" class="categoria-link <?php echo $key === 0 ? 'active' : ''; ?>" data-categoria="<?php echo htmlspecialchars($categoriaKey); ?>"><?php echo htmlspecialchars($categorias[$categoriaKey]); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>

        </aside>

        <div class="productos-grid">
            <?php foreach ($productosCatalogo as $producto): ?>
                <?php
                    $categoriaActual = (string)($producto['categoria'] ?? 'Shampoo');
                    $categoriaKey = match ($categoriaActual) {
                        'Acondicionadores' => 'acondicionadores',
                        'Mascarillas' => 'mascarillas',
                        'Tintes' => 'tintes',
                        default => 'shampoos',
                    };
                    $slugProducto = 'producto-' . (int)($producto['id'] ?? 0);
                ?>
                <div class="card-producto" data-categoria="<?php echo htmlspecialchars($categoriaKey); ?>" style="<?php echo $categoriaKey === 'shampoos' ? '' : 'display: none;'; ?>">
                    <a href="producto.php?producto=<?php echo urlencode($slugProducto); ?>" class="card-producto-link">
                        <div class="card-producto-img-wrapper">
                            <img src="<?php echo htmlspecialchars((string)($producto['imagen'] ?? '/Assets/img/productos/default.jpg')); ?>" alt="<?php echo htmlspecialchars((string)($producto['nombre'] ?? 'Producto')); ?>">
                        </div>
                    </a>
                    <div class="card-producto-content">
                        <h3><?php echo htmlspecialchars((string)($producto['nombre'] ?? 'Producto')); ?></h3>
                        <p>₡<?php echo number_format((float)($producto['precio'] ?? 0), 0, ',', '.'); ?></p>
                        <button class="btn-agregar-carrito-producto" data-producto-id="<?php echo htmlspecialchars($slugProducto); ?>">Agregar al carrito</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</section>

<script>

document.querySelectorAll('.categoria-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const categoria = this.dataset.categoria;
        document.querySelectorAll('.categoria-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.card-producto').forEach(producto => {
            producto.style.display = producto.dataset.categoria === categoria ? 'block' : 'none';
        });
    });
});
</script>

<?php

include(__DIR__ . '/../Includes/footer.php');

?>
