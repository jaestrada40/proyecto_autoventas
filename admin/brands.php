<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $_POST['name'];
        $stmt = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($_POST['action'] === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $stmt = $pdo->prepare("UPDATE brands SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$brands = $pdo->query("SELECT * FROM brands")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Gestión de Marcas</h1>
    
    <button type="button" class="btn btn-add mb-8" id="open-add-modal">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Agregar Marca
    </button>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4 text-1E40AF">Lista de Marcas</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($brand['name']); ?></td>
                        <td>
                            <button class="btn btn-edit edit-brand-btn" 
                                    data-id="<?php echo $brand['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($brand['name']); ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            <a href="delete_brand.php?id=<?php echo $brand['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar esta marca?')" class="btn btn-delete">
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

<!-- Modal para agregar marca -->
<div id="add-brand-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Agregar Nueva Marca</h2>
        <form id="add-brand-form" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <input type="text" name="name" placeholder="Nombre de la marca" class="border p-2 rounded w-full" required>
            <button type="submit" class="btn btn-add">Agregar</button>
        </form>
    </div>
</div>

<!-- Modal para editar marca -->
<div id="edit-brand-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Editar Marca</h2>
        <form id="edit-brand-form" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="edit-brand-id">
            <input type="text" name="name" id="edit-brand-name" placeholder="Nombre de la marca" class="border p-2 rounded w-full" required>
            <button type="submit" class="btn btn-edit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
// Función para confirmar eliminación
function confirmDelete(message) {
    return confirm(message);
}

// Manejar el modal para agregar marca
document.getElementById('open-add-modal').addEventListener('click', () => {
    document.getElementById('add-brand-modal').style.display = 'flex';
});

document.querySelectorAll('#add-brand-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('add-brand-modal').style.display = 'none';
    });
});

document.getElementById('add-brand-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('add-brand-modal').style.display = 'none';
    }
});

// Manejar el formulario de agregar marca
document.getElementById('add-brand-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('add-brand-modal').style.display = 'none';
        location.reload();
    }
});

// Manejar el modal para editar marca
document.querySelectorAll('.edit-brand-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        document.getElementById('edit-brand-id').value = id;
        document.getElementById('edit-brand-name').value = name;

        document.getElementById('edit-brand-modal').style.display = 'flex';
    });
});

document.querySelectorAll('#edit-brand-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('edit-brand-modal').style.display = 'none';
    });
});

document.getElementById('edit-brand-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('edit-brand-modal').style.display = 'none';
    }
});

// Manejar el formulario de editar marca
document.getElementById('edit-brand-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'edit');

    const response = await fetch(window.location.href, {
        method: 'POST',
        body: formData
    });
    if (response.ok) {
        document.getElementById('edit-brand-modal').style.display = 'none';
        location.reload();
    }
});
</script>

<?php include '../includes/footer.php'; ?>