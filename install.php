<?php
// Instalador alternativo: http://localhost/JapanConvenience/install.php
// Cria o banco e popula tudo (use se o phpMyAdmin der erro).
require_once __DIR__ . '/config/database.php';
try {
    $tmp = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $tmp->exec("CREATE DATABASE IF NOT EXISTS japan_convenience CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco ok. Agora importe o database.sql no phpMyAdmin.<br><a href='index.php'>Ir para a loja</a>";
} catch (Exception $e) { echo "Erro: " . $e->getMessage(); }
