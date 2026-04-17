<?php
/**
 * CUATRO ELEMENTOS - AUTH AVICAADMON
 * Blindado contra cortes de canal
 */
ob_start();
session_start();
error_reporting(0);
ini_set('display_errors', 0);
header("Content-Type: application/json; charset=UTF-8");

// Conexión
$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");

if ($mysqli->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión"]));
}

$mysqli->set_charset("utf8");

// Leer datos de entrada
$input = file_get_contents("php://input");
$data = json_decode($input);

if ($data && !empty($data->user) && !empty($data->pass)) {
    $user = $mysqli->real_escape_string($data->user);
    $pass = $mysqli->real_escape_string($data->pass);

    // Consulta exacta a su tabla 'admins'
    $res = $mysqli->query("SELECT id, usuario, rol FROM admins WHERE usuario='$user' AND password='$pass' LIMIT 1");

    if ($res && $res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        
        // Registro de sesión
        $_SESSION['avica_admin_id'] = $admin['id'];
        
        echo json_encode([
            "success" => true, 
            "user" => $admin['usuario'],
            "rol" => $admin['rol']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Usuario o clave incorrectos"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}

// Limpiar y enviar
$output = ob_get_clean();
echo $output;
$mysqli->close();