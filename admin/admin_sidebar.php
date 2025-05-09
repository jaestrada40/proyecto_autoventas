<?php
// Asegurar que la sesión esté iniciada y los datos del usuario estén disponibles
if (!isset($_SESSION['first_name']) || !isset($_SESSION['last_name']) || !isset($_SESSION['image'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT first_name, last_name, image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $_SESSION['first_name'] = $user['first_name'] ?? '';
    $_SESSION['last_name'] = $user['last_name'] ?? '';
    $_SESSION['image'] = $user['image'] ?? 'default-user.png';
}

// Depuración: Verificar si la imagen existe
$image_path = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . 'images/' . $_SESSION['image'];
if (!file_exists($image_path)) {
    error_log("Error en admin_sidebar.php: La imagen $image_path no existe");
    $_SESSION['image'] = 'default-user.png'; // Fallback a la imagen por defecto
}
?>
<div class="sidebar">
    <div class="user-profile mb-6 text-center">
        <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($_SESSION['image']); ?>" alt="Imagen de <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>" class="w-24 h-24 rounded-full mx-auto mb-2 shadow-md">
        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h3>
    </div>
    <h2 class="text-xl font-bold mb-4">Panel de Administración</h2>
    <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a>
    <a href="<?php echo BASE_URL; ?>admin/users.php">Usuarios</a>
    <a href="<?php echo BASE_URL; ?>admin/vehicles.php">Vehículos</a>
    <a href="<?php echo BASE_URL; ?>admin/categories.php">Categorías</a>
    <a href="<?php echo BASE_URL; ?>admin/models.php">Modelos</a>
    <a href="<?php echo BASE_URL; ?>admin/messages.php">Mensajes</a>
    <a href="<?php echo BASE_URL; ?>admin/logout.php">Cerrar Sesión</a>
</div>