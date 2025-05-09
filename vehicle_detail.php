<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$vehicle_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, c.name AS category_name 
                       FROM vehicles v 
                       JOIN models m ON v.model_id = m.id 
                       JOIN categories c ON v.category_id = c.id 
                       WHERE v.id = ?");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    redirect(BASE_URL . 'index.php');
}
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6"><?php echo htmlspecialchars($vehicle['model_name']); ?></h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <p class="text-green-600 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <p class="text-red-600 mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    
    <div class="card flex gap-8">
        <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($vehicle['image']); ?>" alt="<?php echo htmlspecialchars($vehicle['model_name']); ?>" class="w-1/2 h-64 object-cover rounded">
        <div class="w-1/2">
            <p class="text-lg mb-2"><strong>Categoría:</strong> <?php echo htmlspecialchars($vehicle['category_name']); ?></p>
            <p class="text-lg mb-2"><strong>Precio:</strong> $<?php echo number_format($vehicle['price'], 2); ?></p>
            <p class="text-lg mb-4"><strong>Descripción:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
            
            <form method="POST" action="<?php echo BASE_URL; ?>add_to_cart.php" class="flex items-center gap-4">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                <label for="quantity" class="text-sm font-medium">Cantidad:</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" class="border p-2 rounded w-20">
                <button type="submit" class="btn btn-buy">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Añadir al Carrito
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>