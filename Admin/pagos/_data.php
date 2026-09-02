<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function pagos_data_file(): string
{
    return __DIR__ . '/pagos_data.json';
}

function pagos_metodos_validos(): array
{
    return ['SINPE Movil', 'Efectivo', 'Tarjeta de Debito', 'Tarjeta de Credito'];
}

function pagos_estados_validos(): array
{
    return ['Pendiente', 'En revision', 'Aprobado', 'Rechazado', 'Reembolsado'];
}

function pagos_seed(): array
{
    return [
        [
            'id' => 1,
            'numero_pago' => 'P001',
            'cliente' => 'Juan Perez',
            'telefono' => '8888-0001',
            'correo' => 'juan@email.com',
            'tipo' => 'Pedido',
            'numero' => 'PED-1001',
            'metodo' => 'SINPE Movil',
            'monto' => 18000,
            'fecha' => '2026-07-28 09:10:00',
            'estado' => 'En revision',
            'comprobante' => '',
            'observaciones' => 'Pago recibido por SINPE.',
        ],
        [
            'id' => 2,
            'numero_pago' => 'P002',
            'cliente' => 'Maria Lopez',
            'telefono' => '8888-1111',
            'correo' => 'maria@email.com',
            'tipo' => 'Pedido',
            'numero' => 'PED-1002',
            'metodo' => 'Efectivo',
            'monto' => 12000,
            'fecha' => '2026-07-28 11:20:00',
            'estado' => 'Pendiente',
            'comprobante' => '',
            'observaciones' => 'Pago contra entrega.',
        ],
        [
            'id' => 3,
            'numero_pago' => 'P003',
            'cliente' => 'Ana Vargas',
            'telefono' => '8888-3333',
            'correo' => 'ana@email.com',
            'tipo' => 'Pedido',
            'numero' => 'PED-1003',
            'metodo' => 'Tarjeta de Credito',
            'monto' => 32000,
            'fecha' => '2026-07-29 14:35:00',
            'estado' => 'Aprobado',
            'comprobante' => '',
            'observaciones' => 'Pago aprobado con datafono.',
        ],
    ];
}

function pagos_cargar_db(): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT pa.id_pago, pa.numero_pago, pa.metodo, pa.monto, pa.fecha_pago, pa.estado, pa.comprobante, pa.observaciones,
                   pe.numero_pedido, pe.total,
                   cl.nombre AS cliente, cl.telefono, cl.correo
            FROM pagos pa
            JOIN pedidos pe ON pa.id_pedido = pe.id_pedido
            JOIN clientes cl ON pe.id_cliente = cl.id_cliente
            ORDER BY pa.fecha_pago DESC";
    $stmt = $pdo->query($sql);
    $filas = $stmt ? $stmt->fetchAll() : [];

    $pagos = [];
    foreach ($filas as $fila) {
        $idPago = (int)($fila['id_pago'] ?? 0);
        $numeroPago = trim((string)($fila['numero_pago'] ?? ''));
        if ($numeroPago === '') {
            $numeroPago = 'P' . str_pad((string)$idPago, 3, '0', STR_PAD_LEFT);
        }

        $monto = $fila['monto'] !== null ? (float)$fila['monto'] : (float)($fila['total'] ?? 0);

        $pagos[] = [
            'id' => $idPago,
            'numero_pago' => $numeroPago,
            'cliente' => (string)($fila['cliente'] ?? ''),
            'telefono' => (string)($fila['telefono'] ?? ''),
            'correo' => (string)($fila['correo'] ?? ''),
            'tipo' => 'Pedido',
            'numero' => (string)($fila['numero_pedido'] ?? ''),
            'metodo' => (string)($fila['metodo'] ?? 'SINPE Movil'),
            'monto' => $monto,
            'fecha' => (string)($fila['fecha_pago'] ?? ''),
            'estado' => (string)($fila['estado'] ?? 'Pendiente'),
            'comprobante' => (string)($fila['comprobante'] ?? ''),
            'observaciones' => (string)($fila['observaciones'] ?? ''),
        ];
    }

    return $pagos;
}

function pagos_buscar_id_pedido_por_numero(PDO $pdo, string $numeroPedido): ?int
{
    $stmt = $pdo->prepare('SELECT id_pedido FROM pedidos WHERE numero_pedido = :numero LIMIT 1');
    $stmt->execute(['numero' => $numeroPedido]);
    $fila = $stmt->fetch();
    if ($fila && !empty($fila['id_pedido'])) {
        return (int)$fila['id_pedido'];
    }
    return null;
}

function pagos_guardar_db(array $pagos): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $idsNuevos = [];
    foreach ($pagos as $pago) {
        $id = (int)($pago['id'] ?? 0);
        if ($id > 0) {
            $idsNuevos[$id] = true;
        }
    }

    try {
        $pdo->beginTransaction();

        $mapIdPedido = [];
        $stmtIds = $pdo->query('SELECT id_pago, id_pedido FROM pagos');
        foreach (($stmtIds ? $stmtIds->fetchAll() : []) as $fila) {
            $id = (int)($fila['id_pago'] ?? 0);
            $idPedido = (int)($fila['id_pedido'] ?? 0);
            if ($id > 0) {
                $mapIdPedido[$id] = $idPedido;
            }
        }

        foreach ($mapIdPedido as $idExistente => $_idPedido) {
            if (!isset($idsNuevos[$idExistente])) {
                $del = $pdo->prepare('DELETE FROM pagos WHERE id_pago = :id');
                $del->execute(['id' => $idExistente]);
            }
        }

        $upsert = $pdo->prepare(
            'INSERT INTO pagos (id_pago, numero_pago, id_pedido, metodo, monto, comprobante, observaciones, fecha_pago, estado)
             VALUES (:id_pago, :numero_pago, :id_pedido, :metodo, :monto, :comprobante, :observaciones, :fecha_pago, :estado)
                         ON CONFLICT (id_pago) DO UPDATE SET
                             numero_pago = EXCLUDED.numero_pago,
                             id_pedido = EXCLUDED.id_pedido,
                             metodo = EXCLUDED.metodo,
                             monto = EXCLUDED.monto,
                             comprobante = EXCLUDED.comprobante,
                             observaciones = EXCLUDED.observaciones,
                             fecha_pago = EXCLUDED.fecha_pago,
                             estado = EXCLUDED.estado'
        );

        foreach ($pagos as $pago) {
            $idPago = (int)($pago['id'] ?? 0);
            if ($idPago <= 0) {
                continue;
            }

            $idPedido = $mapIdPedido[$idPago] ?? null;
            if ($idPedido === null) {
                $numeroPedido = trim((string)($pago['numero'] ?? ''));
                if ($numeroPedido !== '') {
                    $idPedido = pagos_buscar_id_pedido_por_numero($pdo, $numeroPedido);
                }
            }

            if (!$idPedido) {
                continue;
            }

            $numeroPago = trim((string)($pago['numero_pago'] ?? ''));
            if ($numeroPago === '') {
                $numeroPago = 'P' . str_pad((string)$idPago, 3, '0', STR_PAD_LEFT);
            }

            $fechaPago = trim((string)($pago['fecha'] ?? ''));
            $ts = strtotime($fechaPago);
            $fechaSql = $ts === false ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $ts);

            $upsert->execute([
                'id_pago' => $idPago,
                'numero_pago' => $numeroPago,
                'id_pedido' => (int)$idPedido,
                'metodo' => trim((string)($pago['metodo'] ?? 'SINPE Movil')),
                'monto' => (float)($pago['monto'] ?? 0),
                'comprobante' => trim((string)($pago['comprobante'] ?? '')),
                'observaciones' => trim((string)($pago['observaciones'] ?? '')),
                'fecha_pago' => $fechaSql,
                'estado' => trim((string)($pago['estado'] ?? 'Pendiente')),
            ]);
        }

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return false;
    }
}

function pagos_cargar(): array
{
    $pagosDb = pagos_cargar_db();
    if ($pagosDb) {
        file_put_contents(pagos_data_file(), json_encode($pagosDb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $pagosDb;
    }

    $archivo = pagos_data_file();

    if (!file_exists($archivo)) {
        $seed = pagos_seed();
        file_put_contents($archivo, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    $pagos = is_array($json) ? $json : [];

    if (!$pagos) {
        $pagos = pagos_seed();
        file_put_contents($archivo, json_encode($pagos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    usort($pagos, static function ($a, $b) {
        return strcmp((string)($b['fecha'] ?? ''), (string)($a['fecha'] ?? ''));
    });

    return $pagos;
}

function pagos_guardar(array $pagos): void
{
    pagos_guardar_db($pagos);
    file_put_contents(pagos_data_file(), json_encode(array_values($pagos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function pagos_buscar_por_id(array $pagos, int $id): ?array
{
    foreach ($pagos as $pago) {
        if ((int)($pago['id'] ?? 0) === $id) {
            return $pago;
        }
    }
    return null;
}

function pagos_buscar_indice_por_id(array $pagos, int $id): ?int
{
    foreach ($pagos as $i => $pago) {
        if ((int)($pago['id'] ?? 0) === $id) {
            return $i;
        }
    }
    return null;
}

function pagos_sync_pedido_estado(int $idPago, string $estado): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo || $idPago <= 0) {
        return false;
    }

    $estadoPago = trim($estado);
    if (!in_array($estadoPago, pagos_estados_validos(), true)) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT id_pedido FROM pagos WHERE id_pago = :id_pago LIMIT 1');
    $stmt->execute(['id_pago' => $idPago]);
    $fila = $stmt->fetch();
    if (!$fila || empty($fila['id_pedido'])) {
        return false;
    }

    $idPedido = (int)$fila['id_pedido'];
    $estadoPedido = 'Pendiente';

    if ($estadoPago === 'Aprobado') {
        $estadoPedido = 'Pagado';
    } elseif ($estadoPago === 'Rechazado') {
        $estadoPedido = 'Pendiente';
    }

    $stmtPedido = $pdo->prepare('UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido');
    $stmtPedido->execute([
        'estado' => $estadoPedido,
        'id_pedido' => $idPedido,
    ]);

    return true;
}
