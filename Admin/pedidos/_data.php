<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function pedidos_data_file(): string
{
    return __DIR__ . '/pedidos_data.json';
}

function pedidos_estados_validos(): array
{
    return ['Pendiente', 'Pagado', 'Preparando', 'Entregado', 'Cancelado'];
}

function pedidos_metodos_pago_validos(): array
{
    return ['SINPE'];
}

function pedidos_seed(): array
{
    return [
        [
            'id' => 1,
            'numero_pedido' => 'PED-1001',
            'cliente' => 'Maria Lopez',
            'telefono' => '8888-1111',
            'correo' => 'maria@email.com',
            'direccion' => 'San Jose, Desamparados, Centro',
            'metodo_pago' => 'SINPE',
            'estado' => 'Pagado',
            'total' => 28500,
            'observaciones' => 'Entregar en horario de tarde.',
            'fecha' => '2026-07-25 10:15:00',
            'items' => [
                ['producto' => 'Shampoo Hidratante', 'cantidad' => 1, 'precio' => 9500],
                ['producto' => 'Acondicionador Nutritivo', 'cantidad' => 1, 'precio' => 9000],
                ['producto' => 'Mascarilla Reparadora', 'cantidad' => 1, 'precio' => 10000],
            ],
        ],
        [
            'id' => 2,
            'numero_pedido' => 'PED-1002',
            'cliente' => 'Jose Ramirez',
            'telefono' => '8888-2222',
            'correo' => 'jose@email.com',
            'direccion' => 'Cartago, El Tejar',
            'metodo_pago' => 'SINPE',
            'estado' => 'Preparando',
            'total' => 18000,
            'observaciones' => '',
            'fecha' => '2026-07-26 14:40:00',
            'items' => [
                ['producto' => 'Cera para peinar', 'cantidad' => 2, 'precio' => 9000],
            ],
        ],
        [
            'id' => 3,
            'numero_pedido' => 'PED-1003',
            'cliente' => 'Ana Vargas',
            'telefono' => '8888-3333',
            'correo' => 'ana@email.com',
            'direccion' => 'Heredia, San Francisco',
            'metodo_pago' => 'SINPE',
            'estado' => 'Pendiente',
            'total' => 12000,
            'observaciones' => 'Llamar antes de entregar.',
            'fecha' => '2026-07-27 09:20:00',
            'items' => [
                ['producto' => 'Tinte Profesional', 'cantidad' => 1, 'precio' => 12000],
            ],
        ],
    ];
}

function pedido_hora_sql(string $fechaTexto): string
{
    $ts = strtotime($fechaTexto);
    if ($ts === false) {
        return date('Y-m-d H:i:s');
    }
    return date('Y-m-d H:i:s', $ts);
}

function buscar_o_crear_cliente_pedido(PDO $pdo, string $nombre, string $telefono, string $correo, string $direccion): int
{
    $stmt = $pdo->prepare('SELECT id_cliente FROM clientes WHERE telefono = :telefono AND correo = :correo LIMIT 1');
    $stmt->execute(['telefono' => $telefono, 'correo' => $correo]);
    $fila = $stmt->fetch();

    if ($fila && !empty($fila['id_cliente'])) {
        $id = (int)$fila['id_cliente'];
        $upd = $pdo->prepare('UPDATE clientes SET nombre = :nombre, direccion = :direccion WHERE id_cliente = :id');
        $upd->execute(['nombre' => $nombre, 'direccion' => $direccion, 'id' => $id]);
        return $id;
    }

    $ins = $pdo->prepare('INSERT INTO clientes (nombre, telefono, correo, direccion) VALUES (:nombre, :telefono, :correo, :direccion)');
    $ins->execute([
        'nombre' => $nombre,
        'telefono' => $telefono,
        'correo' => $correo,
        'direccion' => $direccion,
    ]);
    return (int)$pdo->lastInsertId();
}

function buscar_producto_id_por_nombre(PDO $pdo, string $nombre): ?int
{
    $stmt = $pdo->prepare('SELECT id_producto FROM productos WHERE nombre = :nombre LIMIT 1');
    $stmt->execute(['nombre' => $nombre]);
    $fila = $stmt->fetch();
    if ($fila && !empty($fila['id_producto'])) {
        return (int)$fila['id_producto'];
    }
    return null;
}

function pedidos_cargar_db(): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT p.id_pedido, p.numero_pedido, p.direccion, p.metodo_pago, p.tipo_entrega, p.total,
                   p.costo_envio, p.observaciones, p.comprobante, p.estado, p.fecha,
                   c.nombre AS cliente, c.telefono, c.correo
            FROM pedidos p
            JOIN clientes c ON p.id_cliente = c.id_cliente
            ORDER BY p.fecha DESC";
    $stmt = $pdo->query($sql);
    $pedidosDb = $stmt ? $stmt->fetchAll() : [];

    $stmtItems = $pdo->query("SELECT dp.id_pedido, dp.cantidad, dp.precio_unitario,
                                     COALESCE(pr.nombre, CONCAT('Producto #', dp.id_producto)) AS producto
                              FROM detalle_pedido dp
                              LEFT JOIN productos pr ON pr.id_producto = dp.id_producto
                              ORDER BY dp.id_detalle ASC");
    $itemsFilas = $stmtItems ? $stmtItems->fetchAll() : [];

    $itemsPorPedido = [];
    foreach ($itemsFilas as $fila) {
        $idPedido = (int)($fila['id_pedido'] ?? 0);
        if ($idPedido <= 0) {
            continue;
        }

        $itemsPorPedido[$idPedido][] = [
            'producto' => (string)($fila['producto'] ?? 'Producto'),
            'cantidad' => (int)($fila['cantidad'] ?? 0),
            'precio' => (float)($fila['precio_unitario'] ?? 0),
        ];
    }

    $pedidos = [];
    foreach ($pedidosDb as $fila) {
        $idPedido = (int)($fila['id_pedido'] ?? 0);
        $pedidos[] = [
            'id' => $idPedido,
            'numero_pedido' => (string)($fila['numero_pedido'] ?? ''),
            'cliente' => (string)($fila['cliente'] ?? ''),
            'telefono' => (string)($fila['telefono'] ?? ''),
            'correo' => (string)($fila['correo'] ?? ''),
            'direccion' => (string)($fila['direccion'] ?? ''),
            'metodo_pago' => (string)($fila['metodo_pago'] ?? 'SINPE'),
            'estado' => (string)($fila['estado'] ?? 'Pendiente'),
            'tipo_entrega' => (string)($fila['tipo_entrega'] ?? 'envio'),
            'costo_envio' => (float)($fila['costo_envio'] ?? 0),
            'total' => (float)($fila['total'] ?? 0),
            'observaciones' => (string)($fila['observaciones'] ?? ''),
            'fecha' => (string)($fila['fecha'] ?? ''),
            'items' => $itemsPorPedido[$idPedido] ?? [],
            'comprobante' => (string)($fila['comprobante'] ?? ''),
        ];
    }

    return $pedidos;
}

function pedidos_guardar_db(array $pedidos): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $idsNuevos = [];
    foreach ($pedidos as $pedido) {
        $id = (int)($pedido['id'] ?? 0);
        if ($id > 0) {
            $idsNuevos[$id] = true;
        }
    }

    try {
        $pdo->beginTransaction();

        $idsExistentes = [];
        $stmtIds = $pdo->query('SELECT id_pedido FROM pedidos');
        foreach (($stmtIds ? $stmtIds->fetchAll() : []) as $filaId) {
            $id = (int)($filaId['id_pedido'] ?? 0);
            if ($id > 0) {
                $idsExistentes[$id] = true;
            }
        }

        foreach ($idsExistentes as $idExistente => $_) {
            if (!isset($idsNuevos[$idExistente])) {
                $del = $pdo->prepare('DELETE FROM pedidos WHERE id_pedido = :id');
                $del->execute(['id' => $idExistente]);
            }
        }

        $upsertPedido = $pdo->prepare(
            'INSERT INTO pedidos (id_pedido, id_cliente, numero_pedido, direccion, metodo_pago, tipo_entrega, total, costo_envio, observaciones, comprobante, estado, fecha)
             VALUES (:id_pedido, :id_cliente, :numero_pedido, :direccion, :metodo_pago, :tipo_entrega, :total, :costo_envio, :observaciones, :comprobante, :estado, :fecha)
                         ON CONFLICT (id_pedido) DO UPDATE SET
                             id_cliente = EXCLUDED.id_cliente,
                             numero_pedido = EXCLUDED.numero_pedido,
                             direccion = EXCLUDED.direccion,
                             metodo_pago = EXCLUDED.metodo_pago,
                             tipo_entrega = EXCLUDED.tipo_entrega,
                             total = EXCLUDED.total,
                             costo_envio = EXCLUDED.costo_envio,
                             observaciones = EXCLUDED.observaciones,
                             comprobante = EXCLUDED.comprobante,
                             estado = EXCLUDED.estado,
                             fecha = EXCLUDED.fecha'
        );

        $delItems = $pdo->prepare('DELETE FROM detalle_pedido WHERE id_pedido = :id_pedido');
        $insItem = $pdo->prepare('INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (:id_pedido, :id_producto, :cantidad, :precio, :subtotal)');

        foreach ($pedidos as $pedido) {
            $idPedido = (int)($pedido['id'] ?? 0);
            if ($idPedido <= 0) {
                continue;
            }

            $clienteNombre = trim((string)($pedido['cliente'] ?? 'Cliente'));
            $telefono = trim((string)($pedido['telefono'] ?? ''));
            $correo = trim((string)($pedido['correo'] ?? ''));
            if ($telefono === '') {
                $telefono = '0000-0000';
            }
            if ($correo === '') {
                $correo = 'cliente' . $idPedido . '@local.test';
            }

            $idCliente = buscar_o_crear_cliente_pedido(
                $pdo,
                $clienteNombre,
                $telefono,
                $correo,
                (string)($pedido['direccion'] ?? '')
            );

            $upsertPedido->execute([
                'id_pedido' => $idPedido,
                'id_cliente' => $idCliente,
                'numero_pedido' => trim((string)($pedido['numero_pedido'] ?? ('PED-' . $idPedido))),
                'direccion' => trim((string)($pedido['direccion'] ?? '')),
                'metodo_pago' => trim((string)($pedido['metodo_pago'] ?? 'SINPE')),
                'tipo_entrega' => trim((string)($pedido['tipo_entrega'] ?? 'envio')),
                'total' => (float)($pedido['total'] ?? 0),
                'costo_envio' => (float)($pedido['costo_envio'] ?? 0),
                'observaciones' => trim((string)($pedido['observaciones'] ?? '')),
                'comprobante' => trim((string)($pedido['comprobante'] ?? '')),
                'estado' => trim((string)($pedido['estado'] ?? 'Pendiente')),
                'fecha' => pedido_hora_sql((string)($pedido['fecha'] ?? '')),
            ]);

            $delItems->execute(['id_pedido' => $idPedido]);
            foreach (($pedido['items'] ?? []) as $item) {
                $cantidad = max(1, (int)($item['cantidad'] ?? 1));
                $precio = (float)($item['precio'] ?? 0);
                $nombreProducto = trim((string)($item['producto'] ?? ''));
                $idProducto = $nombreProducto !== '' ? buscar_producto_id_por_nombre($pdo, $nombreProducto) : null;

                $insItem->execute([
                    'id_pedido' => $idPedido,
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad,
                    'precio' => $precio,
                    'subtotal' => $cantidad * $precio,
                ]);
            }
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

function pedidos_cargar(): array
{
    $pedidosDb = pedidos_cargar_db();
    if ($pedidosDb) {
        file_put_contents(pedidos_data_file(), json_encode($pedidosDb, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $pedidosDb;
    }

    $archivo = pedidos_data_file();

    if (!file_exists($archivo)) {
        $seed = pedidos_seed();
        file_put_contents($archivo, json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    $pedidos = is_array($json) ? $json : [];

    if (!$pedidos) {
        $pedidos = pedidos_seed();
        file_put_contents($archivo, json_encode($pedidos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    $normalizado = false;
    foreach ($pedidos as $i => $pedido) {
        if (($pedido['metodo_pago'] ?? '') !== 'SINPE') {
            $pedidos[$i]['metodo_pago'] = 'SINPE';
            $normalizado = true;
        }
    }

    if ($normalizado) {
        file_put_contents($archivo, json_encode(array_values($pedidos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    usort($pedidos, static function ($a, $b) {
        $fechaA = (string)($a['fecha'] ?? '');
        $fechaB = (string)($b['fecha'] ?? '');
        return strcmp($fechaB, $fechaA);
    });

    return $pedidos;
}

function pedidos_guardar(array $pedidos): void
{
    pedidos_guardar_db($pedidos);
    file_put_contents(pedidos_data_file(), json_encode(array_values($pedidos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function pedidos_buscar_por_id(array $pedidos, int $id): ?array
{
    foreach ($pedidos as $pedido) {
        if ((int)($pedido['id'] ?? 0) === $id) {
            return $pedido;
        }
    }
    return null;
}

function pedidos_buscar_indice_por_id(array $pedidos, int $id): ?int
{
    foreach ($pedidos as $index => $pedido) {
        if ((int)($pedido['id'] ?? 0) === $id) {
            return $index;
        }
    }
    return null;
}
