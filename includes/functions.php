<?php
require_once 'db.php';

// Determinar dinámicamente la ruta base del proyecto
$scriptPath = dirname($_SERVER['SCRIPT_NAME']); // Obtiene la carpeta del script actual (ejemplo: /car_dealership/admin)
$basePath = str_replace('\\', '/', $scriptPath); // Reemplaza barras invertidas por barras (para compatibilidad con Windows)
$basePath = rtrim($basePath, '/'); // Elimina la barra final si existe
// Si el script está en una subcarpeta (como /car_dealership/admin), eliminamos la última parte para obtener la raíz del proyecto
$basePath = preg_replace('/\/admin$/', '', $basePath); // Elimina "/admin" si está al final
define('BASE_URL', $basePath . '/'); // Define BASE_URL (ejemplo: /car_dealership/)

// Función para iniciar sesión
function login($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['image'] = $user['image'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}
?>