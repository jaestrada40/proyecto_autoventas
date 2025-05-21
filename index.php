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

<!-- Hero Video con botón de precalificación -->
<div class="w-full">
    <div class="hero-video-container relative w-full h-[500px] overflow-hidden shadow-lg">
        <video autoplay muted loop playsinline class="w-full h-full object-cover">
            <source src="<?php echo BASE_URL; ?>images/hero.mp4" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
        <div class="absolute inset-0 bg-black bg-opacity-40 flex flex-col items-center justify-center text-white text-center px-4">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 drop-shadow-lg">Autos MONTGOMERY</h1>
            <p class="text-xl mb-6 drop-shadow-md">Donde comienza tu próxima aventura</p>
            <button onclick="abrirPrecalificacion()" class="relative px-6 py-3 bg-white text-blue-700 font-bold rounded-full text-lg shadow-lg hover:bg-orange-500 hover:text-white transition">
                Precalificá tu crédito
                <span class="absolute inset-0 rounded-full border-4 border-orange-400 animate-ping-slow"></span>
            </button>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="container mx-auto py-12" id="catalogo">

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
                    <a href="<?php echo BASE_URL; ?>vehicle.php?vehicle_id=<?php echo $vehicle['id']; ?>">
                        <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>" class="w-full h-200px object-cover rounded-lg cursor-pointer transition-transform duration-300 hover:scale-105">
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

<!-- Modal de Precalificación -->
<div id="modalPrecalificacion" class="modal fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[9999] hidden">
  <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md relative">
    <button onclick="cerrarPrecalificacion()" class="absolute top-3 right-3 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
    <h2 class="text-2xl font-bold text-blue-800 mb-4">Precalificá para Crédito</h2>
    <form onsubmit="enviarPrecalificacion(event)">
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Nombre completo</label>
        <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Número de DPI</label>
        <input type="text" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Ingresos mensuales (Q)</label>
        <input type="number" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
      </div>
      <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Celular</label>
        <input type="tel" class="w-full border border-gray-300 rounded-md px-3 py-2" required>
      </div>
      <button type="submit" class="w-full bg-blue-700 text-white font-semibold py-2 rounded-md hover:bg-blue-800 transition">
        Enviar solicitud
      </button>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const allVehicles = <?php echo json_encode($vehicles); ?>;

    function abrirPrecalificacion() {
        document.getElementById("modalPrecalificacion").classList.remove("hidden");
    }

    function cerrarPrecalificacion() {
        document.getElementById("modalPrecalificacion").classList.add("hidden");
    }

    function enviarPrecalificacion(event) {
        event.preventDefault();
        cerrarPrecalificacion();
        Swal.fire({
            icon: 'success',
            title: '¡Gracias!',
            text: 'Tu solicitud de precalificación fue enviada con éxito.',
            confirmButtonColor: '#1e40af'
        });
    }
</script>
