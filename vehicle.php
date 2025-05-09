<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
$stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, b.name AS brand_name, c.name AS category_name 
                       FROM vehicles v 
                       JOIN models m ON v.model_id = m.id 
                       JOIN brands b ON m.brand_id = b.id 
                       JOIN categories c ON m.category_id = c.id 
                       WHERE v.id = ?");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    echo "Vehículo no encontrado.";
    include 'includes/footer.php';
    exit;
}

// Verificar stock
$stock = $vehicle['stock'] ?? 0;
$disabled = $stock <= 0 ? 'disabled' : '';
?>

<div class="container mx-auto py-12">
    <h1 class="text-3xl font-bold mb-6"><?php echo $vehicle['brand_name'] . ' ' . $vehicle['model_name']; ?></h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <img src="<?php echo BASE_URL; ?>images/<?php echo $vehicle['image']; ?>" alt="<?php echo $vehicle['model_name']; ?>" class="w-full h-auto rounded-lg">
        <div>
            <p><strong>Precio:</strong> $<?php echo number_format($vehicle['price'], 2); ?></p>
            <p><strong>Categoría:</strong> <?php echo $vehicle['category_name']; ?></p>
            <p><strong>Descripción:</strong> <?php echo $vehicle['description']; ?></p>
            <p><strong>Stock disponible:</strong> <?php echo $stock; ?></p>
            <form id="add-to-cart-form" method="POST" action="<?php echo BASE_URL; ?>add_to_cart.php" class="mt-4">
                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                <input type="number" name="quantity" id="quantity-input" min="1" max="<?php echo $stock; ?>" value="1" class="border p-2 rounded w-20 mr-2" <?php echo $disabled; ?> required>
                <button type="submit" class="button mt-2 inline-block <?php echo $disabled; ?>" <?php echo $disabled; ?>>Añadir al Carrito</button>
            </form>
            <?php if ($stock <= 0): ?>
                <p class="text-red-600 mt-2">No hay stock disponible.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('add-to-cart-form').addEventListener('submit', (e) => {
    e.preventDefault();
    const quantityInput = document.getElementById('quantity-input');
    const stock = <?php echo $stock; ?>;
    const quantity = parseInt(quantityInput.value);

    if (quantity > stock) {
        alert('La cantidad seleccionada excede el stock disponible (' + stock + ').');
        return;
    }

    const formData = new FormData(e.target);
    fetch(e.target.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const cartCount = document.getElementById('cart-count');
            cartCount.textContent = parseInt(cartCount.textContent) + quantity;
        } else {
            alert(result.error || 'Error al añadir al carrito.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar la solicitud.');
    });
});

// Limitar el input al stock disponible
const quantityInput = document.getElementById('quantity-input');
quantityInput.addEventListener('change', () => {
    const stock = <?php echo $stock; ?>;
    if (quantityInput.value > stock) {
        quantityInput.value = stock;
        alert('La cantidad no puede exceder el stock disponible (' + stock + ').');
    } else if (quantityInput.value < 1) {
        quantityInput.value = 1;
    }
});
</script>