<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || !isAdmin()) {
    redirect('../login.php');
}

$messages = $pdo->query("SELECT m.*, u.username FROM messages m LEFT JOIN users u ON m.user_id = u.id ORDER BY m.created_at DESC")->fetchAll();
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="main-content">
    <h1 class="text-3xl font-bold mb-6">Gestión de Mensajes</h1>
    
    <div class="table-container">
        <h2 class="text-xl font-semibold mb-4">Lista de Mensajes</h2>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Asunto</th>
                    <th>Mensaje</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($message['username'] ?: 'Anónimo'); ?></td>
                        <td><?php echo htmlspecialchars($message['subject']); ?></td>
                        <td><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                        <td>
                            <button class="btn btn-edit view-message-btn" 
                                    data-id="<?php echo $message['id']; ?>" 
                                    data-username="<?php echo htmlspecialchars($message['username'] ?: 'Anónimo'); ?>" 
                                    data-subject="<?php echo htmlspecialchars($message['subject']); ?>" 
                                    data-message="<?php echo htmlspecialchars($message['message']); ?>" 
                                    data-created-at="<?php echo htmlspecialchars($message['created_at']); ?>">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Ver
                            </button>
                            <a href="delete_message.php?id=<?php echo $message['id']; ?>" onclick="return confirmDelete('¿Seguro que quieres eliminar este mensaje?')" class="btn btn-delete">
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

<!-- Modal para ver el mensaje -->
<div id="view-message-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 class="text-2xl font-bold mb-4 text-1E40AF">Detalles del Mensaje</h2>
        <div class="message-details">
            <p><strong>Usuario:</strong> <span id="message-username"></span></p>
            <p><strong>Asunto:</strong> <span id="message-subject"></span></p>
            <p><strong>Mensaje:</strong> <span id="message-content"></span></p>
            <p><strong>Fecha:</strong> <span id="message-date"></span></p>
        </div>
    </div>
</div>

<script>
// Función para confirmar eliminación
function confirmDelete(message) {
    return confirm(message);
}

// Manejar el modal para ver mensajes
document.querySelectorAll('.view-message-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Obtener datos del mensaje desde los atributos data
        const username = button.getAttribute('data-username');
        const subject = button.getAttribute('data-subject');
        const message = button.getAttribute('data-message');
        const createdAt = button.getAttribute('data-created-at');

        // Llenar el modal con los datos
        document.getElementById('message-username').textContent = username;
        document.getElementById('message-subject').textContent = subject;
        document.getElementById('message-content').textContent = message;
        document.getElementById('message-date').textContent = createdAt;

        // Mostrar el modal
        document.getElementById('view-message-modal').style.display = 'flex';
    });
});

// Cerrar el modal al hacer clic en la "X"
document.querySelectorAll('.close-modal').forEach(close => {
    close.addEventListener('click', () => {
        document.getElementById('view-message-modal').style.display = 'none';
    });
});

// Cerrar el modal al hacer clic fuera del contenido
document.getElementById('view-message-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) {
        document.getElementById('view-message-modal').style.display = 'none';
    }
});
</script>

<?php include '../includes/footer.php'; ?>