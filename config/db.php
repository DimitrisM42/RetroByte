<?php

$DB_HOST = '127.0.0.1';       
$DB_NAME = 'retrobyte';       
$DB_USER = 'root';            // ο χρήστης της MySQL
$DB_PASS = '';                

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          
  PDO::ATTR_EMULATE_PREPARES   => false, 
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  echo "<h2 style='font-family: monospace; color: red;'>Database connection failed.</h2>";
  exit;
}
?>
