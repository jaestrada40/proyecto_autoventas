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
                    <p><strong>Stock disponible:</strong> <?php echo $vehicle['stock']; ?></p>
                    <form id="add-to-cart-form-<?php echo $vehicle['id']; ?>" method="POST" action="<?php echo BASE_URL; ?>add_to_cart.php" class="mt-4">
                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                        <input type="number" name="quantity" id="quantity-input-<?php echo $vehicle['id']; ?>" min="1" max="<?php echo $vehicle['stock']; ?>" value="1" class="border p-2 rounded w-20 mr-2" <?php echo $vehicle['stock'] <= 0 ? 'disabled' : ''; ?> required>
                        <button type="submit" class="button mt-2 inline-block <?php echo $vehicle['stock'] <= 0 ? 'disabled' : ''; ?>" <?php echo $vehicle['stock'] <= 0 ? 'disabled' : ''; ?>>Añadir al Carrito</button>
                    </form>
                    <?php if ($vehicle['stock'] <= 0): ?>
                        <p class="text-red-600 mt-2">No hay stock disponible.</p>
                    <?php endif; ?>
                </div>
                <script>
                document.getElementById('add-to-cart-form-<?php echo $vehicle['id']; ?>').addEventListener('submit', (e) => {
                    e.preventDefault();
                    const quantityInput = document.getElementById('quantity-input-<?php echo $vehicle['id']; ?>');
                    const stock = <?php echo $vehicle['stock']; ?>;
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
                const quantityInput = document.getElementById('quantity-input-<?php echo $vehicle['id']; ?>');
                quantityInput.addEventListener('change', () => {
                    const stock = <?php echo $vehicle['stock']; ?>;
                    if (quantityInput.value > stock) {
                        quantityInput.value = stock;
                        alert('La cantidad no puede exceder el stock disponible (' + stock + ').');
                    } else if (quantityInput.value < 1) {
                        quantityInput.value = 1;
                    }
                });
                </script>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-600">No se encontraron vehículos.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>