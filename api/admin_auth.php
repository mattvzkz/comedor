<?php
/**
 * COMEDOR URBANO - AUTH FINAL (COLUMNAS REALES)
 */
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); exit();
}

$host = "localhost"; 
$db   = "u601500235_comedor_app"; 
$user = "u601500235_mattvzkz";
$pass = "Rpp123456";

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) { throw new Exception("Error de conexión a la Base de Datos"); }
    $conn->set_charset("utf8");

    $input = file_get_contents("php://input");
    $data = json_decode($input);

    if (isset($data->usuario) && isset($data->password)) {
        $u = $conn->real_escape_string($data->usuario);
        $p = $conn->real_escape_string($data->password);

        // SQL EXACTO: id, usuario, password, rol
        $sql = "SELECT usuario, rol FROM admins WHERE usuario = '$u' AND password = '$p' LIMIT 1";
        $result = $conn->query($sql);

        if (!$result) { throw new Exception("Error en la consulta: " . $conn->error); }

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode([
                "success" => true,
                "admin" => [
                    "nombre" => $row['usuario'], // Usamos 'usuario' ya que no hay columna 'nombre'
                    "rol" => $row['rol']
                ]
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Datos de acceso incompletos"]);
    }
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "DETALLE: " . $e->getMessage()]);
}
?>