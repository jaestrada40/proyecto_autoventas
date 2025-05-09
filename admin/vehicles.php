<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect(BASE_URL . 'login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $model_id = $_POST['model_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $image = $_FILES['image']['name'];

        if ($image) {
            move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $image);
        }

        $stmt = $pdo->prepare("INSERT INTO vehicles (model_id, description, price, stock, image, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$model_id, $description, $price, $stock, $image, $is_featured]);
    } elseif ($_POST['action'] === 'edit') {
        $vehicle_id = $_POST['id'];
        $model_id = $_POST['model_id'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $image = $_FILES['image']['name'];

        if ($image) {
            move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $image);
        } else {
            $stmt = $pdo->prepare("SELECT image FROM vehicles WHERE id = ?");
            $stmt->execute([$vehicle_id]);
            $image = $stmt->fetchColumn();
        }

        $stmt = $pdo->prepare("UPDATE vehicles SET model_id = ?, description = ?, price = ?, stock = ?, image = ?, is_featured = ? WHERE id = ?");
        $stmt->execute([$model_id, $description, $price, $stock, $image, $is_featured, $vehicle_id]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$vehicles = $pdo->query("SELECT v.*, m.name AS model_name FROM vehicles v JOIN models m ON v.model_id = m.id")->fetchAll();
$models = $pdo->query("SELECT * FROM models")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Gestión de Vehículos</h1>
    
    <button type="button" class="btn btn-add mb-8" id="open-add-modal">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Agregar Vehículo
    </button>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4 text-1E40AF">Lista de Vehículos</h2>
        <table>
            <thead>
                <tr>
                    <th>Modelo</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Imagen</th>
                    <th>Destacado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['model_name']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['description']); ?></td>
                        <td>$<?php echo number_format($vehicle['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['stock']); ?></td>
                        <td><img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($vehicle['image']); ?>" alt="Vehículo" class="w-16 h-16 object-cover"></td>
                        <td><?php echo $vehicle['is_featured'] ? 'Sí' : 'No'; ?></td>
                        <td>
                            <button class="btn btn-edit edit-vehicle-btn" 
                                    data-id="<?php echo $vehicle['id']; ?>" 
                                    data-model-id="<?php echo $vehicle['model_id']; ?>" 
                                    data-model-name="<?php echo htmlspecialchars($vehicle['model_name']); ?>" 
                                    data-description="<?php echo htmlspecialchars($vehicle['description']); ?>" 
                                    data-price="<?php echo $vehicle['price']; ?>" 
                                    data-stock="<?php echo $vehicle['stock']; ?>" 
                                    data-image="<?php echo htmlspecialchars($vehicle['image']); ?>" 
                                    data-is-featured="<?php echo $vehicle['is_featured'] ? '1' : '0'; ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/delete_vehicle.php?id=<?php echo $vehicle['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar este vehículo?')" class="btn btn-delete">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1H9V4a1 1 0 011-1zm-5 4h12"></path>
                                </svg>
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para agregar vehículo -->
<div id="add-vehicle-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Agregar Nuevo Vehículo</h2>
        <form id="add-vehicle-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <select name="model_id" required class="border p-2 rounded w-full">
                <?php foreach ($models as $model): ?>
                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="description" placeholder="Descripción" class="border p-2 rounded w-full" required></textarea>
            <input type="number" name="price" placeholder="Precio" class="border p-2 rounded w-full" step="0.01" required>
            <input type="number" name="stock" placeholder="Stock" class="border p-2 rounded w-full" required>
            <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full">
            <label class="flex items-center">
                <input type="checkbox" name="is_featured" class="mr-2">
                <span>Mostrar en el slider de la página principal</span>
            </label>
            <button type="submit" class="btn btn-add">Agregar</button>
        </form>
    </div>
</div>

<!-- Modal para editar vehículo -->
<div id="edit-vehicle-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Editar Vehículo</h2>
        <form id="edit-vehicle-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-vehicle-id">
            <select name="model_id" required class="border p-2 rounded w-full" id="edit-vehicle-model">
                <?php foreach ($models as $model): ?>
                    <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="description" placeholder="Descripción" class="border p-2 rounded w-full" required id="edit-vehicle-description"></textarea>
            <input type="number" name="price" placeholder="Precio" class="border p-2 rounded w-full" step="0.01" required id="edit-vehicle-price">
            <input type="number" name="stock" placeholder="Stock" class="border p-2 rounded w-full" required id="edit-vehicle-stock">
            <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full">
            <label class="flex items-center">
                <input type="checkbox" name="is_featured" class="mr-2" id="edit-vehicle-featured">
                <span>Mostrar en el slider de la página principal</span>
            </label>
            <button type="submit" class="btn btn-edit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
// Función para confirmar eliminación
function confirmDelete(message) {
    return confirm(message);
}

// Manejar el modal para agregar vehículo
document.getElementById('open-add-modal').addEventListener('click', () => {
    document.getElementById('add-vehicle-modal').style.display = 'flex';
});

document.querySelectorAll('#add-vehicle-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('add-vehicle-modal').style.display = 'none';
    });
});

document.getElementById('add-vehicle-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('add-vehicle-modal').style.display = 'none';
    }
});

// Manejar el formulario de agregar vehículo
document.getElementById('add-vehicle-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('add-vehicle-modal').style.display = 'none';
        location.reload();
    } else {
        alert('Error al agregar el vehículo. Por favor, intenta de nuevo.');
    }
});

// Manejar el modal para editar vehículo
document.querySelectorAll('.edit-vehicle-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const modelId = button.getAttribute('data-model-id');
        const description = button.getAttribute('data-description');
        const price = button.getAttribute('data-price');
        const stock = button.getAttribute('data-stock');
        const image = button.getAttribute('data-image');
        const isFeatured = button.getAttribute('data-is-featured');

        document.getElementById('edit-vehicle-id').value = id;
        document.getElementById('edit-vehicle-model').value = modelId;
        document.getElementById('edit-vehicle-description').value = description;
        document.getElementById('edit-vehicle-price').value = price;
        document.getElementById('edit-vehicle-stock').value = stock;
        document.getElementById('edit-vehicle-featured').checked = isFeatured === '1';

        document.getElementById('edit-vehicle-modal').style.display = 'flex';
    });
});

document.querySelectorAll('#edit-vehicle-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('edit-vehicle-modal').style.display = 'none';
    });
});

document.getElementById('edit-vehicle-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('edit-vehicle-modal').style.display = 'none';
    }
});

// Manejar el formulario de editar vehículo
document.getElementById('edit-vehicle-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('edit-vehicle-modal').style.display = 'none';
        location.reload();
    } else {
        alert('Error al actualizar el vehículo. Por favor, intenta de nuevo.');
    }
});
</script>

<?php include '../includes/footer.php'; ?>