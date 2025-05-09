<?php
  // Iniciar la sesión solo si no está activa
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  require_once 'includes/db.php';
  require_once 'includes/functions.php';

  // Manejar la lógica de compra ANTES de cualquier salida
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase'])) {
      if (!isset($_SESSION['user_id'])) {
          // En lugar de redirigir, establecer una bandera para mostrar el modal
          $_SESSION['show_auth_modal'] = true;
          $_SESSION['error'] = 'Debes iniciar sesión o registrarte para finalizar la compra.';
      } else {
          // Obtener ítems del carrito desde la sesión
          $cart_items = [];
          if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
              foreach ($_SESSION['cart'] as $item) {
                  $stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, b.name AS brand_name FROM vehicles v JOIN models m ON v.model_id = m.id JOIN brands b ON m.brand_id = b.id WHERE v.id = ?");
                  $stmt->execute([$item['vehicle_id']]);
                  $vehicle = $stmt->fetch();
                  if ($vehicle) {
                      $cart_items[] = array_merge($item, $vehicle);
                  }
              }
          }

          $user_id = $_SESSION['user_id'];
          foreach ($cart_items as $item) {
              // Verificar stock nuevamente antes de la compra
              $stmt = $pdo->prepare("SELECT stock FROM vehicles WHERE id = ?");
              $stmt->execute([$item['vehicle_id']]);
              $vehicle = $stmt->fetch();
              if ($vehicle['stock'] < $item['quantity']) {
                  $_SESSION['error'] = "No hay suficiente stock para el vehículo: " . $item['brand_name'] . ' ' . $item['model_name'];
                  redirect(BASE_URL . 'cart.php');
              }

              // Descontar stock
              $stmt = $pdo->prepare("UPDATE vehicles SET stock = stock - ? WHERE id = ?");
              $stmt->execute([$item['quantity'], $item['vehicle_id']]);

              // Registrar la compra
              $stmt = $pdo->prepare("INSERT INTO sales (user_id, vehicle_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, ?)");
              $stmt->execute([$user_id, $item['vehicle_id'], $item['quantity'], $item['total_price'], date('Y-m-d H:i:s')]);
          }

          // Limpiar el carrito
          unset($_SESSION['cart']);
          $_SESSION['cart_count'] = 0;
          $_SESSION['success'] = 'Compra realizada con éxito.';
          redirect(BASE_URL . 'cart.php');
      }
  }

  // Ahora que las redirecciones están manejadas, podemos incluir el header y generar salida
  include 'includes/header.php';

  // Obtener ítems del carrito desde la sesión para mostrarlos
  $cart_items = [];
  if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) {
          $stmt = $pdo->prepare("SELECT v.*, m.name AS model_name, b.name AS brand_name FROM vehicles v JOIN models m ON v.model_id = m.id JOIN brands b ON m.brand_id = b.id WHERE v.id = ?");
          $stmt->execute([$item['vehicle_id']]);
          $vehicle = $stmt->fetch();
          if ($vehicle) {
              $cart_items[] = array_merge($item, $vehicle);
          }
      }
  }
  ?>

  <div class="container mx-auto py-8">
      <h1 class="text-3xl font-bold mb-6">Carrito de Compras</h1>
      <?php if (isset($_SESSION['success'])): ?>
          <p class="text-green-600 mb-4"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
      <?php endif; ?>
      <?php if (isset($_SESSION['error'])): ?>
          <p class="text-red-600 mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
      <?php endif; ?>
      <?php if (empty($cart_items)): ?>
          <p class="text-gray-600">Tu carrito está vacío.</p>
      <?php else: ?>
          <table class="w-full mb-6">
              <thead>
                  <tr>
                      <th>Imagen</th>
                      <th>Modelo</th>
                      <th>Cantidad</th>
                      <th>Precio Total</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($cart_items as $item): ?>
                      <tr>
                          <td><img src="<?php echo BASE_URL; ?>images/<?php echo $item['image']; ?>" alt="<?php echo $item['model_name']; ?>" class="w-16 h-16 object-cover rounded"></td>
                          <td><?php echo $item['brand_name'] . ' ' . $item['model_name']; ?></td>
                          <td>
                              <span id="quantity-display-<?php echo $item['vehicle_id']; ?>"><?php echo $item['quantity']; ?></span>
                              <button id="decrease-<?php echo $item['vehicle_id']; ?>" class="bg-gray-500 text-white px-2 py-1 rounded hover:bg-gray-600 ml-2">-</button>
                              <button id="increase-<?php echo $item['vehicle_id']; ?>" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 ml-1">+</button>
                          </td>
                          <td id="total-price-<?php echo $item['vehicle_id']; ?>">$<?php echo number_format($item['total_price'], 2); ?></td>
                          <td>
                              <!-- Formulario para eliminar -->
                              <form id="remove-form-<?php echo $item['vehicle_id']; ?>" method="POST" action="<?php echo BASE_URL; ?>remove_from_cart.php" class="inline-block">
                                  <input type="hidden" name="vehicle_id" value="<?php echo $item['vehicle_id']; ?>">
                                  <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">Eliminar</button>
                              </form>
                          </td>
                      </tr>
                      <script>
                      // Manejar aumento de cantidad
                      document.getElementById('increase-<?php echo $item['vehicle_id']; ?>').addEventListener('click', (e) => {
                          e.preventDefault();
                          let quantity = parseInt(document.getElementById('quantity-display-<?php echo $item['vehicle_id']; ?>').textContent);
                          const stock = <?php echo $item['stock']; ?>;
                          const vehicleId = <?php echo $item['vehicle_id']; ?>;
                          const price = <?php echo $item['price']; ?>;

                          if (quantity < stock) {
                              quantity++;
                              document.getElementById('quantity-display-<?php echo $item['vehicle_id']; ?>').textContent = quantity;
                              document.getElementById('total-price-<?php echo $item['vehicle_id']; ?>').textContent = '$' + numberFormat(price * quantity, 2);
                              updateCart(vehicleId, quantity);
                          } else {
                              alert('No hay suficiente stock disponible (' + stock + ').');
                          }
                      });

                      // Manejar disminución de cantidad
                      document.getElementById('decrease-<?php echo $item['vehicle_id']; ?>').addEventListener('click', (e) => {
                          e.preventDefault();
                          let quantity = parseInt(document.getElementById('quantity-display-<?php echo $item['vehicle_id']; ?>').textContent);
                          const vehicleId = <?php echo $item['vehicle_id']; ?>;
                          const price = <?php echo $item['price']; ?>;

                          if (quantity > 1) {
                              quantity--;
                              document.getElementById('quantity-display-<?php echo $item['vehicle_id']; ?>').textContent = quantity;
                              document.getElementById('total-price-<?php echo $item['vehicle_id']; ?>').textContent = '$' + numberFormat(price * quantity, 2);
                              updateCart(vehicleId, quantity);
                          } else if (quantity === 1) {
                              if (confirm('¿Deseas eliminar este ítem del carrito?')) {
                                  removeItem(vehicleId);
                              }
                          }
                      });

                      // Función para actualizar el carrito
                      function updateCart(vehicleId, quantity) {
                          fetch('<?php echo BASE_URL; ?>add_to_cart.php', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/x-www-form-urlencoded',
                              },
                              body: 'vehicle_id=' + vehicleId + '&quantity=' + quantity
                          })
                          .then(response => response.json())
                          .then(result => {
                              if (result.success) {
                                  // Actualizar cart_count manualmente
                                  const cartCount = document.getElementById('cart-count');
                                  cartCount.textContent = parseInt(cartCount.textContent) + (quantity - <?php echo $item['quantity']; ?>);
                              } else {
                                  alert(result.error || 'Error al actualizar la cantidad.');
                                  location.reload(); // Recargar si falla para mantener consistencia
                              }
                          })
                          .catch(error => {
                              console.error('Error:', error);
                              alert('Ocurrió un error al procesar la solicitud.');
                              location.reload();
                          });
                      }

                      // Función para eliminar ítem
                      function removeItem(vehicleId) {
                          fetch('<?php echo BASE_URL; ?>remove_from_cart.php', {
                              method: 'POST',
                              headers: {
                                  'Content-Type': 'application/x-www-form-urlencoded',
                              },
                              body: 'vehicle_id=' + vehicleId
                          })
                          .then(response => {
                              if (!response.ok) {
                                  throw new Error('Network response was not ok ' + response.statusText);
                              }
                              return response.json();
                          })
                          .then(result => {
                              if (result.success) {
                                  const row = document.querySelector(`#remove-form-${vehicleId}`).closest('tr');
                                  row.remove();
                                  const cartCount = document.getElementById('cart-count');
                                  cartCount.textContent = Math.max(0, parseInt(cartCount.textContent) - <?php echo $item['quantity']; ?>);
                                  if (document.querySelectorAll('tbody tr').length === 0) {
                                      location.reload(); // Recargar si el carrito queda vacío
                                  }
                              } else {
                                  alert(result.error || 'Error al eliminar el ítem.');
                                  location.reload(); // Recargar si falla para mantener consistencia
                              }
                          })
                          .catch(error => {
                              console.error('Error:', error);
                              alert('Ocurrió un error al procesar la solicitud: ' + error.message);
                              location.reload();
                          });
                      }

                      // Manejar el formulario de eliminación
                      document.getElementById('remove-form-<?php echo $item['vehicle_id']; ?>').addEventListener('submit', (e) => {
                          e.preventDefault();
                          removeItem(<?php echo $item['vehicle_id']; ?>);
                      });

                      // Función para formatear números
                      function numberFormat(number, decimals) {
                          return number.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
                      }
                      </script>
                  <?php endforeach; ?>
              </tbody>
          </table>
          <form id="purchase-form" method="POST" action="">
              <button type="submit" name="purchase" class="button mt-4 inline-block">Finalizar Compra</button>
          </form>
      <?php endif; ?>
  </div>

  <?php include 'includes/footer.php'; ?>

  <script>
  // Mostrar el modal de autenticación si es necesario
  document.addEventListener('DOMContentLoaded', () => {
      <?php if (isset($_SESSION['show_auth_modal']) && $_SESSION['show_auth_modal']): ?>
          document.getElementById('auth-modal').style.display = 'flex';
          document.querySelector('.tab-button[data-tab="login"]').click(); // Abrir la pestaña de login por defecto
          <?php unset($_SESSION['show_auth_modal']); ?>
      <?php endif; ?>
  });