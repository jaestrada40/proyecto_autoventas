<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

// Obtener todas las marcas
$stmt_brands = $pdo->query("SELECT * FROM brands");
$brands = $stmt_brands->fetchAll();

// Obtener todos los vehículos con marcas y modelos
$stmt_vehicles = $pdo->query("SELECT v.*, m.name AS model_name, b.name AS brand_name, c.name AS category_name 
                             FROM vehicles v 
                             JOIN models m ON v.model_id = m.id 
                             JOIN brands b ON m.brand_id = b.id 
                             JOIN categories c ON m.category_id = c.id 
                             ORDER BY v.created_at DESC");
$vehicles = $stmt_vehicles->fetchAll();

// Obtener vehículos destacados (para el slider existente)
$featured = $pdo->query("SELECT v.*, m.name AS model_name 
                         FROM vehicles v 
                         JOIN models m ON v.model_id = m.id 
                         WHERE v.is_featured = TRUE");
$featured_vehicles = $featured->fetchAll();
?>

<div class="container mx-auto py-12">
    <!-- Slider Existente (Destacados) -->
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
                <button class="prev">❮</button>
                <button class="next">❯</button>
            </div>
            <div class="slider-dots">
                <?php foreach ($featured_vehicles as $index => $vehicle): ?>
                    <span class="slider-dot <?php echo $index === 0 ? 'active' : ''; ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Menú de marcas y nuevo slider -->
    <h1 class="text-4xl font-bold text-center text-1E40AF my-12">Bienvenido a Autos MONTGOMERY</h1>
    <div class="brand-menu text-center mb-8">
        <a href="#" class="brand-filter active" data-brand="">Todas las marcas</a>
        <?php foreach ($brands as $brand): ?>
            <a href="#" class="brand-filter" data-brand="<?php echo $brand['name']; ?>"><?php echo $brand['name']; ?></a>
        <?php endforeach; ?>
    </div>

    <div class="brand-slider-container">
        <div class="brand-slider">
            <?php foreach ($vehicles as $vehicle): ?>
                <div class="brand-slider-item">
                    <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>">
                    <div class="brand-slider-caption">
                        <h2><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model_name']; ?></h2>
                        <a href="<?php echo BASE_URL; ?>cart.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="button mt-2 inline-block">Comprar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="brand-slider-nav">
            <button class="brand-prev">❮</button>
            <button class="brand-next">❯</button>
        </div>
        <div class="brand-slider-dots">
            <?php for ($i = 0; $i < count($vehicles); $i += 4): ?>
                <span class="brand-slider-dot <?php echo $i === 0 ? 'active' : ''; ?>"></span>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    // Pasar BASE_URL y los vehículos como variables JavaScript
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const allVehicles = <?php echo json_encode($vehicles); ?>;
</script>
<script src="js/scripts.js"></script>