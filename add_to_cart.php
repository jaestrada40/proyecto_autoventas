<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $_POST['vehicle_id'] ?? 0;
    $quantity = min($_POST['quantity'] ?? 1, 10); // Límite razonable
    $quantity = max($quantity, 1); // No permitir cantidades negativas

    if ($vehicle_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Datos de carrito inválidos.']);
        exit;
    }

    // Verificar stock
    $stmt = $pdo->prepare("SELECT stock, price FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle || $vehicle['stock'] < $quantity) {
        echo json_encode(['success' => false, 'error' => 'No hay suficiente stock disponible. Stock actual: ' . ($vehicle['stock'] ?? 0)]);
        exit;
    }

    // Inicializar el carrito en sesión si no existe
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Añadir o actualizar el ítem en el carrito
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['vehicle_id'] == $vehicle_id) {
            $item['quantity'] = $quantity;
            $item['total_price'] = $vehicle['price'] * $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'vehicle_id' => $vehicle_id,
            'quantity' => $quantity,
            'total_price' => $vehicle['price'] * $quantity
        ];
    }

    // Actualizar el conteo del carrito
    $_SESSION['cart_count'] = 0;
    foreach ($_SESSION['cart'] as $item) {
        $_SESSION['cart_count'] += $item['quantity'];
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
exit;