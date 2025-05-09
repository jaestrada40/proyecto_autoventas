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

// Paginación
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Contar el total de ventas para paginación
$stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_sales = $stmt->fetchColumn();
$total_pages = ceil($total_sales / $items_per_page);

// Consulta de ventas con paginación
$query = "SELECT s.*, v.*, m.name AS model_name, b.name AS brand_name 
          FROM sales s 
          JOIN vehicles v ON s.vehicle_id = v.id 
          JOIN models m ON v.model_id = m.id 
          JOIN brands b ON m.brand_id = b.id 
          WHERE s.user_id = ? 
          LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($query);
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
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
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No tienes compras registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['brand_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['model_name']); ?></td>
                            <td>$<?php echo number_format($sale['total_price'] / $sale['quantity'], 2); ?></td>
                            <td><?php echo $sale['quantity']; ?></td>
                            <td>$<?php echo number_format($sale['total_price'], 2); ?></td>
                            <td><?php echo $sale['sale_date']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination mt-4 flex justify-center gap-2">
                <?php if ($page > 1): ?>
                    <a href="profile.php?page=<?php echo $page - 1; ?>" class="btn btn-search">Anterior</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="profile.php?page=<?php echo $i; ?>" class="btn <?php echo $i === $page ? 'btn-add' : 'btn-search'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="profile.php?page=<?php echo $page + 1; ?>" class="btn btn-search">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>