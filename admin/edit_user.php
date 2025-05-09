<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect(BASE_URL . 'login.php');
}

if (!isset($_GET['id'])) {
    redirect(BASE_URL . 'admin/users.php');
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect(BASE_URL . 'admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : $user['password'];
    $image = $_FILES['image']['name'] ?: $user['image'];

    if ($image !== $user['image'] && $_FILES['image']['name']) {
        move_uploaded_file($_FILES['image']['tmp_name'], '../images/' . $image);
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, image = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $first_name, $last_name, $image, $role, $password, $user_id]);

        // Actualizar datos de la sesión si el usuario editado es el actual
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['image'] = $image;
            $_SESSION['role'] = $role;
        }

        redirect(BASE_URL . 'admin/users.php');
    } catch (PDOException $e) {
        $error = "Error al actualizar el usuario: " . $e->getMessage();
    }
}
?>



<div class="main-content">
    <h1 class="text-3xl font-bold mb-6 text-1E40AF">Editar Usuario</h1>
    
    <?php if (isset($error)): ?>
        <p class="text-red-600 mb-4"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="card max-w-2xl">
        <h2 class="text-xl font-semibold mb-4 text-1E40AF">Editar <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <input type="text" name="username" placeholder="Usuario" value="<?php echo htmlspecialchars($user['username']); ?>" class="border p-2 rounded w-full mb-4" required>
        <input type="email" name="email" placeholder="Correo" value="<?php echo htmlspecialchars($user['email']); ?>" class="border p-2 rounded w-full mb-4" required>
        <input type="text" name="first_name" placeholder="Nombre" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="border p-2 rounded w-full mb-4" required>
        <input type="text" name="last_name" placeholder="Apellido" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="border p-2 rounded w-full mb-4" required>
        <input type="password" name="password" placeholder="Nueva contraseña (dejar en blanco para no cambiar)" class="border p-2 rounded w-full mb-4">
        <div class="mb-4">
            <label class="block text-gray-600 mb-2">Imagen Actual:</label>
            <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($user['image']); ?>" alt="Usuario" class="w-24 h-24 object-cover rounded-full mb-2">
            <input type="file" name="image" accept="image/*" class="border p-2 rounded w-full">
        </div>
        <select name="role" class="border p-2 rounded w-full mb-4" required>
            <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Cliente</option>
            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
        </select>
        <button type="submit" class="btn btn-add">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Guardar Cambios
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>