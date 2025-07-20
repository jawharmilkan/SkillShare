<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1");
$user = $res->fetch_assoc();
$message = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $about = $conn->real_escape_string($_POST['about']);
    $photo = $user['photo'];

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $targetDir = "assets/images/";
        $fileName = uniqid('user_', true) . basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if ($check !== false && in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                $photo = $targetFile;
            } else {
                $uploadError = "Image upload failed, using previous photo.";
            }
        } else {
            $uploadError = "Only JPG, JPEG, PNG & GIF files allowed.";
        }
    }

    $sql = "UPDATE users SET name='$name', location='$location', about='$about', photo='$photo' WHERE id='$user_id'";
    if ($conn->query($sql)) {
        $message = "Profile updated!";
        // Refresh user data
        $res = $conn->query("SELECT * FROM users WHERE id='$user_id' LIMIT 1");
        $user = $res->fetch_assoc();
    } else {
        $message = "Update failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Profile</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
      <h2 class="text-2xl font-bold text-blue-700 mb-6 text-center">Edit Profile</h2>
      <?php if ($message): ?>
        <div class="mb-4 text-center text-green-600 font-bold"><?php echo $message; ?></div>
      <?php endif; ?>
      <?php if ($uploadError): ?>
        <div class="mb-4 text-center text-red-500 font-bold"><?php echo $uploadError; ?></div>
      <?php endif; ?>
      <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div class="flex flex-col items-center">
          <label for="photo" class="cursor-pointer">
            <div class="w-20 h-20 bg-gray-100 border-2 border-dashed border-blue-300 rounded-full flex items-center justify-center mb-2 transition hover:shadow-lg">
              <img id="preview" src="<?php echo htmlspecialchars($user['photo'] ? $user['photo'] : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'); ?>" class="rounded-full w-20 h-20 object-cover"/>
            </div>
            <input type="file" id="photo" name="photo" accept="image/*" class="hidden"/>
          </label>
          <span class="text-xs text-gray-400">Click image to upload new photo</span>
        </div>
        <div>
          <label class="block font-semibold mb-1">Name</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
        </div>
        <div>
          <label class="block font-semibold mb-1">Location</label>
          <input type="text" name="location" value="<?php echo htmlspecialchars($user['location']); ?>" class="w-full px-4 py-2 rounded-xl border border-gray-300" required>
        </div>
        <div>
          <label class="block font-semibold mb-1">About</label>
          <textarea name="about" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-300"><?php echo htmlspecialchars($user['about']); ?></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-full font-bold hover:bg-blue-700 w-full">Save Changes</button>
        <a href="profile.php" class="block text-center mt-3 text-blue-600 hover:underline">Back to Profile</a>
      </form>
    </div>
  </div>
  <script>
    document.getElementById('photo').addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (evt) {
          document.getElementById('preview').src = evt.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>
