<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function productos_data_file(): string
{
    return __DIR__ . '/productos_data.json';
}

function productos_categorias_validas(): array
{
    return ['Shampoo', 'Acondicionadores', 'Mascarillas', 'Tintes'];
}

function producto_estado_ui_desde_db(string $estadoDb): string
{
    return $estadoDb === 'Descontinuado' ? 'Inactivo' : 'Activo';
}

function producto_estado_db_desde_ui(string $estadoUi, int $stock): string
{
    if ($estadoUi === 'Inactivo') {
        return 'Descontinuado';
    }

    if ($stock <= 0) {
        return 'Agotado';
    }

    return 'Disponible';
}

function producto_resolver_categoria_id(PDO $pdo, string $categoria): int
{
    $stmt = $pdo->prepare('SELECT id_categoria FROM categorias WHERE nombre = :nombre LIMIT 1');
    $stmt->execute(['nombre' => $categoria]);
    $fila = $stmt->fetch();

    if ($fila && !empty($fila['id_categoria'])) {
        return (int)$fila['id_categoria'];
    }

    $ins = $pdo->prepare('INSERT INTO categorias (nombre) VALUES (:nombre)');
    $ins->execute(['nombre' => $categoria]);

    return (int)$pdo->lastInsertId();
}

function productos_cargar_desde_db(): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio, p.stock, p.destacado, p.estado, p.imagen, c.nombre AS categoria
            FROM productos p
            LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
            ORDER BY p.id_producto ASC";
    $stmt = $pdo->query($sql);
    $filas = $stmt ? $stmt->fetchAll() : [];

    $productos = [];
    foreach ($filas as $fila) {
        $productos[] = [
            'id' => (int)($fila['id_producto'] ?? 0),
            'nombre' => (string)($fila['nombre'] ?? ''),
            'descripcion' => (string)($fila['descripcion'] ?? ''),
            'categoria' => (string)($fila['categoria'] ?? ''),
            'precio' => (float)($fila['precio'] ?? 0),
            'stock' => (int)($fila['stock'] ?? 0),
            'oferta' => !empty($fila['destacado']),
            'estado' => producto_estado_ui_desde_db((string)($fila['estado'] ?? 'Disponible')),
            'imagen' => (string)($fila['imagen'] ?? ''),
            'editable' => true,
        ];
    }

    return $productos;
}

function productos_cargar(): array
{
    $productos = productos_cargar_desde_db();
    if ($productos) {
        file_put_contents(productos_data_file(), json_encode(array_values($productos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $productos;
    }

    $archivo = productos_data_file();
    if (!file_exists($archivo)) {
        return [];
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    if (!is_array($json)) {
        return [];
    }

    foreach ($json as $i => $producto) {
        $json[$i]['editable'] = true;
    }

    return array_values($json);
}

function productos_buscar_por_id(int $id): ?array
{
    foreach (productos_cargar() as $producto) {
        if ((int)($producto['id'] ?? 0) === $id) {
            return $producto;
        }
    }

    return null;
}

function productos_insertar(array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $categoria = trim((string)($payload['categoria'] ?? ''));
    $nombre = trim((string)($payload['nombre'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $precio = (float)($payload['precio'] ?? 0);
    $stock = (int)($payload['stock'] ?? 0);
    $oferta = !empty($payload['oferta']);
    $estadoUi = trim((string)($payload['estado'] ?? 'Activo'));
    $imagen = trim((string)($payload['imagen'] ?? ''));

    if ($categoria === '' || $nombre === '') {
        return false;
    }

    try {
        $idCategoria = producto_resolver_categoria_id($pdo, $categoria);
        $estadoDb = producto_estado_db_desde_ui($estadoUi, $stock);

        $stmt = $pdo->prepare(
            'INSERT INTO productos (id_categoria, codigo, nombre, descripcion, precio, precio_oferta, destacado, stock, imagen, estado)
             VALUES (:id_categoria, :codigo, :nombre, :descripcion, :precio, :precio_oferta, :destacado, :stock, :imagen, :estado)'
        );

        $codigo = 'PRD-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt->execute([
            'id_categoria' => $idCategoria,
            'codigo' => $codigo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'precio_oferta' => null,
            'destacado' => $oferta,
            'stock' => $stock,
            'imagen' => $imagen,
            'estado' => $estadoDb,
        ]);

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function productos_actualizar(int $id, array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $categoria = trim((string)($payload['categoria'] ?? ''));
    $nombre = trim((string)($payload['nombre'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $precio = (float)($payload['precio'] ?? 0);
    $stock = (int)($payload['stock'] ?? 0);
    $oferta = !empty($payload['oferta']);
    $estadoUi = trim((string)($payload['estado'] ?? 'Activo'));
    $imagen = trim((string)($payload['imagen'] ?? ''));

    if ($id <= 0 || $categoria === '' || $nombre === '') {
        return false;
    }

    try {
        $idCategoria = producto_resolver_categoria_id($pdo, $categoria);
        $estadoDb = producto_estado_db_desde_ui($estadoUi, $stock);

        $stmt = $pdo->prepare(
            'UPDATE productos
             SET id_categoria = :id_categoria,
                 nombre = :nombre,
                 descripcion = :descripcion,
                 precio = :precio,
                 destacado = :destacado,
                 stock = :stock,
                 imagen = :imagen,
                 estado = :estado
             WHERE id_producto = :id'
        );

        $stmt->execute([
            'id_categoria' => $idCategoria,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'destacado' => $oferta,
            'stock' => $stock,
            'imagen' => $imagen,
            'estado' => $estadoDb,
            'id' => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT 1 FROM productos WHERE id_producto = :id LIMIT 1');
            $check->execute(['id' => $id]);

            if (!$check->fetchColumn()) {
                return false;
            }
        }

        $productosActualizados = productos_cargar_desde_db();
        if ($productosActualizados) {
            file_put_contents(
                productos_data_file(),
                json_encode(array_values($productosActualizados), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function productos_eliminar(int $id): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $id <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM productos WHERE id_producto = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$productosData = productos_cargar();
