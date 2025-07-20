<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$skill_id = intval($_GET['id']);

$resSkill = $conn->query("SELECT s.*, u.name as user_name, u.photo FROM skills s JOIN users u ON s.user_id = u.id WHERE s.id='$skill_id'");
if (!$resSkill || $resSkill->num_rows == 0) {
    echo "Skill not found.";
    exit();
}
$skill = $resSkill->fetch_assoc();

// Check if this user already sent request
$checkRequest = $conn->query("SELECT * FROM requests WHERE skill_id='$skill_id' AND requester_id='$user_id'");
$existingRequest = $checkRequest->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($skill['title']); ?> - SkillShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-teal-50 min-h-screen">

    <header class="flex items-center justify-between px-8 py-5 bg-white shadow-md">
        <div class="text-2xl font-bold text-blue-700">SkillShare</div>
        <nav class="space-x-6 text-lg">
            <a href="dashboard.php" class="hover:text-blue-500">Home</a>
            <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
            <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
            <a href="profile.php" class="hover:text-blue-500">Profile</a>
        </nav>
    </header>

    <main class="max-w-4xl mx-auto py-12 px-4">

        <div class="bg-white p-8 rounded-3xl shadow-xl mb-8 flex flex-col md:flex-row gap-8">
            <img src="<?php echo $skill['photo'] ? htmlspecialchars($skill['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-24 h-24 rounded-full shadow-md border" alt="User">
            <div>
                <h1 class="text-3xl font-extrabold text-blue-800 mb-1"><?php echo htmlspecialchars($skill['title']); ?></h1>
                <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-semibold"><?php echo htmlspecialchars($skill['category']); ?></span>
                <p class="mt-3 text-gray-600"><?php echo nl2br(htmlspecialchars($skill['description'])); ?></p>
                <p class="mt-3 text-sm text-gray-500">Posted by: <strong><?php echo htmlspecialchars($skill['user_name']); ?></strong></p>

                <?php if ($user_id != $skill['user_id']): ?>
                    <div class="mt-4">
                        <?php if (!$existingRequest): ?>
                            <form method="POST" action="send_request.php">
                                <input type="hidden" name="skill_id" value="<?php echo $skill_id; ?>">
                                <input type="hidden" name="owner_id" value="<?php echo $skill['user_id']; ?>">
                                <button type="submit" class="bg-gradient-to-r from-blue-500 to-teal-400 text-white px-5 py-2 rounded-full shadow hover:scale-105 transition">
                                    Connect & Learn
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="inline-block mt-2 bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-sm">
                                Request <?php echo ucfirst($existingRequest['status']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Video Section -->
        <?php if (!empty($skill['video'])): ?>
            <div class="bg-white p-6 rounded-3xl shadow-xl mb-8">
                <h2 class="text-2xl font-bold text-blue-700 mb-4">Watch Tutorial</h2>
                <video controls class="w-full rounded-xl shadow-lg">
                    <source src="<?php echo htmlspecialchars($skill['video']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            <!-- Notes Section -->
            <div class="bg-white p-6 rounded-3xl shadow-xl">
                <h2 class="text-2xl font-bold text-blue-700 mb-4">Take Notes</h2>
                <textarea id="notes" rows="5" class="w-full border rounded-xl p-4 focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Write your notes here..."></textarea>
                <button onclick="saveNote()" class="mt-3 bg-blue-600 text-white px-6 py-2 rounded-full shadow hover:bg-blue-700 transition">Save Note</button>
                <p id="saved-msg" class="mt-2 text-green-600 hidden">Note saved locally!</p>
            </div>

            <script>
                function saveNote() {
                    const notes = document.getElementById('notes').value;
                    localStorage.setItem('skill_note_<?php echo $skill_id; ?>', notes);
                    document.getElementById('saved-msg').classList.remove('hidden');
                }
                window.onload = function() {
                    const saved = localStorage.getItem('skill_note_<?php echo $skill_id; ?>');
                    if (saved) document.getElementById('notes').value = saved;
                }
            </script>
        <?php else: ?>
            <div class="bg-white p-6 rounded-3xl shadow-xl text-center text-gray-400">
                <p>No tutorial video uploaded for this skill yet.</p>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="skills.php" class="text-blue-700 hover:underline">&larr; Back to Browse Skills</a>
        </div>

    </main>

    <footer class="mt-16 py-8 bg-white text-center shadow-inner text-gray-500">
        &copy; 2025 SkillShare. Built for your local community.
    </footer>

</body>
</html>
