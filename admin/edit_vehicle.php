<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect(BASE_URL . 'login.php');
}

if (!isset($_GET['id'])) {
    redirect(BASE_URL . 'admin/vehicles.php');
}

$vehicle_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT v.*, m.name AS model_name FROM vehicles v JOIN models m ON v.model_id = m.id WHERE v.id = ?");
$stmt->execute([$vehicle_id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    redirect(BASE_URL . 'admin/vehicles.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model_id = $_POST['model_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $image = $_FILES['image']['name'];

    if ($image) {
        move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $image);
    } else {
        $image = $vehicle['image'];
    }

    $stmt = $pdo->prepare("UPDATE vehicles SET model_id = ?, description = ?, price = ?, stock = ?, image = ?, is_featured = ? WHERE id = ?");
    $stmt->execute([$model_id, $description, $price, $stock, $image, $is_featured, $vehicle_id]);
    redirect(BASE_URL . 'admin/vehicles.php');
}

$models = $pdo->query("SELECT * FROM models")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Editar Vehículo</h1>
    
    <form method="POST" enctype="multipart/form-data" class="card max-w-2xl">
        <h2 class="text-xl font-semibold mb-4 text-1E40AF">Editar <?php echo $vehicle['model_name']; ?></h2>
        <select name="model_id" required class="border p-2 rounded w-full mb-4">
            <?php foreach ($models as $model): ?>
                <option value="<?php echo $model['id']; ?>" <?php echo $model['id'] == $vehicle['model_id'] ? 'selected' : ''; ?>>
                    <?php echo $model['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <textarea name="description" placeholder="Descripción" class="border p-2 rounded w-full mb-4" required><?php echo $vehicle['description']; ?></textarea>
        <input type="number" name="price" placeholder="Precio" value="<?php echo $vehicle['price']; ?>" class="border p-2 rounded w-full mb-4" step="0.01" required>
        <input type="number" name="stock" placeholder="Stock" value="<?php echo $vehicle['stock']; ?>" class="border p-2 rounded w-full mb-4" required>
        <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full mb-4">
        <label class="flex items-center mb-4">
            <input type="checkbox" name="is_featured" class="mr-2" <?php echo $vehicle['is_featured'] ? 'checked' : ''; ?>>
            <span>Mostrar en el slider de la página principal</span>
        </label>
        <button type="submit" class="btn btn-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Agregar
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>