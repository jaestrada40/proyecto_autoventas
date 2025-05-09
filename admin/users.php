<?php
// Iniciar la sesión al inicio del script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Manejar solicitudes POST antes de cualquier salida
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        header('Content-Type: application/json');

        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'client';
        $image = 'default-user.png'; // Valor predeterminado fijo

        // Solo procesar nueva imagen si se sube una válida
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $_FILES['image']['name'])) {
                $image = $_FILES['image']['name']; // Usar la nueva imagen solo si se sube correctamente
            }
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Usuario ya existe']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Correo electrónico ya existe']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, image, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $first_name, $last_name, $image, $role]);
            echo json_encode(['success' => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error al crear el usuario: ' . $e->getMessage()]);
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? 0;
        $email = $_POST['email'] ?? '';
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'client';
        $image = $_FILES['image']['name'] ?? '';

        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentUser) {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }

        if ($email !== $currentUser['email']) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'Correo electrónico ya existe']);
                exit;
            }
        }

        $passwordHash = $password ? password_hash($password, PASSWORD_BCRYPT) : $currentUser['password'];
        $newImage = $image && $_FILES['image']['name'] ? $image : $currentUser['image'];

        if ($newImage !== $currentUser['image'] && $_FILES['image']['name'] && isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])) {
            $uploadDir = '../images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newImage)) {
                echo json_encode(['success' => false, 'error' => 'Error al subir la imagen']);
                exit;
            }
        } else {
            $newImage = $currentUser['image'];
        }

        try {
            $stmt = $pdo->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, image = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([$email, $first_name, $last_name, $newImage, $passwordHash, $role, $id]);

            // Actualizar variables de sesión si el usuario editado es el actual
            if (isset($_SESSION['user_id']) && $id == $_SESSION['user_id']) {
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['image'] = $newImage;
                $_SESSION['role'] = $role;
            }

            echo json_encode(['success' => true]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
            exit;
        }
    } elseif (isset($_POST['search_query'])) {
        $search_query = $_POST['search_query'];
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?");
        $stmt->execute(["%$search_query%", "%$search_query%", "%$search_query%", "%$search_query%"]);
        $users = $stmt->fetchAll();
    }
}

// Incluir header después de manejar POST
include '../includes/header.php';

if (!isset($users)) {
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
}

include '../includes/admin_sidebar.php';
?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Gestión de Usuarios</h1>
    
    <form method="POST" class="mb-6 flex items-center gap-4">
        <input type="text" name="search_query" placeholder="Buscar por usuario, correo, nombre o apellido" class="border p-2 rounded w-full">
        <button type="submit" class="btn btn-search">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Buscar
        </button>
    </form>

    <button type="button" class="btn btn-add mb-8" id="open-add-user-modal">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Agregar Usuario
    </button>

    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4 text-1E40AF">Lista de Usuarios</h2>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Imagen</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($user['image']); ?>" alt="Usuario" class="w-12 h-12 object-cover rounded-full"></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <button class="btn btn-edit edit-user-btn"
                                    data-id="<?php echo $user['id']; ?>"
                                    data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                    data-first-name="<?php echo htmlspecialchars($user['first_name']); ?>"
                                    data-last-name="<?php echo htmlspecialchars($user['last_name']); ?>"
                                    data-image="<?php echo htmlspecialchars($user['image']); ?>"
                                    data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar este usuario?')" class="btn btn-delete">
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

<div id="add-user-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Agregar Nuevo Usuario</h2>
        <div id="add-user-error" class="text-red-600 mb-4 hidden"></div>
        <form id="add-user-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add">
            <input type="text" name="username" placeholder="Usuario" class="border p-2 rounded w-full" required>
            <input type="email" name="email" placeholder="Correo" class="border p-2 rounded w-full" required>
            <input type="text" name="first_name" placeholder="Nombre" class="border p-2 rounded w-full" required>
            <input type="text" name="last_name" placeholder="Apellido" class="border p-2 rounded w-full" required>
            <input type="password" name="password" placeholder="Contraseña" class="border p-2 rounded w-full" required>
            <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full">
            <select name="role" class="border p-2 rounded w-full" required>
                <option value="client">Cliente</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" class="btn btn-add">Agregar</button>
        </form>
    </div>
</div>

<div id="edit-user-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">×</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Editar Usuario</h2>
        <div id="edit-user-error" class="text-red-600 mb-4 hidden"></div>
        <form id="edit-user-form" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-user-id">
            <p><strong>Usuario:</strong> <span id="edit-user-username"></span></p>
            <input type="email" name="email" placeholder="Correo" class="border p-2 rounded w-full" required id="edit-user-email">
            <input type="text" name="first_name" placeholder="Nombre" class="border p-2 rounded w-full" required id="edit-user-first-name">
            <input type="text" name="last_name" placeholder="Apellido" class="border p-2 rounded w-full" required id="edit-user-last-name">
            <input type="password" name="password" placeholder="Nueva contraseña (dejar en blanco para no cambiar)" class="border p-2 rounded w-full" id="edit-user-password">
            <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full">
            <select name="role" class="border p-2 rounded w-full" required id="edit-user-role">
                <option value="client">Cliente</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" class="btn btn-edit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
function confirmDelete(message) {
    return confirm(message);
}

document.getElementById('open-add-user-modal').addEventListener('click', () => {
    document.getElementById('add-user-modal').style.display = 'flex';
});

document.querySelectorAll('#add-user-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('add-user-modal').style.display = 'none';
        document.getElementById('add-user-error').classList.add('hidden');
    });
});

document.getElementById('add-user-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('add-user-modal').style.display = 'none';
        document.getElementById('add-user-error').classList.add('hidden');
    }
});

document.getElementById('add-user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('add-user-error');

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        console.log('Respuesta del servidor (add):', text);
        let result = JSON.parse(text);

        if (result.success) {
            document.getElementById('add-user-modal').style.display = 'none';
            location.reload();
        } else {
            errorDiv.textContent = result.error || 'Error al agregar el usuario';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error en la solicitud (add):', error);
        errorDiv.textContent = 'Error en la solicitud: ' + error.message;
        errorDiv.classList.remove('hidden');
    }
});

document.querySelectorAll('.edit-user-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const username = button.getAttribute('data-username');
        const email = button.getAttribute('data-email');
        const firstName = button.getAttribute('data-first-name');
        const lastName = button.getAttribute('data-last-name');
        const image = button.getAttribute('data-image');
        const role = button.getAttribute('data-role');

        document.getElementById('edit-user-id').value = id;
        document.getElementById('edit-user-username').textContent = username;
        document.getElementById('edit-user-email').value = email;
        document.getElementById('edit-user-first-name').value = firstName;
        document.getElementById('edit-user-last-name').value = lastName;
        document.getElementById('edit-user-password').value = '';
        document.getElementById('edit-user-role').value = role;
        document.getElementById('edit-user-modal').style.display = 'flex';
    });
});

document.querySelectorAll('#edit-user-modal .close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('edit-user-modal').style.display = 'none';
        document.getElementById('edit-user-error').classList.add('hidden');
    });
});

document.getElementById('edit-user-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('edit-user-modal').style.display = 'none';
        document.getElementById('edit-user-error').classList.add('hidden');
    }
});

document.getElementById('edit-user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorDiv = document.getElementById('edit-user-error');

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData
        });

        const text = await response.text();
        console.log('Respuesta del servidor (edit):', text);

        let result;
        try {
            result = JSON.parse(text);
        } catch (parseError) {
            throw new Error('Respuesta no es JSON válido: ' + text);
        }

        if (result.success) {
            document.getElementById('edit-user-modal').style.display = 'none';
            location.reload();
        } else {
            errorDiv.textContent = result.error || 'Error al actualizar el usuario';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error en la solicitud (edit):', error);
        errorDiv.textContent = 'Error en la solicitud: ' + error.message;
        errorDiv.classList.remove('hidden');
    }
});
</script>

<?php include '../includes/footer.php'; ?>