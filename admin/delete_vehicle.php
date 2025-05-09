<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect(BASE_URL . 'login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect(BASE_URL . 'admin/vehicles.php');
}

$vehicle_id = (int)$_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $_SESSION['success'] = "Vehículo eliminado exitosamente.";
} catch (PDOException $e) {
    $_SESSION['error'] = "No se pudo eliminar el vehículo. Asegúrate de que no haya dependencias.";
}

redirect(BASE_URL . 'admin/vehicles.php');
?>