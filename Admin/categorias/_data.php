<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function categorias_data_file(): string
{
    return __DIR__ . '/categorias_data.json';
}

function categorias_tipos_validos(): array
{
    return ['Producto', 'Servicio', 'Ambos'];
}

function categorias_estados_validos(): array
{
    return ['Activo', 'Inactivo'];
}

function categorias_ensure_columns(PDO $pdo): void
{
    $esPostgreSQL = (bool)preg_match('/^pgsql:/i', (string)$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS));
    $esPostgreSQL = $esPostgreSQL || strtolower((string)getenv('DB_DRIVER')) === 'pgsql' || getenv('DATABASE_URL') !== false;
    $esquema = $esPostgreSQL ? "table_schema = 'public'" : 'table_schema = DATABASE()';
    $stmtTipo = $pdo->query("SELECT COUNT(*) AS total FROM information_schema.columns WHERE $esquema AND table_name = 'categorias' AND column_name = 'tipo'");
    $tieneTipo = (int)(($stmtTipo ? $stmtTipo->fetch() : ['total' => 0])['total'] ?? 0) > 0;

    if (!$tieneTipo) {
        $pdo->exec($esPostgreSQL
            ? "ALTER TABLE categorias ADD COLUMN tipo VARCHAR(20) DEFAULT 'Producto'"
            : "ALTER TABLE categorias ADD COLUMN tipo ENUM('Producto','Servicio','Ambos') DEFAULT 'Producto' AFTER descripcion");
    }

    $stmtEstado = $pdo->query("SELECT COUNT(*) AS total FROM information_schema.columns WHERE $esquema AND table_name = 'categorias' AND column_name = 'estado'");
    $tieneEstado = (int)(($stmtEstado ? $stmtEstado->fetch() : ['total' => 0])['total'] ?? 0) > 0;

    if (!$tieneEstado) {
        $pdo->exec($esPostgreSQL
            ? "ALTER TABLE categorias ADD COLUMN estado VARCHAR(20) DEFAULT 'Activo'"
            : "ALTER TABLE categorias ADD COLUMN estado ENUM('Activo','Inactivo') DEFAULT 'Activo' AFTER tipo");
    }
}

function categorias_cargar_desde_db(): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    try {
        categorias_ensure_columns($pdo);

        $stmt = $pdo->query('SELECT id_categoria, nombre, descripcion, tipo, estado FROM categorias ORDER BY id_categoria ASC');
        $filas = $stmt ? $stmt->fetchAll() : [];

        $categorias = [];
        foreach ($filas as $fila) {
            $categorias[] = [
                'id' => (int)($fila['id_categoria'] ?? 0),
                'nombre' => (string)($fila['nombre'] ?? ''),
                'descripcion' => (string)($fila['descripcion'] ?? ''),
                'tipo' => (string)($fila['tipo'] ?? 'Producto'),
                'estado' => (string)($fila['estado'] ?? 'Activo'),
            ];
        }

        return $categorias;
    } catch (Throwable $e) {
        return [];
    }
}

function categorias_cargar(): array
{
    $categorias = categorias_cargar_desde_db();
    if ($categorias) {
        file_put_contents(categorias_data_file(), json_encode(array_values($categorias), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $categorias;
    }

    $archivo = categorias_data_file();
    if (!file_exists($archivo)) {
        return [];
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    if (!is_array($json)) {
        return [];
    }

    return array_values($json);
}

function categorias_buscar_por_id(int $id): ?array
{
    foreach (categorias_cargar() as $categoria) {
        if ((int)($categoria['id'] ?? 0) === $id) {
            return $categoria;
        }
    }

    return null;
}

function categorias_existe_nombre(string $nombre, int $ignorarId = 0): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        foreach (categorias_cargar() as $categoria) {
            if ((int)($categoria['id'] ?? 0) === $ignorarId) {
                continue;
            }
            if (strcasecmp((string)($categoria['nombre'] ?? ''), $nombre) === 0) {
                return true;
            }
        }
        return false;
    }

    try {
        categorias_ensure_columns($pdo);

        $sql = 'SELECT id_categoria FROM categorias WHERE LOWER(nombre) = LOWER(:nombre)';
        $params = ['nombre' => $nombre];
        if ($ignorarId > 0) {
            $sql .= ' AND id_categoria <> :id';
            $params['id'] = $ignorarId;
        }
        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    } catch (Throwable $e) {
        return false;
    }
}

function categorias_insertar(array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $nombre = trim((string)($payload['nombre'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $tipo = trim((string)($payload['tipo'] ?? 'Producto'));
    $estado = trim((string)($payload['estado'] ?? 'Activo'));

    if ($nombre === '' || $descripcion === '') {
        return false;
    }

    try {
        categorias_ensure_columns($pdo);

        $stmt = $pdo->prepare('INSERT INTO categorias (nombre, descripcion, tipo, estado) VALUES (:nombre, :descripcion, :tipo, :estado)');
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'tipo' => $tipo,
            'estado' => $estado,
        ]);

        return true;
    } catch (Throwable $e) {
        return false;
    }
}

function categorias_actualizar(int $id, array $payload): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $id <= 0) {
        return false;
    }

    $nombre = trim((string)($payload['nombre'] ?? ''));
    $descripcion = trim((string)($payload['descripcion'] ?? ''));
    $tipo = trim((string)($payload['tipo'] ?? 'Producto'));
    $estado = trim((string)($payload['estado'] ?? 'Activo'));

    if ($nombre === '' || $descripcion === '') {
        return false;
    }

    try {
        categorias_ensure_columns($pdo);

        $stmt = $pdo->prepare('UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, tipo = :tipo, estado = :estado WHERE id_categoria = :id');
        $stmt->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'tipo' => $tipo,
            'estado' => $estado,
            'id' => $id,
        ]);

        return $stmt->rowCount() >= 0;
    } catch (Throwable $e) {
        return false;
    }
}

function categorias_eliminar(int $id): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $id <= 0) {
        return false;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM categorias WHERE id_categoria = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

$categoriasData = categorias_cargar();
