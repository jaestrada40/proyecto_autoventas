<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';
?>

<div class="container mx-auto py-12">
    <h1 class="text-4xl font-bold text-center text-1E40AF mb-8">Sobre Nosotros</h1>
    <div class="card max-w-3xl mx-auto">
        <h2 class="text-2xl font-semibold text-1E40AF mb-4">Concesionaria de Autos</h2>
        <p class="text-gray-600 mb-6 leading-relaxed">
            Somos una empresa líder en la venta de vehículos nuevos y usados, comprometidos con ofrecer la mejor calidad y servicio a nuestros clientes. 
            Con más de una década de experiencia, nuestro objetivo es ayudarte a encontrar el auto perfecto que se adapte a tu estilo de vida y necesidades.
        </p>
        <p class="text-gray-600 leading-relaxed">
            Nuestra misión es proporcionar una experiencia de compra transparente, confiable y personalizada. Contamos con un equipo de profesionales apasionados por los autos, listos para guiarte en cada paso del proceso.
        </p>
        <div class="mt-6 text-center">
            <a href="<?php echo BASE_URL; ?>contact.php" class="button inline-block">Contáctanos</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>