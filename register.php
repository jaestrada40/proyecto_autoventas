<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $password = $_POST['password'] ?? '';

    // Depuración: Registrar los datos recibidos
    error_log("Intentando registrar usuario: username=$username, email=$email, first_name=$first_name, last_name=$last_name");

    // Validar que los campos requeridos no estén vacíos
    if (empty($username) || empty($email) || empty($first_name) || empty($last_name) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Todos los campos son obligatorios']);
        exit;
    }

    // Validar si el username ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya existe']);
        exit;
    }

    // Validar si el email ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'El correo electrónico ya existe']);
        exit;
    }

    // Valores predeterminados para los campos no enviados
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $image = 'default-user.png'; // Imagen predeterminada
    $role = 'client'; // Rol predeterminado
    $created_at = date('Y-m-d H:i:s'); // Fecha y hora actuales

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, image, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hashed, $first_name, $last_name, $image, $role, $created_at]);
        error_log("Usuario registrado exitosamente: username=$username");
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Error al registrar usuario: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error al registrarse: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
exit;
?>