<?php
$host = "localhost"; // Hostinger suele usar localhost
$db   = "u601500235_comedor_app"; // Nombre que te dé Hostinger
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

// Intentar conectar con reporte de errores encendido
mysqli_report(MYSQLI_REPORT_OFF); 
$conn = @new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    // Si falla, enviamos el error como JSON para que no truene el JS
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false, 
        "error" => "Fallo de conexión: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8");
?>