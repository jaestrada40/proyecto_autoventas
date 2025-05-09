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
        $image = $_FILES['image']['name'] ?? 'default-vehicle.png';

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
                // Imagen subida exitosamente
            } else {
                $image = 'default-vehicle.png'; // Fallback si falla la subida
            }
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

        if ($image && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image)) {
                $image = null; // Si falla la subida, no cambiamos la imagen
            }
        }

        $stmt = $pdo->prepare("SELECT image FROM vehicles WHERE id = ?");
        $stmt->execute([$vehicle_id]);
        $current_image = $stmt->fetchColumn();
        $image = $image ?: $current_image;

        $stmt = $pdo->prepare("UPDATE vehicles SET model_id = ?, description = ?, price = ?, stock = ?, image = ?, is_featured = ? WHERE id = ?");
        $stmt->execute([$model_id, $description, $price, $stock, $image, $is_featured, $vehicle_id]);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Inicializar variables
$search_query = $_POST['search_query'] ?? '';
$brand_filter = $_POST['brand_filter'] ?? '';

// Obtener marcas y categorías
$brands = $pdo->query("SELECT * FROM brands")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$stmt = $pdo->query("SELECT m.*, b.name AS brand_name, c.name AS category_name FROM models m JOIN brands b ON m.brand_id = b.id JOIN categories c ON m.category_id = c.id");
$models = $stmt->fetchAll() ?: []; // Asegurar que sea un array vacío si no hay resultados
if (empty($models)) {
    error_log("No se encontraron modelos en la base de datos.");
}

// Manejo de búsqueda y filtrado
$where = [];
$params = [];

if ($search_query) {
    $where[] = "(m.name LIKE ? OR v.description LIKE ? OR v.price LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

if ($brand_filter) {
    $where[] = "b.id = ?";
    $params[] = $brand_filter;
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";
$query = "SELECT v.*, m.name AS model_name, b.name AS brand_name 
          FROM vehicles v 
          JOIN models m ON v.model_id = m.id 
          JOIN brands b ON m.brand_id = b.id 
          $where_clause";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehicles = $stmt->fetchAll() ?: []; // Inicializar como array vacío si la consulta falla

?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Gestión de Vehículos</h1>
    
    <!-- Formulario de búsqueda y filtrado -->
    <form method="POST" class="mb-6 flex items-center gap-4">
        <input type="text" name="search_query" placeholder="Buscar por modelo, descripción o precio" value="<?php echo htmlspecialchars($search_query); ?>" class="border p-2 rounded w-full">
        <select name="brand_filter" class="border p-2 rounded">
            <option value="">Todas las marcas</option>
            <?php foreach ($brands as $brand): ?>
                <option value="<?php echo $brand['id']; ?>" <?php echo $brand_filter == $brand['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($brand['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Buscar
        </button>
    </form>

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
                    <th>Marca</th>
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
                        <td><?php echo htmlspecialchars($vehicle['brand_name']); ?></td>
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
            <div class="mb-4">
                <label for="brand_id" class="block">Seleccionar Marca:</label>
                <select name="brand_id" id="brand_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar marca</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['id']; ?>">
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="category_id" class="block">Seleccionar Categoría:</label>
                <select name="category_id" id="category_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="model_id" class="block">Seleccionar Modelo:</label>
                <select name="model_id" id="model_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar modelo</option>
                    <?php if (!empty($models)): ?>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['id']; ?>" data-brand-id="<?php echo $model['brand_id']; ?>" data-category-id="<?php echo $model['category_id']; ?>">
                                <?php echo htmlspecialchars($model['brand_name'] . ' ' . $model['name'] . ' (' . $model['category_name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay modelos disponibles</option>
                    <?php endif; ?>
                </select>
            </div>
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
            <div class="mb-4">
                <label for="edit_brand_id" class="block">Seleccionar Marca:</label>
                <select name="brand_id" id="edit_brand_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar marca</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['id']; ?>">
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_category_id" class="block">Seleccionar Categoría:</label>
                <select name="category_id" id="edit_category_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar categoría</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="edit_model_id" class="block">Seleccionar Modelo:</label>
                <select name="model_id" id="edit_model_id" class="border p-2 rounded w-full" required>
                    <option value="">Seleccionar modelo</option>
                    <?php if (!empty($models)): ?>
                        <?php foreach ($models as $model): ?>
                            <option value="<?php echo $model['id']; ?>" data-brand-id="<?php echo $model['brand_id']; ?>" data-category-id="<?php echo $model['category_id']; ?>">
                                <?php echo htmlspecialchars($model['brand_name'] . ' ' . $model['name'] . ' (' . $model['category_name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No hay modelos disponibles</option>
                    <?php endif; ?>
                </select>
            </div>
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

// Filtrar modelos según la marca y categoría seleccionadas
function filterModels(brandSelectId, categorySelectId, modelSelectId) {
    const brandSelect = document.getElementById(brandSelectId);
    const categorySelect = document.getElementById(categorySelectId);
    const modelSelect = document.getElementById(modelSelectId);

    const updateModels = () => {
        const selectedBrandId = brandSelect.value;
        const selectedCategoryId = categorySelect.value;
        const options = modelSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') {
                option.style.display = '';
            } else {
                const brandId = option.getAttribute('data-brand-id');
                const categoryId = option.getAttribute('data-category-id');
                const matchesBrand = !selectedBrandId || brandId === selectedBrandId;
                const matchesCategory = !selectedCategoryId || categoryId === selectedCategoryId;
                option.style.display = matchesBrand && matchesCategory ? '' : 'none';
            }
        });

        // Resetear el modelo seleccionado si no coincide
        const selectedOption = modelSelect.querySelector(`option[value="${modelSelect.value}"]`);
        if (selectedOption && selectedOption.style.display === 'none') {
            modelSelect.value = '';
        }
    };

    brandSelect.addEventListener('change', updateModels);
    categorySelect.addEventListener('change', updateModels);
    updateModels(); // Ejecutar al cargar
}

// Manejar el modal para agregar vehículo
document.getElementById('open-add-modal').addEventListener('click', () => {
    document.getElementById('add-vehicle-modal').style.display = 'flex';
    document.getElementById('brand_id').value = '';
    document.getElementById('category_id').value = '';
    document.getElementById('model_id').value = '';
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
        document.getElementById('edit_model_id').value = modelId;
        document.getElementById('edit-vehicle-description').value = description;
        document.getElementById('edit-vehicle-price').value = price;
        document.getElementById('edit-vehicle-stock').value = stock;
        document.getElementById('edit-vehicle-featured').checked = isFeatured === '1';

        // Obtener la marca y categoría asociadas al modelo
        const brandId = <?php echo json_encode(array_column($models, 'brand_id', 'id')); ?>;
        const categoryId = <?php echo json_encode(array_column($models, 'category_id', 'id')); ?>;
        if (modelId && brandId[modelId]) {
            document.getElementById('edit_brand_id').value = brandId[modelId];
            document.getElementById('edit_category_id').value = categoryId[modelId];
        } else {
            document.getElementById('edit_brand_id').value = '';
            document.getElementById('edit_category_id').value = '';
        }

        // Filtrar modelos según la marca y categoría seleccionadas
        const options = document.getElementById('edit_model_id').querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '' || !brandId[modelId] || !categoryId[modelId]) {
                option.style.display = '';
            } else {
                const optionBrandId = option.getAttribute('data-brand-id');
                const optionCategoryId = option.getAttribute('data-category-id');
                option.style.display = (optionBrandId === brandId[modelId] && optionCategoryId === categoryId[modelId]) ? '' : 'none';
            }
        });

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

// Filtrar modelos según la marca y categoría seleccionadas en ambos modales
filterModels('brand_id', 'category_id', 'model_id');
filterModels('edit_brand_id', 'edit_category_id', 'edit_model_id');
</script>

<?php include '../includes/footer.php'; ?>