<?php
// Depuración: Verificar si hay salida antes de conectar
if (headers_sent()) {
    die("Error: Headers already sent in db.php before connecting to database.");
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=car_dealership', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Error connecting to the database. Please try again later.");
}