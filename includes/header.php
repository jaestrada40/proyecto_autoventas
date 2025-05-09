<?php
// Iniciar la sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concesionaria de Autos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        <div class="navbar-actions flex items-center gap-4">
            <div class="navbar-cart relative">
                <a href="<?php echo BASE_URL; ?>cart.php" id="cart-icon">
                    <i class="fas fa-shopping-cart text-white text-xl hover:text-yellow-500 transition-colors duration-300"></i>
                </a>
                <span class="cart-count absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" id="cart-count"><?php echo isset($_SESSION['cart_count']) ? $_SESSION['cart_count'] : 0; ?></span>
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
                        <a href="#" id="open-auth-modal" data-tab="login">Iniciar Sesión</a>
                        <a href="#" id="open-auth-modal-register" data-tab="register">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modal combinado para autenticación (login y registro) -->
    <div id="auth-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <!-- Pestañas -->
            <div class="flex border-b mb-4">
                <button class="tab-button flex-1 py-2 text-center font-semibold text-1E40AF border-b-2 border-transparent hover:border-1E40AF focus:outline-none active-tab" data-tab="login">Iniciar Sesión</button>
                <button class="tab-button flex-1 py-2 text-center font-semibold text-1E40AF border-b-2 border-transparent hover:border-1E40AF focus:outline-none" data-tab="register">Registrarse</button>
            </div>

            <!-- Contenido de las pestañas -->
            <!-- Iniciar Sesión -->
            <div id="login-tab" class="tab-content">
                <h2 class="text-2xl font-bold mb-4 text-1E40AF">Iniciar Sesión</h2>
                <div id="login-error" class="text-red-600 mb-4 hidden"></div>
                <form id="login-form" method="POST" class="space-y-4">
                    <input type="text" name="username" placeholder="Usuario" class="border p-2 rounded w-full" required>
                    <input type="password" name="password" placeholder="Contraseña" class="border p-2 rounded w-full" required>
                    <button type="submit" class="btn btn-add">Iniciar Sesión</button>
                </form>
            </div>

            <!-- Registrarse -->
            <div id="register-tab" class="tab-content hidden">
                <h2 class="text-2xl font-bold mb-4 text-1E40AF">Registrarse</h2>
                <div id="register-error" class="text-red-600 mb-4 hidden"></div>
                <form id="register-form" method="POST" class="space-y-4">
                    <input type="text" name="username" placeholder="Usuario" class="border p-2 rounded w-full" required>
                    <input type="email" name="email" placeholder="Correo" class="border p-2 rounded w-full" required>
                    <input type="text" name="first_name" placeholder="Nombre" class="border p-2 rounded w-full" required>
                    <input type="text" name="last_name" placeholder="Apellido" class="border p-2 rounded w-full" required>
                    <input type="password" name="password" placeholder="Contraseña" class="border p-2 rounded w-full" required>
                    <button type="submit" class="btn btn-add">Registrarse</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Manejar el modal de autenticación
    document.querySelectorAll('[id^="open-auth-modal"]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = button.getAttribute('data-tab');
            document.getElementById('auth-modal').style.display = 'flex';
            // Simular clic en la pestaña correspondiente
            document.querySelector(`.tab-button[data-tab="${tab}"]`).click();
        });
    });

    // Manejar el cierre del modal
    document.querySelectorAll('#auth-modal .close-modal').forEach(close => {
        close.addEventListener('click', () => {
            document.getElementById('auth-modal').style.display = 'none';
            document.getElementById('login-error').classList.add('hidden');
            document.getElementById('register-error').classList.add('hidden');
        });
    });

    document.getElementById('auth-modal').addEventListener('click', (e) => {
        if (e.target === e.currentTarget) {
            document.getElementById('auth-modal').style.display = 'none';
            document.getElementById('login-error').classList.add('hidden');
            document.getElementById('register-error').classList.add('hidden');
        }
    });

    // Manejar las pestañas
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            // Actualizar las pestañas
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active-tab');
                btn.classList.add('border-transparent');
            });
            button.classList.add('active-tab');
            button.classList.remove('border-transparent');
            button.classList.add('border-1E40AF');

            // Mostrar el contenido correspondiente
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(`${button.getAttribute('data-tab')}-tab`).classList.remove('hidden');
        });
    });

    // Manejar el formulario de inicio de sesión
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const errorDiv = document.getElementById('login-error');

        try {
            const response = await fetch('<?php echo BASE_URL; ?>login.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            console.log('Respuesta del servidor (login):', text);

            let result;
            try {
                result = JSON.parse(text);
            } catch (parseError) {
                throw new Error('Respuesta no es JSON válido: ' + text);
            }

            if (result.success) {
                document.getElementById('auth-modal').style.display = 'none';
                location.reload(); // Recargar la página para actualizar el estado de la sesión
            } else {
                errorDiv.textContent = result.error || 'Credenciales incorrectas';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error en la solicitud (login):', error);
            errorDiv.textContent = 'Error en la solicitud: ' + error.message;
            errorDiv.classList.remove('hidden');
        }
    });

    // Manejar el formulario de registro
    document.getElementById('register-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const errorDiv = document.getElementById('register-error');

        try {
            const response = await fetch('<?php echo BASE_URL; ?>register.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            console.log('Respuesta del servidor (register):', text);

            let result;
            try {
                result = JSON.parse(text);
            } catch (parseError) {
                throw new Error('Respuesta no es JSON válido: ' + text);
            }

            if (result.success) {
                document.getElementById('auth-modal').style.display = 'none';
                document.querySelector('.tab-button[data-tab="login"]').click(); // Cambiar a la pestaña de login
                document.getElementById('auth-modal').style.display = 'flex'; // Mostrar el modal de nuevo
            } else {
                errorDiv.textContent = result.error || 'Error al registrarse';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error en la solicitud (register):', error);
            errorDiv.textContent = 'Error en la solicitud: ' + error.message;
            errorDiv.classList.remove('hidden');
        }
    });
    </script>