<?php
/**
 * COMEDOR URBANO - Registro de Usuarios
 * Hostinger Production Build - 2026
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// --- CREDENCIALES REALES HOSTINGER ---
$host = "localhost"; 
$db   = "u601500235_comedor_app"; 
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

// 1. Establecer conexión
$conn = new mysqli($host, $user, $pass, $db);

// 2. Verificar si la conexión falló
if ($conn->connect_error) {
    die(json_encode([
        "success" => false, 
        "error" => "Error de conexión a la base de datos: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8");

// 3. Capturar datos del frontend
$datos = json_decode(file_get_contents("php://input"));

if ($datos && !empty($datos->email)) {
    // Escapar datos para seguridad
    $nombre = $conn->real_escape_string($datos->nombre);
    $email = $conn->real_escape_string($datos->email);
    // Encriptar password (Seguridad industrial)
    $password_encriptada = password_hash($datos->password, PASSWORD_BCRYPT);
    $telefono = $conn->real_escape_string($datos->telefono);
    $lugar = $conn->real_escape_string($datos->lugar_trabajo);

    // 4. Insertar en la tabla 'usuarios'
    // Nota: Asegúrate de que tu tabla se llame 'usuarios' en phpMyAdmin
    $sql = "INSERT INTO usuarios (nombre, email, password, telefono, lugar_trabajo, saldo_creditos, rol) 
            VALUES ('$nombre', '$email', '$password_encriptada', '$telefono', '$lugar', 0, 'empleado')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            "success" => true, 
            "message" => "¡Bienvenido, $nombre! Tu cuenta ha sido creada."
        ]);
    } else {
        // Manejo de correos duplicados
        if ($conn->errno == 1062) {
            echo json_encode(["success" => false, "error" => "Este correo ya está registrado."]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al guardar: " . $conn->error]);
        }
    }
} else {
    echo json_encode(["success" => false, "error" => "No se recibieron datos válidos."]);
}

$conn->close();
?>