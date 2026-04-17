<?php
/**
 * CUATRO ELEMENTOS - CORE API v2
 * Optimizada para Hostinger LiteSpeed
 */
ob_start(); // Iniciar buffer para evitar salidas accidentales
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=UTF-8");

// Conexión con variables directas para mayor velocidad
$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");

if ($mysqli->connect_error) {
    die(json_encode(["success" => false, "error" => "No DB"]));
}

$mysqli->set_charset("utf8");

// 1. Ingresos (Saldos)
$res_inc = $mysqli->query("SELECT SUM(monto_mxn) as total FROM transacciones WHERE tipo='recarga'");
$row_inc = $res_inc->fetch_assoc();
$money = $row_inc['total'] ? number_format((float)$row_inc['total'], 2, '.', '') : "0.00";

// 2. Servicios (Comidas)
$res_ser = $mysqli->query("SELECT COUNT(*) as total FROM transacciones WHERE tipo='consumo'");
$row_ser = $res_ser->fetch_assoc();
$servs = (int)($row_ser['total'] ?? 0);

// 3. Historial (Bitácora)
$hist = [];
$res_h = $mysqli->query("SELECT u.usuario, t.tipo, t.fecha FROM transacciones t LEFT JOIN usuarios u ON t.usuario_id = u.id ORDER BY t.fecha DESC LIMIT 10");
if ($res_h) {
    while($r = $res_h->fetch_assoc()){
        $hist[] = [
            "nombre" => $r['usuario'] ?? "Comensal",
            "tipo" => $r['tipo'],
            "fecha" => date('H:i', strtotime($r['fecha']))
        ];
    }
}

// 4. Gráfica (Datos reales de los últimos 7 días)
$labels = []; $values = [];
$res_g = $mysqli->query("SELECT DATE(fecha) as d, COUNT(*) as c FROM transacciones WHERE tipo='consumo' GROUP BY d ORDER BY d ASC LIMIT 7");
if ($res_g && $res_g->num_rows > 0) {
    while($r = $res_g->fetch_assoc()){
        $labels[] = date('d/m', strtotime($r['d']));
        $values[] = (int)$r['c'];
    }
} else {
    $labels = ["Hoy"]; $values = [$servs];
}

// Limpiar cualquier salida previa y enviar JSON
$output = json_encode([
    "success" => true,
    "stats" => [
        "recaudacion" => $money,
        "servicios" => $servs,
        "grafica" => ["labels" => $labels, "data" => $values],
        "historial" => $hist
    ]
]);

ob_end_clean(); // Limpiar buffer de cualquier espacio en blanco accidental
echo $output;
$mysqli->close();