<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Depuración: Mostrar los datos recibidos
    error_log("Intentando login con username: $username, password: $password");

    if (login($username, $password)) {
        error_log("Login exitoso para usuario: $username");
        echo json_encode(['success' => true]);
    } else {
        error_log("Login fallido para usuario: $username");
        echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
exit;
?>