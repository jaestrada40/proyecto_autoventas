<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';

$sql = "SELECT v.*, m.name AS model_name, c.name AS category_name 
        FROM vehicles v 
        JOIN models m ON v.model_id = m.id 
        JOIN categories c ON m.category_id = c.id 
        WHERE 1=1";

$params = [];
if ($query) {
    $sql .= " AND (m.name LIKE ? OR c.name LIKE ? OR v.description LIKE ?)";
    $params = ["%$query%", "%$query%", "%$query%"];
}
if ($category_id) {
    $sql .= " AND c.id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehicles = $stmt->fetchAll();
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Resultados de Búsqueda</h1>
    <form action="search.php" method="GET" class="mb-6">
        <input type="text" name="query" value="<?php echo htmlspecialchars($query); ?>" placeholder="Buscar autos..." class="border p-2 rounded w-full md:w-1/2">
        <button type="submit">Buscar</button>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php if ($vehicles): ?>
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="card">
                    <img src="images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>" class="w-full h-48 object-cover rounded">
                    <h2 class="text-xl font-semibold mt-2"><?php echo $vehicle['model_name']; ?></h2>
                    <p class="text-gray-600"><?php echo $vehicle['category_name']; ?></p>
                    <p class="text-lg font-bold text-blue-900">$<?php echo number_format($vehicle['price'], 2); ?></p>
                    <p><?php echo $vehicle['description']; ?></p>
                    <a href="cart.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="button mt-4 inline-block">Comprar</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-600">No se encontraron vehículos.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>