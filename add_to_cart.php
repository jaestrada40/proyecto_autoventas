<?php
// Verificar si la sesión ya está iniciada antes de llamarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = BASE_URL . 'vehicle_detail.php?id=' . ($_POST['vehicle_id'] ?? 0);
    redirect(BASE_URL . 'login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $_POST['vehicle_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $user_id = $_SESSION['user_id'];

    if ($vehicle_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = 'Datos de carrito inválidos.';
        redirect(BASE_URL . 'vehicle_detail.php?id=' . $vehicle_id);
    }

    $stmt = $pdo->prepare("SELECT price FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        $_SESSION['error'] = 'Vehículo no encontrado.';
        redirect(BASE_URL . 'index.php');
    }

    $total_price = $vehicle['price'] * $quantity;
    $sale_date = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO sales (user_id, vehicle_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $vehicle_id, $quantity, $total_price, $sale_date]);

    $_SESSION['success'] = 'Vehículo añadido al carrito con éxito.';
    redirect(BASE_URL . 'vehicle_detail.php?id=' . $vehicle_id);
}

redirect(BASE_URL . 'index.php');