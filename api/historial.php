<?php
include '../config.php';
header('Content-Type: application/json');

// Obtenemos el ID del usuario (esto vendrá de tu sesión o localStorage en el front)
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode(["success" => false, "message" => "ID de usuario no válido"]);
    exit;
}

// Consulta SQL para obtener las últimas 20 transacciones
$sql = "SELECT 
            tipo, 
            cantidad, 
            monto_mxn, 
            DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') as fecha_formateada 
        FROM transacciones 
        WHERE usuario_id = ? 
        ORDER BY fecha DESC 
        LIMIT 20";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$movimientos = [];
while ($row = $result->fetch_assoc()) {
    $movimientos[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $movimientos
]);

$stmt->close();
$conn->close();
?>