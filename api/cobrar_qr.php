<?php
/**
 * COMEDOR URBANO - Procesador de Cobro QR
 */
header("Content-Type: application/json");

$host = "localhost"; 
$db   = "u601500235_comedor_app"; 
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

$conn = new mysqli($host, $user, $pass, $db);

$datos = json_decode(file_get_contents("php://input"));
// El QR envía algo como "USER_ID:1", extraemos el número
$usuario_id = intval(str_replace("USER_ID:", "", $datos->qr_data));

if ($usuario_id > 0) {
    // 1. Verificar si tiene saldo antes de cobrar
    $res = $conn->query("SELECT saldo_creditos FROM usuarios WHERE id = $usuario_id");
    $user = $res->fetch_assoc();

    if ($user && $user['saldo_creditos'] > 0) {
        // 2. Descontar el crédito
        $conn->query("UPDATE usuarios SET saldo_creditos = saldo_creditos - 1 WHERE id = $usuario_id");
        // 3. Registrar el consumo
        $conn->query("INSERT INTO transacciones (usuario_id, tipo, cantidad) VALUES ($usuario_id, 'consumo', 1)");
        
        echo json_encode(["success" => true, "message" => "¡Buen provecho! Crédito descontado."]);
    } else {
        echo json_encode(["success" => false, "error" => "Saldo insuficiente o usuario no existe."]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Código QR inválido."]);
}
$conn->close();
?>