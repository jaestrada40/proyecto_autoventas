<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$name]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6">Gestión de Categorías</h1>
    
    <button type="button" class="btn btn-add mb-8" id="open-add-modal">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Agregar Categoría
    </button>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4">Lista de Categorías</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <button class="btn btn-edit edit-category-btn" 
                                    data-id="<?php echo $category['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($category['name']); ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            <a href="delete_category.php?id=<?php echo $category['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar esta categoría?')" class="btn btn-delete">
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

<!-- Modal para agregar categoría -->
<div id="add-category-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Agregar Nueva Categoría</h2>
        <form id="add-category-form" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Nombre de la categoría" class="border p-2 rounded w-full" required>
            <button type="submit" class="btn btn-add">Agregar</button>
        </form>
    </div>
</div>

<!-- Modal para editar categoría -->
<div id="edit-category-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Editar Categoría</h2>
        <form id="edit-category-form" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="edit-category-id">
            <input type="text" name="name" id="edit-category-name" placeholder="Nombre de la categoría" class="border p-2 rounded w-full" required>
            <button type="submit" class="btn btn-edit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
// Función para confirmar eliminación
function confirmDelete(message) {
    return confirm(message);
}

// Manejar el modal para agregar categoría
document.getElementById('open-add-modal').addEventListener('click', () => {
    document.getElementById('add-category-modal').style.display = 'flex';
});

document.querySelectorAll('#add-category-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('add-category-modal').style.display = 'none';
    });
});

document.getElementById('add-category-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('add-category-modal').style.display = 'none';
    }
});

// Manejar el formulario de agregar categoría
document.getElementById('add-category-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('add-category-modal').style.display = 'none';
        location.reload();
    }
});

// Manejar el modal para editar categoría
document.querySelectorAll('.edit-category-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        document.getElementById('edit-category-id').value = id;
        document.getElementById('edit-category-name').value = name;

        document.getElementById('edit-category-modal').style.display = 'flex';
    });
});

document.querySelectorAll('#edit-category-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('edit-category-modal').style.display = 'none';
    });
});

document.getElementById('edit-category-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('edit-category-modal').style.display = 'none';
    }
});

// Manejar el formulario de editar categoría
document.getElementById('edit-category-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'edit');

    const response = await fetch('edit_category.php', {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('edit-category-modal').style.display = 'none';
        location.reload();
    }
});
</script>

<?php include '../includes/footer.php'; ?>