<?php
/**
 * CUATRO ELEMENTOS - PROCESADOR DE ÉXITO POST-PAGO
 */
header("Content-Type: text/html; charset=UTF-8");

$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");
$mysqli->set_charset("utf8");

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if ($status === 'approved' && $user_id > 0 && $cantidad > 0) {
    
    // 1. Actualizar Saldo
    $mysqli->query("UPDATE usuarios SET saldo_creditos = saldo_creditos + $cantidad WHERE id = $user_id");
    
    // 2. Registrar Transacción
    $monto_total = ($cantidad == 1) ? 30.00 : (($cantidad == 5) ? 140.00 : 270.00);
    $stmt = $mysqli->prepare("INSERT INTO transacciones (usuario_id, tipo, cantidad, monto_mxn) VALUES (?, 'recarga', ?, ?)");
    $stmt->bind_param("iid", $user_id, $cantidad, $monto_total);
    $stmt->execute();

    // 3. Interfaz de Éxito
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Recarga Exitosa</title>
        <link href='https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700&display=swap' rel='stylesheet'>
        <style>
            body { background: #FBFBFB; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; text-align: center; }
            .card { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); }
            .check { font-size: 50px; color: #8CC63F; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='check'>✔</div>
            <h1 style='color: #1A1A1A; margin: 0;'>RECARGA EXITOSA</h1>
            <p style='color: #888;'>Se han sumado $cantidad créditos a tu cuenta.</p>
            <script>
                // Actualizar saldo local para evitar discrepancia visual
                let user = JSON.parse(localStorage.getItem('usuario'));
                if(user) {
                    user.saldo_creditos = parseInt(user.saldo_creditos || 0) + $cantidad;
                    localStorage.setItem('usuario', JSON.stringify(user));
                }
                setTimeout(() => { window.location.href = 'perfil.html'; }, 3000);
            </script>
        </div>
    </body>
    </html>";
} else {
    header("Location: perfil.html?error=pago_invalido");
}
$mysqli->close();