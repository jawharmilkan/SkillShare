<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get and sanitize form data
$user_id = $_SESSION['user_id'];
$skill_name = isset($_POST['skill_name']) ? trim($_POST['skill_name']) : '';
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

// Optional: Validate inputs
if ($skill_name == '' || $category == '' || $description == '') {
    header('Location: post_skill.php?error=Please+fill+all+fields');
    exit();
}

// Optional: Default image for skill (you can change this later)
$default_image = "https://cdn-icons-png.flaticon.com/512/3135/3135715.png";

// Insert into database
$stmt = $conn->prepare("INSERT INTO skills (user_id, title, category, description, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("issss", $user_id, $skill_name, $category, $description, $default_image);
if ($stmt->execute()) {
    header('Location: profile.php?success=Skill+added+successfully');
} else {
    header('Location: post_skill.php?error=Error+saving+skill');
}
exit();
?>
