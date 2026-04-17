<?php
include '../config.php';
// Aquí integrarás una librería de JWT o un hash simple con timestamp
$token = md5(uniqid(rand(), true)); 
// Guardar token en DB con expiración de 1 minuto
echo json_encode(["token" => $token, "expires" => time() + 60]);
?>