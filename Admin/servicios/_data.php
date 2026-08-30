<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function servicios_data_file(): string
{
    return __DIR__ . '/servicios_data.json';
}

function servicios_categorias_validas(): array
{
    return ['Cortes', 'Coloracion', 'Tratamientos', 'Barberia', 'Manicure', 'Pedicure', 'Peinados', 'Maquillaje', 'Faciales', 'Depilacion'];
}

function servicio_estado_ui_desde_db(string $estadoDb): string
{
    return $estadoDb === 'No Disponible' ? 'Inactivo' : 'Activo';
}

function servicio_estado_db_desde_ui(string $estadoUi): string
{
    return $estadoUi === 'Inactivo' ? 'No Disponible' : 'Disponible';
}

function servicios_cargar_desde_db(): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    $stmt = $pdo->query('SELECT id_servicio, nombre, categoria, descripcion, duracion, precio, imagen, estado FROM servicios ORDER BY id_servicio ASC');
    $filas = $stmt ? $stmt->fetchAll() : [];

    $servicios = [];
    foreach ($filas as $fila) {
        $servicios[] = [
            'id' => (int)($fila['id_servicio'] ?? 0),
            'nombre' => (string)($fila['nombre'] ?? ''),
            'categoria' => (string)($fila['categoria'] ?? ''),
            'descripcion' => (string)($fila['descripcion'] ?? ''),
            'duracion' => (int)($fila['duracion'] ?? 0),
            'precio' => (float)($fila['precio'] ?? 0),
            'imagen' => (string)($fila['imagen'] ?? ''),
            'estado' => servicio_estado_ui_desde_db((string)($fila['estado'] ?? 'Disponible')),
        ];
    }

    return $servicios;
}

function servicios_cargar(): array
{
    $servicios = servicios_cargar_desde_db();
    if ($servicios) {
        file_put_contents(servicios_data_file(), json_encode(array_values($servicios), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $servicios;
    }

    $archivo = servicios_data_file();
    if (!file_exists($archivo)) {
        return [];
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    if (!is_array($json)) {
        return [];
    }

    return array_values($json);
}

function servicios_buscar_por_id(int $id): ?array
{
    foreach (servicios_cargar() as $servicio) {
        if ((int)($servicio['id'] ?? 0) === $id) {
            return $servicio;
        }
    }

    return null;
}

function servicios_insertar(array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $nombre = trim((string)($payload['nombre'] ?? ''));
    $categoria = trim((string)($payload['categoria'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $duracion = (int)($payload['duracion'] ?? 0);
    $precio = (float)($payload['precio'] ?? 0);
    $imagen = trim((string)($payload['imagen'] ?? ''));
    $estadoUi = trim((string)($payload['estado'] ?? 'Activo'));

    if ($nombre === '' || $categoria === '' || $duracion <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO servicios (nombre, categoria, descripcion, precio, duracion, imagen, estado) VALUES (:nombre, :categoria, :descripcion, :precio, :duracion, :imagen, :estado)');
        $stmt->execute([
            'nombre' => $nombre,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'duracion' => $duracion,
            'imagen' => $imagen,
            'estado' => servicio_estado_db_desde_ui($estadoUi),
        ]);
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function servicios_actualizar(int $id, array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $id <= 0) {
        return false;
    }

    $nombre = trim((string)($payload['nombre'] ?? ''));
    $categoria = trim((string)($payload['categoria'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $duracion = (int)($payload['duracion'] ?? 0);
    $precio = (float)($payload['precio'] ?? 0);
    $imagen = trim((string)($payload['imagen'] ?? ''));
    $estadoUi = trim((string)($payload['estado'] ?? 'Activo'));

    if ($nombre === '' || $categoria === '' || $duracion <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('UPDATE servicios SET nombre = :nombre, categoria = :categoria, descripcion = :descripcion, precio = :precio, duracion = :duracion, imagen = :imagen, estado = :estado WHERE id_servicio = :id');
        $stmt->execute([
            'nombre' => $nombre,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'duracion' => $duracion,
            'imagen' => $imagen,
            'estado' => servicio_estado_db_desde_ui($estadoUi),
            'id' => $id,
        ]);

        return $stmt->rowCount() >= 0;
    } catch (Throwable $e) {
        return false;
    }
}

function servicios_eliminar(int $id): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $id <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM servicios WHERE id_servicio = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$serviciosData = servicios_cargar();
