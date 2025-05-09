<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $name = $_POST['name'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $name]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al registrarse: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
exit;
?>