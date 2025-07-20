<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$skill_id = intval($_POST['skill_id']);
$receiver_id = intval($_POST['owner_id']);  // This is the skill owner
$requester_id = $_SESSION['user_id'];

// Insert connection request
$conn->query("
    INSERT INTO requests (skill_id, requester_id, receiver_id, status) 
    VALUES ('$skill_id', '$requester_id', '$receiver_id', 'pending')
");

header("Location: skill_detail.php?id=$skill_id");
exit();
?>
