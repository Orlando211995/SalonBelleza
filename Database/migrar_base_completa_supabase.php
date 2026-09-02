<?php

declare(strict_types=1);

function crearConexion(string $dsn, string $usuario, string $password): PDO
{
    return new PDO($dsn, $usuario, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function identificar(string $nombre): string
{
    return '"' . str_replace('"', '""', $nombre) . '"';
}

function identificarMysql(string $nombre): string
{
    return '`' . str_replace('`', '``', $nombre) . '`';
}

function normalizarValor(string $tabla, string $columna, mixed $valor): mixed
{
    if ($valor === null) {
        return null;
    }

    if ($tabla === 'categorias' && $columna === 'tipo' && $valor === '') {
        return 'Producto';
    }

    if ($tabla === 'categorias' && $columna === 'estado') {
        return $valor === 'Inactivo' ? 'Inactivo' : 'Activo';
    }

    if (($tabla === 'productos' || $tabla === 'servicios') && $columna === 'estado') {
        if ($valor === 'Activo') {
            return 'Disponible';
        }
        if ($valor === 'Inactivo') {
            return $tabla === 'servicios' ? 'No Disponible' : 'Descontinuado';
        }
    }

    return $valor;
}

$databaseUrl = trim((string)(getenv('DATABASE_URL') ?: ''));
if ($databaseUrl === '') {
    fwrite(STDERR, "Falta configurar DATABASE_URL para Supabase.\n");
    exit(1);
}

$url = parse_url($databaseUrl);
$targetHost = (string)($url['host'] ?? '');
$targetPort = (int)($url['port'] ?? 5432);
$targetDatabase = ltrim((string)($url['path'] ?? 'postgres'), '/');
$targetUser = rawurldecode((string)($url['user'] ?? 'postgres'));
$targetPassword = rawurldecode((string)($url['pass'] ?? ''));

$mysqlHost = getenv('MYSQL_HOST') ?: '127.0.0.1';
$mysqlPort = (int)(getenv('MYSQL_PORT') ?: 3306);
$mysqlDatabase = getenv('MYSQL_DATABASE') ?: 'salon_belleza';
$mysqlUser = getenv('MYSQL_USER') ?: 'root';
$mysqlPassword = getenv('MYSQL_PASSWORD') ?: '';

$mysql = crearConexion(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $mysqlHost, $mysqlPort, $mysqlDatabase),
    $mysqlUser,
    $mysqlPassword
);
$pgsql = crearConexion(
    sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=require', $targetHost, $targetPort, $targetDatabase),
    $targetUser,
    $targetPassword
);

$tablas = [
    'administrador',
    'categorias',
    'clientes',
    'servicios',
    'horarios',
    'productos',
    'ofertas',
    'citas',
    'pedidos',
    'detalle_pedido',
    'pagos',
    'contacto_mensajes',
];

$ids = [
    'administrador' => 'id_admin',
    'categorias' => 'id_categoria',
    'clientes' => 'id_cliente',
    'servicios' => 'id_servicio',
    'horarios' => 'id_horario',
    'productos' => 'id_producto',
    'ofertas' => 'id_oferta',
    'citas' => 'id_cita',
    'pedidos' => 'id_pedido',
    'detalle_pedido' => 'id_detalle',
    'pagos' => 'id_pago',
    'contacto_mensajes' => 'id_mensaje',
];

$pgsql->beginTransaction();

try {
    foreach ($tablas as $tabla) {
        $stmtColumnas = $pgsql->prepare(
            "SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = 'public' AND table_name = :tabla
             ORDER BY ordinal_position"
        );
        $stmtColumnas->execute(['tabla' => $tabla]);
        $columnasDestino = array_column($stmtColumnas->fetchAll(), 'column_name');

        $filas = $mysql->query('SELECT * FROM ' . identificarMysql($tabla))->fetchAll();
        if (!$filas) {
            echo $tabla . ': 0 registros' . PHP_EOL;
            continue;
        }

        $columnas = array_values(array_filter(array_keys($filas[0]), static function (string $columna) use ($columnasDestino): bool {
            return in_array($columna, $columnasDestino, true);
        }));

        $columnasSql = implode(', ', array_map('identificar', $columnas));
        $columnasActualizacion = array_values(array_filter($columnas, static function (string $columna) use ($ids, $tabla): bool {
            return $columna !== $ids[$tabla];
        }));

        foreach ($filas as $fila) {
            $parametros = [];
            $marcadores = [];
            foreach ($columnas as $indice => $columna) {
                $parametro = ':v' . $indice;
                $marcadores[] = $parametro;
                $parametros[$parametro] = normalizarValor($tabla, $columna, $fila[$columna]);
            }

            $sql = 'INSERT INTO ' . identificar($tabla) . ' (' . $columnasSql . ') VALUES (' . implode(', ', $marcadores) . ')';
            if ($columnasActualizacion) {
                $actualizaciones = [];
                foreach ($columnasActualizacion as $columna) {
                    $actualizaciones[] = identificar($columna) . ' = EXCLUDED.' . identificar($columna);
                }
                $sql .= ' ON CONFLICT (' . identificar($ids[$tabla]) . ') DO UPDATE SET ' . implode(', ', $actualizaciones);
            } else {
                $sql .= ' ON CONFLICT (' . identificar($ids[$tabla]) . ') DO NOTHING';
            }

            $stmtInsertar = $pgsql->prepare($sql);
            $stmtInsertar->execute($parametros);
        }

        $id = $ids[$tabla];
        $secuencia = $pgsql->query("SELECT pg_get_serial_sequence('public." . $tabla . "', '" . $id . "')")->fetchColumn();
        if ($secuencia) {
            $stmtSecuencia = $pgsql->prepare(
                "SELECT setval(:secuencia, COALESCE((SELECT MAX(\"$id\") FROM \"$tabla\"), 1), true)"
            );
            $stmtSecuencia->execute(['secuencia' => $secuencia]);
        }

        echo $tabla . ': ' . count($filas) . ' registros' . PHP_EOL;
    }

    $pgsql->commit();
    echo 'Migracion completa finalizada correctamente.' . PHP_EOL;
} catch (Throwable $e) {
    if ($pgsql->inTransaction()) {
        $pgsql->rollBack();
    }

    fwrite(STDERR, 'La migracion fue cancelada: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
