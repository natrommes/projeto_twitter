<?php

function getConnection() {
    $config = require '../configs/db.php';
    extract($config);

    $dsn = sprintf("mysql:host=%s;dbname=%s;port=%d", $host, $name, $port);

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch(PDOException $e) {
        die('Error connecting to database!' . $e->getMessage());
    }
}