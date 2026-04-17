<?php
/**
 * CUATRO ELEMENTOS - AVICAADMON API v3.1
 * Versión de alta compatibilidad
 */
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");

if ($mysqli->connect_error) {
    die(json_encode(["success" => false, "message" => "Error DB"]));
}

$mysqli->set_charset("utf8");
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semana';

// Configurar WHERE según filtro
if ($filter === 'dia') {
    $where = " AND DATE(fecha) = CURDATE() ";
    $group = " HOUR(fecha) ";
} elseif ($filter === 'mes') {
    $where = " AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE()) ";
    $group = " DAY(fecha) ";
} else {
    $where = " AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1) ";
    $group = " DAYNAME(fecha) ";
}

// 1. Datos de SERVICIOS
$q_serv_total = $mysqli->query("SELECT COUNT(*) as t FROM transacciones WHERE tipo='consumo' $where");
$total_serv = ($q_serv_total) ? (int)$q_serv_total->fetch_assoc()['t'] : 0;

$labels_s = []; $values_s = [];
$q_serv_chart = $mysqli->query("SELECT $group as lbl, COUNT(*) as qty FROM transacciones WHERE tipo='consumo' $where GROUP BY lbl ORDER BY fecha ASC");
while($r = $q_serv_chart->fetch_assoc()){ $labels_s[] = $r['lbl']; $values_s[] = (int)$r['qty']; }

// 2. Datos de FINANZAS
$q_fin_total = $mysqli->query("SELECT SUM(monto_mxn) as t FROM transacciones WHERE tipo='recarga' $where");
$total_fin = ($q_fin_total) ? (float)$q_fin_total->fetch_assoc()['t'] : 0;

$labels_f = []; $values_f = [];
$q_fin_chart = $mysqli->query("SELECT $group as lbl, SUM(monto_mxn) as qty FROM transacciones WHERE tipo='recarga' $where GROUP BY lbl ORDER BY fecha ASC");
while($r = $q_fin_chart->fetch_assoc()){ $labels_f[] = $r['lbl']; $values_f[] = (float)$r['qty']; }

// 3. Listado Finanzas (Últimas 10 recargas)
$lista_f = [];
$q_lista = $mysqli->query("SELECT u.nombre, t.monto_mxn, t.fecha FROM transacciones t LEFT JOIN usuarios u ON t.usuario_id = u.id WHERE t.tipo='recarga' ORDER BY t.fecha DESC LIMIT 10");
while($r = $q_lista->fetch_assoc()){
    $lista_f[] = ["u" => $r['nombre'] ?? "Usuario", "m" => $r['monto_mxn'], "h" => date('d/m H:i', strtotime($r['fecha']))];
}

// 4. Listado Servicios (Últimos 10 consumos)
$lista_s = [];
$q_lista_s = $mysqli->query("SELECT u.nombre, t.fecha FROM transacciones t LEFT JOIN usuarios u ON t.usuario_id = u.id WHERE t.tipo='consumo' ORDER BY t.fecha DESC LIMIT 10");
while($r = $q_lista_s->fetch_assoc()){
    $lista_s[] = ["u" => $r['nombre'] ?? "Usuario", "t" => date('H:i', strtotime($r['fecha']))];
}

$output = json_encode([
    "success" => true,
    "servicios" => ["total" => $total_serv, "labels" => $labels_s, "values" => $values_s, "lista" => $lista_s],
    "finanzas" => ["total" => number_format($total_fin, 2, '.', ''), "labels" => $labels_f, "values" => $values_f, "lista" => $lista_f]
]);

if (ob_get_length()) ob_clean();
echo $output;
$mysqli->close();