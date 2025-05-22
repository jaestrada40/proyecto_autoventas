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
?>

<!-- Hero Video con texto impactante -->
<div class="w-full">
    <div class="hero-video-container relative w-full h-[550px] md:h-[650px] overflow-hidden shadow-lg">
        <video autoplay muted loop playsinline class="w-full h-full object-cover">
            <source src="<?php echo BASE_URL; ?>images/hero.mp4" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
        <div class="absolute inset-0 bg-black bg-opacity-40 flex items-start justify-start px-8 md:px-16 lg:px-24 py-16 md:py-24 text-white">
            <div class="max-w-xl animate-logoFadeIn">
            <h1 class="text-6xl md:text-7xl lg:text-8xl font-extrabold leading-tight tracking-wide uppercase text-left drop-shadow-lg">
                    Tu <span class="text-orange-400">Nueva</span><br>
                    Aventura<br>
                    <span class="text-orange-400">Comienza Aquí</span>
                </h1>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="container mx-auto py-12" id="catalogo">

    <!-- Menú de marcas y nuevo slider -->
    <h1 class="text-5xl font-bold text-center text-1E40AF my-12">Bienvenido a Autos MONTGOMERY</h1>
    
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
                    <a href="<?php echo BASE_URL; ?>vehicle.php?vehicle_id=<?php echo $vehicle['id']; ?>">
                        <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>" class="w-full h-[200px] object-cover rounded-lg cursor-pointer transition-transform duration-300 hover:scale-105">
                    </a>
                    <div class="brand-slider-caption">
                        <h2 class="text-1.2rem font-semibold mt-2"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model_name']; ?></h2>
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
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const allVehicles = <?php echo json_encode($vehicles); ?>;
</script>
