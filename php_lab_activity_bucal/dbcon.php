<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'php_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if (!$conn->set_charset('utf8mb4')) {
    die('Character set setup failed: ' . $conn->error);
    }
