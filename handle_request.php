<?php
session_start();
require 'db.php';

$request_id = intval($_POST['request_id']);
$action = $_POST['action'];

$status = $action == 'accept' ? 'accepted' : 'declined';
$conn->query("UPDATE requests SET status='$status' WHERE id='$request_id'");

header("Location: notifications.php");
exit();
