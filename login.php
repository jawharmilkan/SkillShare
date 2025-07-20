<?php
require 'db.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - SkillShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 flex items-center justify-center min-h-screen">
  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-blue-700 mb-6 text-center">Login</h2>
    <?php if ($message): ?>
      <div class="mb-4 text-center text-red-600"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="email" name="email" placeholder="Email" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
      <input type="password" name="password" placeholder="Password" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
      <button type="submit" class="bg-blue-600 text-white w-full py-3 rounded-xl font-bold hover:bg-blue-700">Login</button>
    </form>
    <div class="text-center mt-3">
      New user? <a href="register.php" class="text-blue-600 font-semibold">Register</a>
    </div>
  </div>
</body>
</html>
