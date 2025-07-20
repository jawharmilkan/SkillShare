<?php
session_start();
require 'db.php';

// Validate skill id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: skills.php');
    exit();
}
$skill_id = intval($_GET['id']);

// Get skill
$resSkill = $conn->query("SELECT s.*, u.name as user_name, u.photo FROM skills s JOIN users u ON s.user_id = u.id WHERE s.id='$skill_id'");
if (!$resSkill || $resSkill->num_rows == 0) {
    echo "<h2 class='text-center text-2xl mt-20'>Skill not found.</h2>";
    exit();
}
$skill = $resSkill->fetch_assoc();

// Get reviews
$resReviews = $conn->query("SELECT r.*, u.name, u.photo FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.skill_id='$skill_id' ORDER BY r.created_at DESC");
$reviews = [];
$ratings = [];
if ($resReviews) {
    while ($r = $resReviews->fetch_assoc()) {
        $reviews[] = $r;
        $ratings[] = intval($r['rating']);
    }
}
$avg_rating = count($ratings) ? round(array_sum($ratings)/count($ratings),1) : null;

// Can this user review?
$can_review = false; $existing_review = null; $request_id = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $skill['user_id']) {
    $uid = $_SESSION['user_id'];
    // Find accepted request (connection)
    $resReq = $conn->query("SELECT * FROM requests WHERE skill_id='$skill_id' AND requester_id='$uid' AND status='accepted' LIMIT 1");
    if ($resReq && $req = $resReq->fetch_assoc()) {
        $request_id = $req['id'];
        // Has this user reviewed?
        $resMyReview = $conn->query("SELECT * FROM reviews WHERE skill_id='$skill_id' AND reviewer_id='$uid' AND request_id='{$req['id']}' LIMIT 1");
        if ($resMyReview && $resMyReview->num_rows == 0) $can_review = true;
        else if ($resMyReview) $existing_review = $resMyReview->fetch_assoc();
    }
}

// Handle review POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $can_review && isset($_POST['rating'])) {
    $rating = max(1, min(5, intval($_POST['rating'])));
    $review = $conn->real_escape_string($_POST['review']);
    $conn->query("INSERT INTO reviews (skill_id, reviewer_id, rating, review, request_id) VALUES ('$skill_id', '$uid', '$rating', '$review', '$request_id')");
    header("Location: skill_detail.php?id=$skill_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Skill Detail</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header (with nav) -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="text-blue-600 font-semibold">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-700 font-semibold">Profile</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block text-blue-600 font-semibold">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-700 font-semibold">Profile</a>
  </div>

  <main class="max-w-3xl mx-auto py-12 px-4">
    <!-- Skill Info -->
    <div class="bg-white rounded-3xl shadow-xl p-8 flex flex-col md:flex-row items-center gap-8 mb-12">
      <img src="<?php echo htmlspecialchars($skill['image']); ?>" class="w-24 h-24 rounded-full shadow-lg" alt="Skill">
      <div class="flex-1 text-center md:text-left">
        <h1 class="text-3xl font-extrabold text-blue-800 mb-1"><?php echo htmlspecialchars($skill['title']); ?></h1>
        <div class="mb-2">
          <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-semibold mr-2"><?php echo htmlspecialchars($skill['category']); ?></span>
          <span class="text-gray-400 text-xs"><?php echo htmlspecialchars($skill['location']); ?></span>
        </div>
        <div class="mb-2"><?php echo htmlspecialchars($skill['description']); ?></div>
        <div class="flex items-center gap-2 mt-1">
          <img src="<?php echo $skill['photo'] ? htmlspecialchars($skill['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-8 h-8 rounded-full border" alt="">
          <span class="text-gray-600"><?php echo htmlspecialchars($skill['user_name']); ?></span>
        </div>
      </div>
    </div>

    <!-- Average Rating -->
    <div class="mb-8 text-center">
      <?php if ($avg_rating): ?>
        <div class="flex justify-center items-center gap-2 text-lg font-bold text-yellow-600 mb-1">
          <span><?php echo number_format($avg_rating,1); ?></span>
          <?php for ($i=1; $i<=5; $i++): ?>
            <svg class="inline w-5 h-5" fill="<?php echo $i<=$avg_rating ? '#F59E42' : 'none'; ?>" stroke="#F59E42" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"></polygon></svg>
          <?php endfor; ?>
          <span class="text-gray-500 font-medium text-sm ml-2">(<?php echo count($ratings); ?> review<?php if(count($ratings)!=1)echo "s"; ?>)</span>
        </div>
      <?php else: ?>
        <div class="text-gray-400 font-semibold mb-1">No reviews yet</div>
      <?php endif; ?>
    </div>

    <!-- Write Review (if allowed) -->
    <?php if ($can_review): ?>
      <div class="mb-10 bg-blue-50 p-4 rounded-xl shadow">
        <form method="POST" class="flex flex-col gap-2">
          <div class="font-bold mb-1 text-blue-800">Leave a Review:</div>
          <div class="flex items-center gap-1">
            <?php for ($i=1;$i<=5;$i++): ?>
              <label>
                <input type="radio" name="rating" value="<?php echo $i; ?>" required style="display:none;">
                <svg class="inline w-7 h-7 cursor-pointer hover:scale-110" fill="#F59E42" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"></polygon></svg>
              </label>
            <?php endfor; ?>
          </div>
          <textarea name="review" rows="2" placeholder="Write your feedback..." class="rounded-xl border px-4 py-2 focus:outline-blue-400"></textarea>
          <button class="self-start bg-blue-600 text-white px-4 py-2 rounded-full shadow hover:bg-blue-700 transition-all font-bold mt-2">Submit Review</button>
        </form>
      </div>
      <script>
        // Make clicking the svg star select the rating input
        document.querySelectorAll('input[name="rating"]').forEach((input, idx, all) => {
          input.parentElement.addEventListener('click', () => {
            input.checked = true;
            all.forEach((inp2, i2) => inp2.parentElement.querySelector('svg').setAttribute('fill', i2 <= idx ? '#F59E42' : '#FFF'));
          });
        });
      </script>
    <?php elseif ($existing_review): ?>
      <div class="mb-10 bg-green-50 text-green-800 rounded-xl p-4 shadow font-bold">
        You already reviewed this skill.
      </div>
    <?php endif; ?>

    <!-- All Reviews -->
    <div class="mb-12">
      <div class="text-2xl font-bold text-blue-700 mb-4">Reviews</div>
      <?php if ($reviews): ?>
        <div class="space-y-6">
          <?php foreach ($reviews as $rev): ?>
            <div class="bg-white rounded-2xl shadow p-4 flex gap-3 items-start">
              <img src="<?php echo $rev['photo'] ? htmlspecialchars($rev['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-10 h-10 rounded-full border shadow" alt="">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold"><?php echo htmlspecialchars($rev['name']); ?></span>
                  <?php for ($i=1; $i<=5; $i++): ?>
                    <svg class="inline w-4 h-4" fill="<?php echo $i<=$rev['rating'] ? '#F59E42' : 'none'; ?>" stroke="#F59E42" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"></polygon></svg>
                  <?php endfor; ?>
                  <span class="text-gray-400 text-xs ml-2"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                </div>
                <div class="text-gray-700 mt-1"><?php echo nl2br(htmlspecialchars($rev['review'])); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-gray-400 font-semibold text-center">No reviews yet.</div>
      <?php endif; ?>
    </div>
    <div class="mt-8 text-center">
      <a href="skills.php" class="text-blue-700 hover:underline">&larr; Back to Browse Skills</a>
    </div>
  </main>
  <footer class="mt-16 py-8 bg-white text-center shadow-inner text-gray-500">
    &copy; 2025 SkillShare. Built for your local community.
  </footer>
  <script>
    document.getElementById('menu-btn').addEventListener('click', function() {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('hidden');
    });
  </script>
</body>
</html>
