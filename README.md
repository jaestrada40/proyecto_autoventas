# 🚗 Proyecto Autoventas

Este proyecto es una plataforma de gestión de ventas de autos, desarrollada para administrar vehículos, usuarios y ventas de forma eficiente. Está construida con PHP, MySQL, etc.].

---

## 📥 Clonar el Repositorio

Para obtener una copia del proyecto en tu máquina local, abre tu terminal y ejecuta:

```bash
git clone https://github.com/jaestrada40/proyecto_autoventas.git
cd proyecto_autoventas
mysql -u root -p car_dealership < car_dealership.sql
CREATE DATABASE car_dealership

🔐 Credenciales de Administrador
👤 Usuario: jaestradag
🔑 Contraseña: abc123
Usa estas credenciales para iniciar sesión como administrador en la plataforma.

▶️ Cómo Ejecutar
Asegúrate de tener un servidor local (XAMPP, MAMP o similar).
Copia el repositorio dentro de la carpeta htdocs (o equivalente).

Abre tu navegador en:
http://localhost/proyecto_autoventas


📁 Estructura del Proyecto
car_dealership
├── README.md
├── about.php
├── add_to_cart.php
├── admin
│   ├── add_user.php
│   ├── admin_sidebar.php
│   ├── categories.php
│   ├── dashboard.php
│   ├── delete_category.php
│   ├── delete_model.php
│   ├── delete_user.php
│   ├── delete_vehicle.php
│   ├── edit_category.php
│   ├── edit_model.php
│   ├── edit_user.php
│   ├── edit_vehicle.php
│   ├── index.php
│   ├── logout.php
│   ├── messages.php
│   ├── models.php
│   ├── users.php
│   └── vehicles.php
├── buy_vehicle.php
├── car_dealership.sql
├── cart.php
├── categories.php
├── contact.php
├── css
│   └── styles.css
├── images
│   ├── Porsche 911.jpg
│   ├── Toyota-Camry-2025 (1).jpg
│   ├── cr-v-2019-lhd-exterior-78.jpg
│   ├── default-user.png
│   ├── logo.png
│   ├── man.png
│   └── toyota-camry-2025.jpg
├── includes
│   ├── admin_sidebar.php
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── index.php
├── js
│   └── scripts.js
├── login.php
├── logout.php
├── profile.php
├── register.php
├── search.php
└── vehicle_detail.php
