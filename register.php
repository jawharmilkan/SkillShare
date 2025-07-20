<?php
require 'db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if user already exists
    $check = $conn->query("SELECT id FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $message = "Email already registered.";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql)) {
            $message = "Registration successful! <a href='login.php'>Login here</a>.";
        } else {
            $message = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register - SkillShare</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 flex items-center justify-center min-h-screen">
  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-blue-700 mb-6 text-center">Register</h2>
    <?php if ($message): ?>
      <div class="mb-4 text-center text-red-600"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <input type="text" name="name" placeholder="Your Name" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
      <input type="email" name="email" placeholder="Email" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
      <input type="password" name="password" placeholder="Password" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
      <button type="submit" class="bg-blue-600 text-white w-full py-3 rounded-xl font-bold hover:bg-blue-700">Register</button>
    </form>
    <div class="text-center mt-3">
      Already have an account? <a href="login.php" class="text-blue-600 font-semibold">Login</a>
    </div>
  </div>
</body>
</html>
