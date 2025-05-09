<?php
session_start();
require_once 'functions.php';

// Obtener datos del usuario para la barra lateral
if (isset($_SESSION['user_id'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT first_name, last_name, image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    $_SESSION['first_name'] = $user['first_name'] ?? '';
    $_SESSION['last_name'] = $user['last_name'] ?? '';
    $_SESSION['image'] = $user['image'] ?? 'default-user.png';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionaria de Autos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/styles.css">
    <script src="<?php echo BASE_URL; ?>js/scripts.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">
            <img src="<?php echo BASE_URL; ?>images/logo.png" alt="Logo">
        </div>
        <div class="navbar-menu">
            <a href="<?php echo BASE_URL; ?>index.php">Inicio</a>
            <a href="<?php echo BASE_URL; ?>categories.php">Categorías</a>
            <a href="<?php echo BASE_URL; ?>contact.php">Contacto</a>
            <a href="<?php echo BASE_URL; ?>about.php">Sobre Nosotros</a>
        </div>
        <div class="navbar-user">
            <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($_SESSION['image'] ?? 'default-user.png'); ?>" alt="Usuario" class="w-9 h-9 rounded-full user-toggle">
            <div class="dropdown-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>profile.php">Perfil</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo BASE_URL; ?>admin/dashboard.php">Panel de Administración</a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>logout.php">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="#" id="open-login-modal">Iniciar Sesión</a>
                    <a href="#" id="open-register-modal">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Modal para iniciar sesión -->
    <div id="login-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h2 class="text-2xl font-bold mb-4 text-1E40AF">Iniciar Sesión</h2>
            <div id="login-error" class="text-red-600 mb-4 hidden"></div>
            <form id="login-form" method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="Usuario" class="border p-2 rounded w-full" required>
                <input type="password" name="password" placeholder="Contraseña" class="border p-2 rounded w-full" required>
                <button type="submit" class="btn btn-add">Iniciar Sesión</button>
            </form>
        </div>
    </div>

    <!-- Modal para registrarse -->
    <div id="register-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h2 class="text-2xl font-bold mb-4 text-1E40AF">Registrarse</h2>
            <div id="register-error" class="text-red-600 mb-4 hidden"></div>
            <form id="register-form" method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="Usuario" class="border p-2 rounded w-full" required>
                <input type="email" name="email" placeholder="Correo" class="border p-2 rounded w-full" required>
                <input type="text" name="name" placeholder="Nombre" class="border p-2 rounded w-full" required>
                <input type="password" name="password" placeholder="Contraseña" class="border p-2 rounded w-full" required>
                <button type="submit" class="btn btn-add">Registrarse</button>
            </form>
        </div>
    </div>

    <script>
    // Manejar el modal para iniciar sesión
    document.getElementById('open-login-modal').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('login-modal').style.display = 'flex';
    });

    document.querySelectorAll('#login-modal .close-modal').forEach(close => {
        close.addEventListener('click', () => {
            document.getElementById('login-modal').style.display = 'none';
            document.getElementById('login-error').classList.add('hidden');
        });
    });

    document.getElementById('login-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            document.getElementById('login-modal').style.display = 'none';
            document.getElementById('login-error').classList.add('hidden');
        }
    });

    // Manejar el formulario de inicio de sesión
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const errorDiv = document.getElementById('login-error');

        const response = await fetch('<?php echo BASE_URL; ?>login.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            document.getElementById('login-modal').style.display = 'none';
            location.reload(); // Recargar la página para actualizar el estado de la sesión
        } else {
            errorDiv.textContent = result.error || 'Credenciales incorrectas';
            errorDiv.classList.remove('hidden');
        }
    });

    // Manejar el modal para registrarse
    document.getElementById('open-register-modal').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('register-modal').style.display = 'flex';
    });

    document.querySelectorAll('#register-modal .close-modal').forEach(close => {
        close.addEventListener('click', () => {
            document.getElementById('register-modal').style.display = 'none';
            document.getElementById('register-error').classList.add('hidden');
        });
    });

    document.getElementById('register-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            document.getElementById('register-modal').style.display = 'none';
            document.getElementById('register-error').classList.add('hidden');
        }
    });

    // Manejar el formulario de registro
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const errorDiv = document.getElementById('register-error');

        const response = await fetch('<?php echo BASE_URL; ?>register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            document.getElementById('register-modal').style.display = 'none';
            document.getElementById('login-modal').style.display = 'flex'; // Abrir modal de inicio de sesión
        } else {
            errorDiv.textContent = result.error || 'Error al registrarse';
            errorDiv.classList.remove('hidden');
        }
    });
    </script>