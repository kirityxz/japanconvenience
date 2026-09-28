<?php
// Configuracao do banco de dados - JapanConvenience
define('DB_HOST', 'localhost');
define('DB_NAME', 'japan_convenience');
define('DB_USER', 'root');
define('DB_PASS', ''); // no XAMPP o padrao e vazio

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage() .
        "<br><br>👉 Importe o arquivo <b>database.sql</b> no phpMyAdmin (http://localhost/phpmyadmin).");
}
