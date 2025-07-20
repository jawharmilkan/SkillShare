<?php
session_start();
include 'db.php'; // Make sure you include your DB connection!

$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $unread_res = $conn->query("
        SELECT COUNT(m.id) AS cnt
        FROM messages m
        JOIN requests r ON m.request_id = r.id
        WHERE m.receiver_id = $uid AND m.is_read = 0 AND r.status = 'accepted'
    ");
    if ($unread_res && $row = $unread_res->fetch_assoc()) {
        $unread_count = $row['cnt'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Skill - SkillShare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Link your main CSS file -->
    <link rel="stylesheet" href="assets/style.css">
    <!-- Optional: Google Fonts, etc. -->
    <style>
        body {
            background: #f6fbff;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.07);
            padding: 40px 32px;
        }
        h2 {
            color: #1666e8;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: bold;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: #222;
            font-weight: 600;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 12px 10px;
            border: 1px solid #c3d4ea;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 1rem;
            background: #f6faff;
        }
        textarea {
            resize: vertical;
        }
        button[type="submit"] {
            width: 100%;
            background: #1666e8;
            color: #fff;
            font-size: 1.1rem;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(22,102,232,0.1);
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #0547af;
        }
        .navbar {
            background: #fff;
            padding: 20px 0 15px 0;
            box-shadow: 0 3px 16px rgba(22,102,232,0.04);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .navbar .logo {
            font-size: 1.6rem;
            font-weight: bold;
            color: #1666e8;
            letter-spacing: 1px;
            margin-right: 32px;
        }
        .navbar nav a {
            margin: 0 16px;
            text-decoration: none;
            color: #222;
            font-weight: 500;
            position: relative;
        }
        .navbar nav a:hover,
        .navbar nav a.active {
            color: #1666e8;
        }
        .navbar nav .relative {
            position: relative;
        }
        .navbar nav .absolute {
            position: absolute;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">SkillShare</div>
        <nav>
          <a href="dashboard.php" class="hover:text-blue-500">Home</a>
          <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
          <a href="post_skill.php" class="hover:text-blue-500 active">Post Skill</a>
          <a href="profile.php" class="<?php echo $unread_count>0?'relative text-blue-600 font-semibold':'text-blue-600 font-semibold'; ?>">
            Profile
            <?php if ($unread_count > 0): ?>
              <span class="absolute -top-2 -right-3 bg-red-600 text-white rounded-full text-xs px-2 py-0.5 shadow font-bold"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </a>
        </nav>
    </div>

    <div class="container">
        <h2>Post a New Skill</h2>
        <!-- Post Skill Form -->
        <form action="save_skill.php" method="POST">
            <label for="skill_name">Skill Name</label>
            <input type="text" id="skill_name" name="skill_name" required>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select Category</option>
                <option value="Tech">Tech</option>
                <option value="Art">Art</option>
                <option value="Language">Language</option>
                <option value="Other">Other</option>
            </select>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <button type="submit">Submit Skill</button>
        </form>
    </div>
</body>
</html>
