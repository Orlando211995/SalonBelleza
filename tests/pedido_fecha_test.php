<?php

require_once __DIR__ . '/../Admin/pedidos/_data.php';

$casos = [
    ['2026-11-24 13:30:00 UTC', '2026-11-24 07:30:00'],
    ['2026-11-24 03:15:00 UTC', '2026-11-23 21:15:00'],
    ['2026-11-24 16:30:00 Asia/Tokyo', '2026-11-24 01:30:00'],
];

foreach (['UTC', 'Asia/Tokyo', 'America/Costa_Rica'] as $zonaServidor) {
    date_default_timezone_set($zonaServidor);
    foreach ($casos as [$instante, $esperado]) {
        $actual = pedido_fecha_local(new DateTimeImmutable($instante));
        if ($actual !== $esperado || pedido_hora_sql($actual) !== $esperado) {
            throw new RuntimeException("Fecha incorrecta para $instante con servidor $zonaServidor.");
        }
    }

    $fechaHistorica = '2026-08-20 23:45:00';
    if (pedido_hora_sql($fechaHistorica) !== $fechaHistorica) {
        throw new RuntimeException("La fecha historica cambio con servidor $zonaServidor.");
    }
}

echo "Fechas de pedidos verificadas.\n";
