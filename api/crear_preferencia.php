<?php
/**
 * 4 Elementos To Go! - Mercado Pago Integration v15.9
 */
header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true);
$usuario_id = isset($data['usuario_id']) ? $data['usuario_id'] : null;

if (!$usuario_id) {
    die(json_encode(["success" => false, "error" => "ID de usuario faltante"]));
}

$access_token = "APP_USR-31690893386938-040523-9c116313f91a936792c66f77f14a3d39-3316391449"; 

$preference_data = [
    "items" => [[
        "title" => "1 Crédito de Comida - 4 Elementos To Go!",
        "quantity" => 1,
        "currency_id" => "MXN",
        "unit_price" => 30.00
    ]],
    "back_urls" => [
        // ESTA LÍNEA ES LA CLAVE: Envía el ID del usuario de vuelta al perfil
        "success" => "https://comedor.solucionesrgvhsa.tech/perfil.html?pago=exitoso&uid=" . $usuario_id,
        "failure" => "https://comedor.solucionesrgvhsa.tech/perfil.html?status=failure",
        "pending" => "https://comedor.solucionesrgvhsa.tech/perfil.html?status=pending"
    ],
    "auto_return" => "approved",
    "external_reference" => (string)$usuario_id
];

$ch = curl_init("https://api.mercadopago.com/checkout/preferences");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if (isset($result['init_point'])) {
    echo json_encode(["success" => true, "init_point" => $result['init_point']]);
} else {
    echo json_encode(["success" => false, "error" => "No se pudo crear la preferencia"]);
}