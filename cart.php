<?php
// Iniciar la sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Manejar la lógica de compra ANTES de cualquier salida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
    if (!isset($_SESSION['user_id'])) {
        // En lugar de redirigir, establecer una bandera para mostrar el modal
        $_SESSION['show_auth_modal'] = true;
        $_SESSION['error'] = 'Debes iniciar sesión o registrarte para finalizar la compra.';
    } else {
        // Obtener ítems del carrito desde la sesión
        $cart_items = [];
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, b.name AS brand_name FROM vehicles v JOIN models m ON v.model_id = m.id JOIN brands b ON m.brand_id = b.id WHERE v.id = ?");
                $stmt->execute([$item['vehicle_id']]);
                $vehicle = $stmt->fetch();
                if ($vehicle) {
                    $cart_items[] = array_merge($item, $vehicle);
                }
            }
        }

        $user_id = $_SESSION['user_id'];
        foreach ($cart_items as $item) {
            // Verificar stock nuevamente antes de la compra
            $stmt = $pdo->prepare("SELECT stock FROM vehicles WHERE id = ?");
            $stmt->execute([$item['vehicle_id']]);
            $vehicle = $stmt->fetch();
            if ($vehicle['stock'] < $item['quantity']) {
                $_SESSION['error'] = "No hay suficiente stock para el vehículo: " . $item['brand_name'] . ' ' . $item['model_name'];
                redirect(BASE_URL . 'cart.php');
            }

            // Descontar stock
            $stmt = $pdo->prepare("UPDATE vehicles SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['vehicle_id']]);

            // Registrar la compra (eliminamos la columna 'status')
            $stmt = $pdo->prepare("INSERT INTO sales (user_id, vehicle_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $item['vehicle_id'], $item['quantity'], $item['total_price'], date('Y-m-d H:i:s')]);
        }

        // Limpiar el carrito
        unset($_SESSION['cart']);
        $_SESSION['cart_count'] = 0;
        $_SESSION['success'] = 'Compra realizada con éxito.';
        redirect(BASE_URL . 'cart.php');
    }
}

// Ahora que las redirecciones están manejadas, podemos incluir el header y generar salida
include 'includes/header.php';

// Obtener ítems del carrito desde la sesión para mostrarlos
$cart_items = [];
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, b.name AS brand_name FROM vehicles v JOIN models m ON v.model_id = m.id JOIN brands b ON m.brand_id = b.id WHERE v.id = ?");
        $stmt->execute([$item['vehicle_id']]);
        $vehicle = $stmt->fetch();
        if ($vehicle) {
            $cart_items[] = array_merge($item, $vehicle);
        }
    }
}
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Carrito de Compras</h1>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="text-green-600 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="text-red-600 mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (empty($cart_items)): ?>
        <p class="text-gray-600">Tu carrito está vacío.</p>
    <?php else: ?>
        <table class="w-full mb-6">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Modelo</th>
                    <th>Cantidad</th>
                    <th>Precio Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><img src="<?php echo BASE_URL; ?>images/<?php echo $item['image']; ?>" alt="<?php echo $item['model_name']; ?>" class="w-16 h-16 object-cover rounded"></td>
                        <td><?php echo $item['brand_name'] . ' ' . $item['model_name']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form id="purchase-form" method="POST" action="">
            <button type="submit" name="purchase" class="button mt-4 inline-block">Finalizar Compra</button>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Mostrar el modal de autenticación si es necesario
document.addEventListener('DOMContentLoaded', () => {
    <?php if (isset($_SESSION['show_auth_modal']) && $_SESSION['show_auth_modal']): ?>
        document.getElementById('auth-modal').style.display = 'flex';
        document.querySelector('.tab-button[data-tab="login"]').click(); // Abrir la pestaña de login por defecto
        <?php unset($_SESSION['show_auth_modal']); ?>
    <?php endif; ?>
});
</script>