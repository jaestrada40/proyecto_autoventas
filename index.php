<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$stmt = $pdo->query("SELECT v.*, m.name AS model_name, c.name AS category_name 
                     FROM vehicles v 
                     JOIN models m ON v.model_id = m.id 
                     JOIN categories c ON m.category_id = c.id");
$vehicles = $stmt->fetchAll();

$featured = $pdo->query("SELECT v.*, m.name AS model_name 
                         FROM vehicles v 
                         JOIN models m ON v.model_id = m.id 
                         WHERE v.is_featured = TRUE");
$featured_vehicles = $featured->fetchAll();
?>

<div class="container mx-auto py-12">
    <!-- Slider -->
    <?php if ($featured_vehicles): ?>
        <div class="slider-container">
            <div class="slider">
                <?php foreach ($featured_vehicles as $vehicle): ?>
                    <div class="slider-item">
                        <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>">
                        <div class="slider-caption">
                            <h2><?php echo $vehicle['model_name']; ?></h2>
                            <p>$<?php echo number_format($vehicle['price'], 2); ?> - <?php echo substr($vehicle['description'], 0, 100); ?>...</p>
                            <a href="<?php echo BASE_URL; ?>cart.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="button mt-2 inline-block">Comprar Ahora</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="slider-nav">
                <button class="prev">&#10094;</button>
                <button class="next">&#10095;</button>
            </div>
            <div class="slider-dots">
                <?php foreach ($featured_vehicles as $index => $vehicle): ?>
                    <span class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Búsqueda y Vehículos -->
    <h1 class="text-4xl font-bold text-center text-1E40AF my-12">Bienvenido a la Concesionaria</h1>
    <form action="<?php echo BASE_URL; ?>search.php" method="GET" class="mb-8 flex justify-center">
        <input type="text" name="query" placeholder="Buscar autos..." class="border p-3 rounded-l-lg w-full max-w-md focus:outline-none focus:border-F97316">
        <button type="submit" class="button rounded-r-lg">Buscar</button>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($vehicles as $vehicle): ?>
            <div class="card">
                <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>" class="w-full h-48 object-cover rounded">
                <h2 class="text-xl font-semibold mt-4 text-1E40AF"><?php echo $vehicle['model_name']; ?></h2>
                <p class="text-gray-600"><?php echo $vehicle['category_name']; ?></p>
                <p class="text-lg font-bold text-1E40AF">$<?php echo number_format($vehicle['price'], 2); ?></p>
                <p class="text-gray-600"><?php echo $vehicle['description']; ?></p>
                <a href="<?php echo BASE_URL; ?>cart.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="button mt-4 inline-block">Comprar</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>