<?php
/**
 * COMEDOR URBANO - Consulta de Perfil Real
 * Versión con cabeceras CORS para evitar "Error de Red"
 */

// Cabeceras de seguridad para que el navegador permita la conexión
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// --- CREDENCIALES REALES ---
$host = "localhost"; 
$db   = "u601500235_comedor_app"; 
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Error de conexión"]));
}

$conn->set_charset("utf8");

// Limpiar el email de entrada
$email = isset($_GET['email']) ? strtolower(trim($conn->real_escape_string($_GET['email']))) : '';

if ($email != '') {
    $sql = "SELECT id, nombre, saldo_creditos FROM usuarios WHERE LOWER(TRIM(email)) = '$email'";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true, 
            "data" => [
                "id" => $row['id'],
                "nombre" => $row['nombre'],
                "saldo" => (int)$row['saldo_creditos']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No encontrado"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Falta email"]);
}
$conn->close();
?>