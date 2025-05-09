<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $stmt = $pdo->prepare("INSERT INTO models (name, category_id) VALUES (?, ?)");
    $stmt->execute([$name, $category_id]);
    // Redirigir o recargar la página después de agregar (simulado con recarga)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$models = $pdo->query("SELECT m.*, c.name AS category_name FROM models m JOIN categories c ON m.category_id = c.id")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6">Gestión de Modelos</h1>
    
    <button type="button" class="btn btn-add mb-8" id="open-add-modal">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Agregar Modelo
    </button>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4">Lista de Modelos</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $model): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($model['name']); ?></td>
                        <td><?php echo htmlspecialchars($model['category_name']); ?></td>
                        <td>
                            <button class="btn btn-edit edit-model-btn" 
                                    data-id="<?php echo $model['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($model['name']); ?>" 
                                    data-category-id="<?php echo $model['category_id']; ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            <a href="delete_model.php?id=<?php echo $model['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar este modelo?')" class="btn btn-delete">
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

<!-- Modal para agregar modelo -->
<div id="add-model-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Agregar Nuevo Modelo</h2>
        <form id="add-model-form" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Nombre del modelo" class="border p-2 rounded w-full" required>
            <select name="category_id" class="border p-2 rounded w-full" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-add">Agregar</button>
        </form>
    </div>
</div>

<!-- Modal para editar modelo -->
<div id="edit-model-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Editar Modelo</h2>
        <form id="edit-model-form" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="edit-model-id">
            <input type="text" name="name" id="edit-model-name" placeholder="Nombre del modelo" class="border p-2 rounded w-full" required>
            <select name="category_id" id="edit-model-category" class="border p-2 rounded w-full" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-edit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
// Función para confirmar eliminación
function confirmDelete(message) {
    return confirm(message);
}

// Manejar el modal para agregar modelo
document.getElementById('open-add-modal').addEventListener('click', () => {
    document.getElementById('add-model-modal').style.display = 'flex';
});

document.querySelectorAll('#add-model-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('add-model-modal').style.display = 'none';
    });
});

document.getElementById('add-model-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('add-model-modal').style.display = 'none';
    }
});

// Manejar el formulario de agregar modelo
document.getElementById('add-model-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('add-model-modal').style.display = 'none';
        location.reload(); // Recargar la página para mostrar el nuevo modelo
    }
});

// Manejar el modal para editar modelo
document.querySelectorAll('.edit-model-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const categoryId = button.getAttribute('data-category-id');

        document.getElementById('edit-model-id').value = id;
        document.getElementById('edit-model-name').value = name;
        document.getElementById('edit-model-category').value = categoryId;

        document.getElementById('edit-model-modal').style.display = 'flex';
    });
});

document.querySelectorAll('#edit-model-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('edit-model-modal').style.display = 'none';
    });
});

document.getElementById('edit-model-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('edit-model-modal').style.display = 'none';
    }
});

// Manejar el formulario de editar modelo (simulación de edición)
document.getElementById('edit-model-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'edit'); // Indicador para diferenciar la acción

    const response = await fetch('edit_model.php', { // Ajusta la URL según tu lógica
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('edit-model-modal').style.display = 'none';
        location.reload(); // Recargar la página para mostrar los cambios
    }
});
</script>

<?php include '../includes/footer.php'; ?>