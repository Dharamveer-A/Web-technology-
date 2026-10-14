<?php
/**
 * Web Technologies (U21CS501) - Assignment 02: Question 2
 * Database Connection Configuration using PHP PDO
 */

$host = 'localhost';
$dbname = 'college_portal';
$username = 'root';
$password = ''; // Set according to local MySQL configuration

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . htmlspecialchars($e->getMessage()));
}
?>
