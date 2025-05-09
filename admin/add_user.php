<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Asegurarse de que el usuario esté autenticado y sea administrador
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    header('Content-Type: application/json'); // Asegurar que la respuesta sea JSON

    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';
    $image = $_FILES['image']['name'] ?? 'default-user.png';

    // Validar si el nombre de usuario ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Usuario ya existe']);
        exit;
    }

    // Validar si el correo electrónico ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Correo electrónico ya existe']);
        exit;
    }

    // Proceder con la inserción si no hay duplicados
    if ($image !== 'default-user.png' && isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])) {
        $uploadDir = '../images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, image, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $first_name, $last_name, $image, $role]);
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al crear el usuario: ' . $e->getMessage()]);
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Acción no válida']);
exit;
?>