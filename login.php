<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if (login($username, $password)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
exit;
?>