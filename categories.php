<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Categorías de Vehículos</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($categories as $category): ?>
            <div class="card">
                <h2 class="text-xl font-semibold"><?php echo $category['name']; ?></h2>
                <p class="text-gray-600 mb-4">Explora nuestra selección de vehículos en la categoría <?php echo $category['name']; ?>.</p>
                <a href="search.php?category_id=<?php echo $category['id']; ?>" class="button inline-block">Ver Vehículos</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>