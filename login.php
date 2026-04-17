<?php
// login.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

// --- CREDENCIALES REALES HOSTINGER ---
$host = "localhost"; 
$db   = "u601500235_comedor_app"; 
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Error de conexión"]));
}

$datos = json_decode(file_get_contents("php://input"));

if ($datos && !empty($datos->email) && !empty($datos->password)) {
    $email = $conn->real_escape_string($datos->email);
    $password_cliente = $datos->password;

    // Buscar al usuario
    $sql = "SELECT id, nombre, password, saldo_creditos FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Verificar contraseña encriptada
        if (password_verify($password_cliente, $user_data['password'])) {
            // Guardar datos en la sesión del servidor
            $_SESSION['usuario_id'] = $user_data['id'];
            $_SESSION['nombre'] = $user_data['nombre'];

            echo json_encode([
                "success" => true,
                "message" => "Acceso concedido",
                "user" => [
                    "id" => $user_data['id'],
                    "nombre" => $user_data['nombre'],
                    "saldo" => $user_data['saldo_creditos']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "error" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "El usuario no existe"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
}

$conn->close();
?>