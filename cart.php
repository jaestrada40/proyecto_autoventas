<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

if (isset($_GET['vehicle_id'])) {
    $vehicle_id = $_GET['vehicle_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = 1; // Simplificado: una unidad por compra

    $stmt = $pdo->prepare("SELECT price FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch();

    if ($vehicle) {
        $total_price = $vehicle['price'] * $quantity;
        $stmt = $pdo->prepare("INSERT INTO sales (user_id, vehicle_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $vehicle_id, $quantity, $total_price]);

        $stmt = $pdo->prepare("UPDATE vehicles SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$quantity, $vehicle_id]);

        $success = "Compra realizada con éxito.";
    } else {
        $error = "Vehículo no encontrado.";
    }
}
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Carrito de Compras</h1>
    <?php if (isset($success)): ?>
        <p class="text-green-600 mb-4"><?php echo $success; ?></p>
    <?php elseif (isset($error)): ?>
        <p class="text-red-600 mb-4"><?php echo $error; ?></p>
    <?php endif; ?>
    <p class="text-gray-600">Tu compra ha sido procesada. Puedes ver el historial de tus compras en <a href="profile.php" class="text-blue-600">tu perfil</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>