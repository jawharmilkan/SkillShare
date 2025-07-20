<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    die("Request ID missing.");
}

$request_id = intval($_GET['id']);

// Update the request to accepted
$conn->query("UPDATE requests SET status='accepted' WHERE id=$request_id");

// Redirect back to notifications
header("Location: notifications.php");
exit();
?>
