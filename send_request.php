<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skill_id'])) {
    $skill_id = intval($_POST['skill_id']);
    $requester_id = $_SESSION['user_id'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Prevent requesting your own skill
    $owner_check = $conn->query("SELECT user_id FROM skills WHERE id = $skill_id LIMIT 1");
    $row = $owner_check ? $owner_check->fetch_assoc() : null;
    if (!$row || $row['user_id'] == $requester_id) {
        header('Location: skills.php?msg=invalid_request');
        exit();
    }

    // Check for duplicate request
    $check = $conn->query("SELECT id FROM requests WHERE skill_id = $skill_id AND requester_id = $requester_id");
    if ($check && $check->num_rows > 0) {
        header('Location: skills.php?msg=already_requested');
        exit();
    }

    // Insert request
    $stmt = $conn->prepare("INSERT INTO requests (skill_id, requester_id, status, message) VALUES (?, ?, 'pending', ?)");
    $stmt->bind_param("iis", $skill_id, $requester_id, $message);
    $stmt->execute();
    header('Location: skills.php?msg=request_sent');
    exit();
}

header('Location: skills.php');
exit();
?>
