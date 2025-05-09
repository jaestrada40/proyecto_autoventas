<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('models.php'); // Redirigir si no hay ID válido
}

$model_id = (int)$_GET['id'];

// Eliminar el modelo
try {
    $stmt = $pdo->prepare("DELETE FROM models WHERE id = ?");
    $stmt->execute([$model_id]);
} catch (PDOException $e) {
    // Manejar error (por ejemplo, si hay restricciones de clave foránea)
    $_SESSION['error'] = "No se pudo eliminar el modelo.";
    redirect('models.php');
}

// Redirigir a la página de modelos con un mensaje de éxito
$_SESSION['success'] = "Modelo eliminado exitosamente.";
redirect('models.php');
?>