<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

// Estadísticas
$total_sales = $pdo->query("SELECT SUM(total_price) AS total FROM sales")->fetch()['total'];
$total_users = $pdo->query("SELECT COUNT(*) AS count FROM users")->fetch()['count'];
$total_vehicles = $pdo->query("SELECT COUNT(*) AS count FROM vehicles")->fetch()['count'];

$top_vehicles = $pdo->query("SELECT v.*, m.name AS model_name, COUNT(s.id) AS sales_count 
                             FROM vehicles v 
                             JOIN models m ON v.model_id = m.id 
                             LEFT JOIN sales s ON v.id = s.vehicle_id 
                             GROUP BY v.id 
                             ORDER BY sales_count DESC 
                             LIMIT 5")->fetchAll();

$today_sales = $pdo->query("SELECT * FROM sales WHERE DATE(sale_date) = CURDATE()")->fetchAll();
$week_sales = $pdo->query("SELECT * FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchAll();
$month_sales = $pdo->query("SELECT * FROM sales WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <h2 class="text-xl font-semibold">Ventas Totales</h2>
            <p class="text-2xl font-bold text-blue-900">$<?php echo number_format($total_sales, 2); ?></p>
        </div>
        <div class="card">
            <h2 class="text-xl font-semibold">Clientes</h2>
            <p class="text-2xl font-bold text-blue-900"><?php echo $total_users; ?></p>
        </div>
        <div class="card">
            <h2 class="text-xl font-semibold">Vehículos</h2>
            <p class="text-2xl font-bold text-blue-900"><?php echo $total_vehicles; ?></p>
        </div>
    </div>

    <div class="table-container mb-8">
        <h2 class="text-xl font-semibold mb-4">Vehículos Más Vendidos</h2>
        <table>
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Ventas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo $vehicle['model_name']; ?></td>
                        <td><?php echo $vehicle['sales_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container mb-8">
        <h2 class="text-xl font-semibold mb-4">Ventas de Hoy</h2>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($today_sales as $sale): ?>
                    <tr>
                        <td><?php echo $sale['user_id']; ?></td>
                        <td><?php echo $sale['vehicle_id']; ?></td>
                        <td>$<?php echo number_format($sale['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="table-container mb-8">
        <h2 class="text-xl font-semibold mb-4">Ventas de la Semana</h2>
        <!-- Similar a ventas de hoy -->
    </div>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4">Ventas del Mes</h2>
        <!-- Similar a ventas de hoy -->
    </div>
</div>

<?php include '../includes/footer.php'; ?>