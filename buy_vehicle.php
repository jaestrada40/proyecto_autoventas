<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    redirect(BASE_URL . 'login.php');
}

// Verificar si se envió el formulario de compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicle_id = $_POST['vehicle_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $user_id = $_SESSION['user_id'];

    // Validar datos
    if ($vehicle_id <= 0 || $quantity <= 0) {
        $_SESSION['error'] = 'Datos de compra inválidos.';
        redirect(BASE_URL . 'vehicle_detail.php?id=' . $vehicle_id);
    }

    // Obtener el vehículo para calcular el precio total
    $stmt = $pdo->prepare("SELECT price FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if (!$vehicle) {
        $_SESSION['error'] = 'Vehículo no encontrado.';
        redirect(BASE_URL . 'index.php');
    }

    $total_price = $vehicle['price'] * $quantity;
    $sale_date = date('Y-m-d H:i:s');

    // Registrar la compra en la tabla sales
    $stmt = $pdo->prepare("INSERT INTO sales (user_id, vehicle_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $vehicle_id, $quantity, $total_price, $sale_date]);

    // Redirigir a una página de confirmación
    $_SESSION['success'] = '¡Compra realizada con éxito!';
    redirect(BASE_URL . 'profile.php');
}

// Si no es un POST, redirigir a la página principal
redirect(BASE_URL . 'index.php');