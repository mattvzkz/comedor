<?php
/**
 * 4 Elementos To Go! - Acreditación Automática
 */
header("Content-Type: application/json; charset=UTF-8");
$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");

if ($mysqli->connect_error) { die(json_encode(["success" => false])); }
$mysqli->set_charset("utf8");

// Mercado Pago envía el ID en la URL de retorno o vía external_reference
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : null;

if ($uid) {
    // 1. Sumamos 1 crédito al usuario
    $sql = "UPDATE usuarios SET saldo_creditos = saldo_creditos + 1 WHERE id = $uid";
    
    if ($mysqli->query($sql)) {
        echo json_encode(["success" => true, "message" => "Crédito acreditado"]);
    } else {
        echo json_encode(["success" => false, "error" => "Error al actualizar"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "No se recibió ID de usuario"]);
}

$mysqli->close();