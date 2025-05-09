<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Iniciar sesión (por si no está iniciada)
session_start();

// Depuración: Verificar estado de la sesión
if (!isset($_SESSION['user_id'])) {
    error_log("Error en delete_user.php: No hay user_id en la sesión");
    redirect(BASE_URL . 'login.php');
}

if (!isAdmin()) {
    error_log("Error en delete_user.php: El usuario no es administrador");
    redirect(BASE_URL . 'login.php');
}

if (!isset($_GET['id'])) {
    error_log("Error en delete_user.php: Falta el parámetro id");
    redirect(BASE_URL . 'admin/users.php');
}

$user_id = (int)$_GET['id'];

// Evitar que un administrador se elimine a sí mismo
if ($user_id == $_SESSION['user_id']) {
    error_log("Error en delete_user.php: Intento de auto-eliminación por user_id: $user_id");
    redirect(BASE_URL . 'admin/users.php');
}

// Verificar que el usuario existe
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
if (!$stmt->fetch()) {
    error_log("Error en delete_user.php: Usuario con id $user_id no existe");
    redirect(BASE_URL . 'admin/users.php');
}

// Eliminar el usuario
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    error_log("Usuario con id $user_id eliminado exitosamente");
} catch (PDOException $e) {
    error_log("Error en delete_user.php: No se pudo eliminar el usuario con id $user_id. Error: " . $e->getMessage());
}

redirect(BASE_URL . 'admin/users.php');
?>