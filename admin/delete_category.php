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
    redirect('categories.php'); // Redirigir si no hay ID válido
}

$category_id = (int)$_GET['id'];

// Eliminar la categoría
try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
} catch (PDOException $e) {
    // Manejar error (por ejemplo, si hay modelos asociados a esta categoría debido a clave foránea)
    $_SESSION['error'] = "No se pudo eliminar la categoría. Asegúrate de que no haya modelos asociados.";
    redirect('categories.php');
}

// Redirigir a la página de categorías con un mensaje de éxito
$_SESSION['success'] = "Categoría eliminada exitosamente.";
redirect('categories.php');
?>