<?php
/**
 * 4 Elementos To Go! - Gestión Integral v15.0
 */
header("Content-Type: application/json; charset=UTF-8");
$mysqli = new mysqli("localhost", "u601500235_mattvzkz", "Rpp123456", "u601500235_comedor_app");

if ($mysqli->connect_error) {
    die(json_encode(["success" => false, "error" => "Fallo de conexión"]));
}
$mysqli->set_charset("utf8");

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'ver_pedidos') {
        $sql = "SELECT p.id, u.nombre as usuario, f.nombre as fuerte, g.nombre as guarnicion, b.nombre as bebida, p.observaciones 
                FROM preordenes p 
                JOIN usuarios u ON p.usuario_id = u.id
                JOIN menu_elementos f ON p.fuerte_id = f.id 
                JOIN menu_elementos g ON p.guarnicion_id = g.id
                JOIN menu_elementos b ON p.bebida_id = b.id
                WHERE p.estatus = 'pendiente' 
                ORDER BY p.id ASC";
        $res = $mysqli->query($sql);
        $list = [];
        if($res) while($row = $res->fetch_assoc()) { $list[] = $row; }
        echo json_encode(["success" => true, "pedidos" => $list]);
    } 
    elseif ($action === 'resumen_cocina') {
        $sql = "SELECT m.nombre, COUNT(p.id) as total FROM preordenes p 
                JOIN menu_elementos m ON (p.fuerte_id = m.id OR p.guarnicion_id = m.id OR p.bebida_id = m.id)
                WHERE p.estatus = 'pendiente' GROUP BY m.id";
        $res = $mysqli->query($sql);
        $resumen = [];
        if($res) while($row = $res->fetch_assoc()) { $resumen[] = $row; }
        echo json_encode(["success" => true, "resumen" => $resumen]);
    }
    elseif ($action === 'ver_notificaciones') {
        $res = $mysqli->query("SELECT titulo, mensaje FROM notificaciones ORDER BY id DESC LIMIT 3");
        $list = [];
        if($res) while($row = $res->fetch_assoc()) { $list[] = $row; }
        echo json_encode(["success" => true, "notificaciones" => $list]);
    }
    else {
        $res = $mysqli->query("SELECT id, categoria, nombre, stock_diario FROM menu_elementos WHERE disponible = 1");
        $menu = ["fuerte" => [], "guarnicion" => [], "bebida" => []];
        if($res) {
            while($row = $res->fetch_assoc()) { $menu[$row['categoria']][] = ["id" => $row['id'], "nombre" => $row['nombre'], "stock" => (int)$row['stock_diario']]; }
        }
        echo json_encode(["success" => true, "menu" => $menu]);
    }
}

if ($method === 'POST') {
    $act = $data['action'] ?? '';
    if ($act === 'marcar_entregado') {
        $pid = (int)$data['pedido_id'];
        $mysqli->query("UPDATE preordenes SET estatus = 'entregado' WHERE id = $pid");
        echo json_encode(["success" => true]);
    }
    elseif ($act === 'set_stock') {
        foreach($data['inventario'] as $item) {
            $id = (int)$item['id']; $cant = (int)$item['cantidad'];
            $mysqli->query("UPDATE menu_elementos SET stock_diario = $cant WHERE id = $id");
        }
        echo json_encode(["success" => true]);
    }
    elseif ($act === 'enviar_notificacion') {
        $t = $mysqli->real_escape_string($data['titulo']);
        $m = $mysqli->real_escape_string($data['mensaje']);
        $mysqli->query("INSERT INTO notificaciones (titulo, mensaje, fecha) VALUES ('$t', '$m', NOW())");
        echo json_encode(["success" => true]);
    }
    else {
        $uid = (int)$data['usuario_id'];
        $f_id = (int)$data['fuerte']; $g_id = (int)$data['guarnicion']; $b_id = (int)$data['bebida'];
        $obs = $mysqli->real_escape_string($data['observaciones']);
        
        $user_data = $mysqli->query("SELECT saldo_creditos FROM usuarios WHERE id = $uid")->fetch_assoc();
        if ($user_data['saldo_creditos'] <= 0) {
            echo json_encode(["success" => false, "error" => "Saldo insuficiente"]); exit;
        }

        $mysqli->begin_transaction();
        try {
            $mysqli->query("UPDATE usuarios SET saldo_creditos = saldo_creditos - 1 WHERE id = $uid");
            $stmt = $mysqli->prepare("INSERT INTO preordenes (usuario_id, fuerte_id, guarnicion_id, bebida_id, observaciones, estatus, fecha) VALUES (?, ?, ?, ?, ?, 'pendiente', CURRENT_DATE)");
            $stmt->bind_param("iiiis", $uid, $f_id, $g_id, $b_id, $obs);
            $stmt->execute();
            $mysqli->query("UPDATE menu_elementos SET stock_diario = stock_diario - 1 WHERE id IN ($f_id, $g_id, $b_id)");
            $mysqli->commit();
            echo json_encode(["success" => true, "nuevo_saldo" => ($user_data['saldo_creditos'] - 1)]);
        } catch (Exception $e) { $mysqli->rollback(); echo json_encode(["success" => false, "error" => "Error de sistema"]); }
    }
}
$mysqli->close();