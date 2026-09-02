<?php

require_once __DIR__ . '/../../Includes/conexion.php';

function citas_data_file(): string
{
    return __DIR__ . '/citas_data.json';
}

function servicios_data_file(): string
{
    return __DIR__ . '/../servicios/servicios_data.json';
}

function estados_cita_validos(): array
{
    return ['Pendiente', 'Confirmada', 'En proceso', 'Finalizada', 'Cancelada', 'No asistio'];
}

function pagos_cita_validos(): array
{
    return ['SINPE', 'Efectivo', 'Tarjeta', 'No aplica'];
}

function cargar_json_array(string $archivo): array
{
    if (!file_exists($archivo)) {
        return [];
    }

    $json = json_decode(file_get_contents($archivo) ?: '[]', true);
    if (!is_array($json)) {
        return [];
    }

    return $json;
}

function guardar_json_array(string $archivo, array $data): void
{
    file_put_contents($archivo, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function cargar_servicios_citas(): array
{
    $pdo = obtenerConexionSalon();
    if ($pdo) {
        $stmt = $pdo->query("SELECT id_servicio, nombre, categoria, descripcion, duracion, precio, imagen, estado FROM servicios ORDER BY nombre ASC");
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
                'estado' => (string)($fila['estado'] ?? ''),
            ];
        }

        if ($servicios) {
            return $servicios;
        }
    }

    $servicios = cargar_json_array(servicios_data_file());
    usort($servicios, static function ($a, $b) {
        return strcmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
    });
    return $servicios;
}

function cita_creada_en_iso(string $fechaCreacion): string
{
    if ($fechaCreacion === '') {
        return date('c');
    }

    $ts = strtotime($fechaCreacion);
    if ($ts === false) {
        return date('c');
    }

    return date('c', $ts);
}

function normalizar_hora_hm(string $hora): string
{
    $hora = trim($hora);
    if (preg_match('/^\d{2}:\d{2}$/', $hora) === 1) {
        return $hora;
    }

    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora) === 1) {
        return substr($hora, 0, 5);
    }

    return $hora;
}

function cargar_citas_db(array $serviciosIndex): array
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return [];
    }

    $sql = "SELECT ci.id_cita, ci.id_servicio, ci.empleado, ci.fecha, h.hora, ci.observaciones, ci.estado, ci.pago, ci.fecha_creacion,
                   cl.nombre AS cliente, cl.telefono, cl.correo,
                   s.nombre AS servicio, s.duracion
            FROM citas ci
            JOIN clientes cl ON ci.id_cliente = cl.id_cliente
            JOIN servicios s ON ci.id_servicio = s.id_servicio
            JOIN horarios h ON ci.id_horario = h.id_horario
            ORDER BY ci.fecha ASC, h.hora ASC";

    $stmt = $pdo->query($sql);
    $filas = $stmt ? $stmt->fetchAll() : [];
    $citas = [];

    foreach ($filas as $fila) {
        $idServicio = (int)($fila['id_servicio'] ?? 0);
        $servicio = $serviciosIndex[$idServicio] ?? null;
        $duracion = (int)($fila['duracion'] ?? ($servicio['duracion'] ?? 30));

        $citas[] = [
            'id' => (int)($fila['id_cita'] ?? 0),
            'cliente' => (string)($fila['cliente'] ?? ''),
            'telefono' => (string)($fila['telefono'] ?? ''),
            'correo' => (string)($fila['correo'] ?? ''),
            'servicio_id' => $idServicio,
            'servicio' => (string)($fila['servicio'] ?? ($servicio['nombre'] ?? '')),
            'duracion' => $duracion,
            'empleado' => (string)($fila['empleado'] ?? ''),
            'fecha' => (string)($fila['fecha'] ?? ''),
            'hora' => normalizar_hora_hm((string)($fila['hora'] ?? '')),
            'observaciones' => (string)($fila['observaciones'] ?? ''),
            'estado' => normalizar_estado_cita((string)($fila['estado'] ?? 'Pendiente')),
            'pago' => normalizar_pago_cita((string)($fila['pago'] ?? 'No aplica')),
            'created_at' => cita_creada_en_iso((string)($fila['fecha_creacion'] ?? '')),
        ];
    }

    return $citas;
}

function buscar_o_crear_cliente_cita(PDO $pdo, string $cliente, string $telefono, string $correo): int
{
    $stmt = $pdo->prepare('SELECT id_cliente FROM clientes WHERE telefono = :telefono AND correo = :correo LIMIT 1');
    $stmt->execute(['telefono' => $telefono, 'correo' => $correo]);
    $fila = $stmt->fetch();
    if ($fila && !empty($fila['id_cliente'])) {
        $idCliente = (int)$fila['id_cliente'];
        $upd = $pdo->prepare('UPDATE clientes SET nombre = :nombre WHERE id_cliente = :id');
        $upd->execute(['nombre' => $cliente, 'id' => $idCliente]);
        return $idCliente;
    }

    $ins = $pdo->prepare('INSERT INTO clientes (nombre, telefono, correo) VALUES (:nombre, :telefono, :correo)');
    $ins->execute([
        'nombre' => $cliente,
        'telefono' => $telefono,
        'correo' => $correo,
    ]);

    return (int)$pdo->lastInsertId();
}

function buscar_o_crear_horario_id(PDO $pdo, string $horaHm): int
{
    $hora = normalizar_hora_hm($horaHm);
    $horaSql = $hora . ':00';

    $stmt = $pdo->prepare('SELECT id_horario FROM horarios WHERE hora = :hora LIMIT 1');
    $stmt->execute(['hora' => $horaSql]);
    $fila = $stmt->fetch();
    if ($fila && !empty($fila['id_horario'])) {
        return (int)$fila['id_horario'];
    }

    $ins = $pdo->prepare('INSERT INTO horarios (hora) VALUES (:hora)');
    $ins->execute(['hora' => $horaSql]);
    return (int)$pdo->lastInsertId();
}

function guardar_citas_db(array $citas): bool
{
    $pdo = obtenerConexionSalon();
    if (!$pdo) {
        return false;
    }

    $idsNuevos = [];
    foreach ($citas as $cita) {
        $id = (int)($cita['id'] ?? 0);
        if ($id > 0) {
            $idsNuevos[$id] = true;
        }
    }

    try {
        $pdo->beginTransaction();

        $idsExistentes = [];
        $stmtIds = $pdo->query('SELECT id_cita FROM citas');
        foreach (($stmtIds ? $stmtIds->fetchAll() : []) as $filaId) {
            $id = (int)($filaId['id_cita'] ?? 0);
            if ($id > 0) {
                $idsExistentes[$id] = true;
            }
        }

        foreach ($idsExistentes as $idExistente => $_) {
            if (!isset($idsNuevos[$idExistente])) {
                $del = $pdo->prepare('DELETE FROM citas WHERE id_cita = :id');
                $del->execute(['id' => $idExistente]);
            }
        }

        $upsert = $pdo->prepare(
            'INSERT INTO citas (id_cita, id_cliente, id_servicio, id_horario, empleado, fecha, observaciones, estado, pago)
             VALUES (:id_cita, :id_cliente, :id_servicio, :id_horario, :empleado, :fecha, :observaciones, :estado, :pago)
                         ON CONFLICT (id_cita) DO UPDATE SET
                             id_cliente = EXCLUDED.id_cliente,
                             id_servicio = EXCLUDED.id_servicio,
                             id_horario = EXCLUDED.id_horario,
                             empleado = EXCLUDED.empleado,
                             fecha = EXCLUDED.fecha,
                             observaciones = EXCLUDED.observaciones,
                             estado = EXCLUDED.estado,
                             pago = EXCLUDED.pago'
        );

        foreach ($citas as $cita) {
            $idCita = (int)($cita['id'] ?? 0);
            if ($idCita <= 0) {
                continue;
            }

            $servicioId = (int)($cita['servicio_id'] ?? 0);
            if ($servicioId <= 0) {
                continue;
            }

            $cliente = trim((string)($cita['cliente'] ?? ''));
            $telefono = trim((string)($cita['telefono'] ?? ''));
            $correo = trim((string)($cita['correo'] ?? ''));
            if ($cliente === '' || $telefono === '' || $correo === '') {
                continue;
            }

            $idCliente = buscar_o_crear_cliente_cita($pdo, $cliente, $telefono, $correo);
            $idHorario = buscar_o_crear_horario_id($pdo, (string)($cita['hora'] ?? '00:00'));

            $upsert->execute([
                'id_cita' => $idCita,
                'id_cliente' => $idCliente,
                'id_servicio' => $servicioId,
                'id_horario' => $idHorario,
                'empleado' => trim((string)($cita['empleado'] ?? '')),
                'fecha' => (string)($cita['fecha'] ?? date('Y-m-d')),
                'observaciones' => trim((string)($cita['observaciones'] ?? '')),
                'estado' => normalizar_estado_cita((string)($cita['estado'] ?? 'Pendiente')),
                'pago' => normalizar_pago_cita((string)($cita['pago'] ?? 'No aplica')),
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

function indexar_servicios_por_id(array $servicios): array
{
    $index = [];
    foreach ($servicios as $servicio) {
        $id = (int)($servicio['id'] ?? 0);
        if ($id > 0) {
            $index[$id] = $servicio;
        }
    }
    return $index;
}

function servicio_por_id(int $id, array $serviciosIndex): ?array
{
    return $serviciosIndex[$id] ?? null;
}

function citas_seed(array $serviciosIndex): array
{
    $seed = [];

    $items = [
        ['cliente' => 'Maria', 'telefono' => '8888-0001', 'correo' => 'maria@email.com', 'servicio_id' => 1, 'fecha' => '2026-07-25', 'hora' => '09:00', 'estado' => 'Confirmada', 'pago' => 'SINPE'],
        ['cliente' => 'Jose', 'telefono' => '8888-0002', 'correo' => 'jose@email.com', 'servicio_id' => 3, 'fecha' => '2026-07-25', 'hora' => '09:30', 'estado' => 'Pendiente', 'pago' => 'Efectivo'],
        ['cliente' => 'Ana', 'telefono' => '8888-0003', 'correo' => 'ana@email.com', 'servicio_id' => 2, 'fecha' => '2026-07-25', 'hora' => '10:00', 'estado' => 'Confirmada', 'pago' => 'SINPE'],
    ];

    $id = 1;
    foreach ($items as $item) {
        $servicio = servicio_por_id((int)$item['servicio_id'], $serviciosIndex);
        if (!$servicio) {
            continue;
        }

        $seed[] = [
            'id' => $id++,
            'cliente' => $item['cliente'],
            'telefono' => $item['telefono'],
            'correo' => $item['correo'],
            'servicio_id' => (int)$servicio['id'],
            'servicio' => (string)$servicio['nombre'],
            'duracion' => (int)($servicio['duracion'] ?? 30),
            'empleado' => '',
            'fecha' => $item['fecha'],
            'hora' => $item['hora'],
            'observaciones' => '',
            'estado' => $item['estado'],
            'pago' => $item['pago'],
            'created_at' => date('c'),
        ];
    }

    return $seed;
}

function cargar_citas(array $serviciosIndex): array
{
    $citasDb = cargar_citas_db($serviciosIndex);
    if ($citasDb) {
        guardar_json_array(citas_data_file(), $citasDb);
        return $citasDb;
    }

    $archivo = citas_data_file();
    $citas = cargar_json_array($archivo);

    if (!$citas) {
        $citas = citas_seed($serviciosIndex);
        guardar_json_array($archivo, $citas);
    }

    usort($citas, static function ($a, $b) {
        $keyA = (string)($a['fecha'] ?? '') . ' ' . (string)($a['hora'] ?? '');
        $keyB = (string)($b['fecha'] ?? '') . ' ' . (string)($b['hora'] ?? '');
        return strcmp($keyA, $keyB);
    });

    return $citas;
}

function guardar_citas(array $citas): void
{
    guardar_citas_db($citas);
    guardar_json_array(citas_data_file(), $citas);
}

function siguiente_cita_id(array $citas): int
{
    $maxId = 0;
    foreach ($citas as $cita) {
        $id = (int)($cita['id'] ?? 0);
        if ($id > $maxId) {
            $maxId = $id;
        }
    }
    return $maxId + 1;
}

function normalizar_estado_cita(string $estado): string
{
    return in_array($estado, estados_cita_validos(), true) ? $estado : 'Pendiente';
}

function normalizar_pago_cita(string $pago): string
{
    return in_array($pago, pagos_cita_validos(), true) ? $pago : 'No aplica';
}

function fecha_valida_ymd(string $fecha): bool
{
    $dt = DateTimeImmutable::createFromFormat('Y-m-d', $fecha);
    return $dt && $dt->format('Y-m-d') === $fecha;
}

function hora_valida_hm(string $hora): bool
{
    $dt = DateTimeImmutable::createFromFormat('H:i', $hora);
    return $dt && $dt->format('H:i') === $hora;
}

function horario_salon_por_fecha(string $fechaYmd): ?array
{
    if (!fecha_valida_ymd($fechaYmd)) {
        return null;
    }

    $dt = new DateTimeImmutable($fechaYmd);
    $diaSemana = (int)$dt->format('N');

    if ($diaSemana === 7) {
        return null;
    }

    if ($diaSemana === 6) {
        return ['apertura' => '08:00', 'cierre' => '16:00', 'intervalo' => 30];
    }

    return ['apertura' => '08:00', 'cierre' => '18:00', 'intervalo' => 30];
}

function timestamp_cita(string $fecha, string $hora): int
{
    return strtotime($fecha . ' ' . $hora . ':00') ?: 0;
}

function cita_bloquea_espacio(array $cita): bool
{
    $estado = (string)($cita['estado'] ?? 'Pendiente');
    return !in_array($estado, ['Cancelada', 'No asistio'], true);
}

function cita_se_traslapa(string $fecha, string $hora, int $duracionMin, array $cita): bool
{
    if ((string)($cita['fecha'] ?? '') !== $fecha) {
        return false;
    }

    if (!cita_bloquea_espacio($cita)) {
        return false;
    }

    $inicioNuevo = timestamp_cita($fecha, $hora);
    $finNuevo = $inicioNuevo + ($duracionMin * 60);

    $inicioExistente = timestamp_cita($fecha, (string)($cita['hora'] ?? '00:00'));
    $finExistente = $inicioExistente + ((int)($cita['duracion'] ?? 30) * 60);

    return ($inicioNuevo < $finExistente) && ($finNuevo > $inicioExistente);
}

function hora_disponible_para_cita(string $fecha, string $hora, int $duracionMin, array $citas, int $ignorarId = 0): bool
{
    $horario = horario_salon_por_fecha($fecha);
    if (!$horario) {
        return false;
    }

    $inicio = timestamp_cita($fecha, $hora);
    $fin = $inicio + ($duracionMin * 60);
    $inicioSalon = timestamp_cita($fecha, (string)$horario['apertura']);
    $finSalon = timestamp_cita($fecha, (string)$horario['cierre']);

    if ($inicio < $inicioSalon || $fin > $finSalon) {
        return false;
    }

    foreach ($citas as $cita) {
        if ($ignorarId > 0 && (int)($cita['id'] ?? 0) === $ignorarId) {
            continue;
        }

        if (cita_se_traslapa($fecha, $hora, $duracionMin, $cita)) {
            return false;
        }
    }

    return true;
}

function generar_horarios_del_dia(string $fecha): array
{
    $horario = horario_salon_por_fecha($fecha);
    if (!$horario) {
        return [];
    }

    $intervalo = (int)$horario['intervalo'];
    $inicio = timestamp_cita($fecha, (string)$horario['apertura']);
    $fin = timestamp_cita($fecha, (string)$horario['cierre']);

    $horas = [];
    for ($ts = $inicio; $ts < $fin; $ts += ($intervalo * 60)) {
        $horas[] = date('H:i', $ts);
    }

    return $horas;
}

function generar_horas_disponibles(string $fecha, int $duracionMin, array $citas, int $ignorarId = 0): array
{
    $horas = generar_horarios_del_dia($fecha);
    $disponibles = [];

    foreach ($horas as $hora) {
        if (hora_disponible_para_cita($fecha, $hora, $duracionMin, $citas, $ignorarId)) {
            $disponibles[] = $hora;
        }
    }

    return $disponibles;
}

function resumen_dia(string $fecha, array $citas): array
{
    $resultado = ['total' => 0, 'pendientes' => 0, 'confirmadas' => 0];

    foreach ($citas as $cita) {
        if (($cita['fecha'] ?? '') !== $fecha) {
            continue;
        }
        $resultado['total']++;
        $estado = (string)($cita['estado'] ?? 'Pendiente');
        if ($estado === 'Pendiente') {
            $resultado['pendientes']++;
        }
        if ($estado === 'Confirmada') {
            $resultado['confirmadas']++;
        }
    }

    return $resultado;
}
