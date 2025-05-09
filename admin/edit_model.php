<?php
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $stmt = $pdo->prepare("UPDATE models SET name = ?, category_id = ? WHERE id = ?");
    $stmt->execute([$name, $category_id, $id]);
    echo json_encode(['success' => true]);
}
exit;