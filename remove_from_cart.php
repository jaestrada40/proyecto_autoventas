<?php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  require_once 'includes/db.php';
  require_once 'includes/functions.php';

  header('Content-Type: application/json');

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $vehicle_id = $_POST['vehicle_id'] ?? 0;

      if ($vehicle_id <= 0) {
          echo json_encode(['success' => false, 'error' => 'ID de vehículo inválido.']);
          exit;
      }

      // Verificar si el carrito existe y contiene el ítem
      if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
          foreach ($_SESSION['cart'] as $index => $item) {
              if ($item['vehicle_id'] == $vehicle_id) {
                  $removed_quantity = $item['quantity'];
                  unset($_SESSION['cart'][$index]);
                  // Reindexar el array para evitar huecos
                  $_SESSION['cart'] = array_values($_SESSION['cart']);
                  // Actualizar el conteo del carrito
                  $_SESSION['cart_count'] = max(0, ($_SESSION['cart_count'] ?? 0) - $removed_quantity);
                  echo json_encode(['success' => true]);
                  exit;
              }
          }
          echo json_encode(['success' => false, 'error' => 'Vehículo no encontrado en el carrito.']);
      } else {
          echo json_encode(['success' => false, 'error' => 'Carrito no encontrado o vacío.']);
      }
  } else {
      echo json_encode(['success' => false, 'error' => 'Método no permitido']);
  }
  exit;