<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $stmt = $pdo->prepare("INSERT INTO messages (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $subject, $message]);
    $success = "Mensaje enviado con éxito.";
}
?>

<div class="container mx-auto py-8">
    <h1 class="text-3xl font-bold mb-6">Contáctanos</h1>
    <?php if (isset($success)): ?>
        <p class="text-green-600 mb-4"><?php echo $success; ?></p>
    <?php endif; ?>
    <form method="POST" class="card max-w-md mx-auto">
        <input type="text" name="subject" placeholder="Asunto" class="border p-2 rounded w-full mb-4" required>
        <textarea name="message" placeholder="Mensaje" class="border p-2 rounded w-full mb-4" rows="5" required></textarea>
        <button type="submit">Enviar Mensaje</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>