<?php

declare(strict_types=1);

require_once __DIR__ . '/../Includes/conexion.php';

$archivoProductos = __DIR__ . '/../Admin/productos/productos_data.json';
$productos = json_decode((string)file_get_contents($archivoProductos), true, 512, JSON_THROW_ON_ERROR);
$pdo = obtenerConexionSalon();

if (!$pdo) {
    fwrite(STDERR, "No se pudo conectar a Supabase. Verifica DATABASE_URL y la contraseña.\n");
    exit(1);
}

$pdo->beginTransaction();

try {
    $categorias = [];
    $stmtCategoria = $pdo->prepare(
        "INSERT INTO categorias (nombre, tipo, estado)
         VALUES (:nombre, 'Producto', 'Activo')
         ON CONFLICT (nombre) DO UPDATE SET nombre = EXCLUDED.nombre
         RETURNING id_categoria"
    );

    foreach ($productos as $producto) {
        $nombreCategoria = trim((string)($producto['categoria'] ?? ''));
        if ($nombreCategoria === '') {
            throw new RuntimeException('Hay un producto sin categoria.');
        }

        if (!isset($categorias[$nombreCategoria])) {
            $stmtCategoria->execute(['nombre' => $nombreCategoria]);
            $categorias[$nombreCategoria] = (int)$stmtCategoria->fetchColumn();
        }
    }

    $stmtProducto = $pdo->prepare(
        "INSERT INTO productos
            (id_producto, id_categoria, nombre, descripcion, precio, stock, imagen, estado)
         VALUES
            (:id, :id_categoria, :nombre, :descripcion, :precio, :stock, :imagen, :estado)
         ON CONFLICT (id_producto) DO UPDATE SET
            id_categoria = EXCLUDED.id_categoria,
            nombre = EXCLUDED.nombre,
            descripcion = EXCLUDED.descripcion,
            precio = EXCLUDED.precio,
            stock = EXCLUDED.stock,
            imagen = EXCLUDED.imagen,
            estado = EXCLUDED.estado"
    );

    foreach ($productos as $producto) {
        $nombreCategoria = trim((string)$producto['categoria']);
        $estado = (string)($producto['estado'] ?? 'Disponible');
        $estado = $estado === 'Activo' ? 'Disponible' : $estado;

        $stmtProducto->execute([
            'id' => (int)$producto['id'],
            'id_categoria' => $categorias[$nombreCategoria],
            'nombre' => (string)$producto['nombre'],
            'descripcion' => (string)($producto['descripcion'] ?? ''),
            'precio' => (float)($producto['precio'] ?? 0),
            'stock' => (int)($producto['stock'] ?? 0),
            'imagen' => (string)($producto['imagen'] ?? ''),
            'estado' => $estado,
        ]);
    }

    $pdo->exec(
        "SELECT setval(
            pg_get_serial_sequence('productos', 'id_producto'),
            COALESCE((SELECT MAX(id_producto) FROM productos), 1),
            true
        )"
    );

    $pdo->commit();
    echo 'Productos migrados correctamente: ' . count($productos) . PHP_EOL;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, 'No se migraron los productos: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
