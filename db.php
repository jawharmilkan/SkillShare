<?php
// db.php
$host = 'localhost';
$db   = 'skillshare';
$user = 'root';
$pass = ''; // XAMPP default is no password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
