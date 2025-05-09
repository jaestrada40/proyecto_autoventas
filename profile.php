<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $password = $_POST['password'];
    
    $update_fields = ["first_name = ?", "last_name = ?"];
    $params = [$first_name, $last_name];
    
    if (!empty($password)) {
        $update_fields[] = "password = ?";
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }
    
    $params[] = $user_id;
    $stmt = $pdo->prepare("UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?");
    $stmt->execute($params);
    redirect('profile.php');
}

// Consulta segura para las ventas
$stmt = $pdo->prepare("SELECT s.*, v.*, m.name AS model_name 
                       FROM sales s 
                       JOIN vehicles v ON s.vehicle_id = v.id 
                       JOIN models m ON v.model_id = m.id 
                       WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll();
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Mi Perfil</h1>
    
    <form method="POST" class="card mb-8">
        <h2 class="text-xl font-semibold mb-4">Editar Perfil</h2>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" placeholder="Nombre" class="border p-2 rounded w-full mb-4" required>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" placeholder="Apellido" class="border p-2 rounded w-full mb-4" required>
        <input type="password" name="password" placeholder="Nueva contraseña (dejar en blanco para no cambiar)" class="border p-2 rounded w-full mb-4">
        <button type="submit" class="btn btn-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Guardar Cambios
        </button>
    </form>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4">Mis Compras</h2>
        <table>
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['model_name']); ?></td>
                        <td>$<?php echo number_format($sale['total_price'] / $sale['quantity'], 2); ?></td>
                        <td><?php echo $sale['quantity']; ?></td>
                        <td>$<?php echo number_format($sale['total_price'], 2); ?></td>
                        <td><?php echo $sale['sale_date']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>