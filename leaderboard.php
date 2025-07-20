<?php
session_start();
require 'db.php';

// Fetch leaderboard: users with most highly-rated skills (average rating, then total reviews)
$sql = "
    SELECT 
        u.id,
        u.name, 
        u.photo, 
        COUNT(DISTINCT s.id) AS skills_count,
        COUNT(r.id) AS review_count,
        COALESCE(ROUND(AVG(r.rating),1),0) AS avg_rating
    FROM users u
    LEFT JOIN skills s ON s.user_id = u.id
    LEFT JOIN reviews r ON r.skill_id = s.id
    GROUP BY u.id
    HAVING skills_count > 0
    ORDER BY avg_rating DESC, review_count DESC
    LIMIT 10
";
$res = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Leaderboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="hover:text-blue-500">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
      <a href="logout.php" class="bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block hover:text-blue-500">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-500">Profile</a>
    <a href="logout.php" class="block bg-red-100 text-red-600 font-bold px-5 py-2 rounded-full shadow hover:bg-red-200 transition">Logout</a>
  </div>

  <main class="max-w-3xl mx-auto py-14 px-4">
    <div class="bg-white rounded-3xl shadow-xl p-8">
      <h1 class="text-3xl font-extrabold text-blue-700 mb-8 text-center">🏆 SkillShare Leaderboard</h1>
      <?php if ($res && $res->num_rows > 0): ?>
        <table class="w-full table-auto border-collapse">
          <thead>
            <tr class="text-blue-800 font-bold text-lg border-b">
              <th class="py-2">Rank</th>
              <th class="py-2 text-left">User</th>
              <th class="py-2">Avg. Rating</th>
              <th class="py-2">Skills</th>
              <th class="py-2">Reviews</th>
            </tr>
          </thead>
          <tbody>
          <?php $rank = 1; while ($row = $res->fetch_assoc()): ?>
            <tr class="text-gray-700 border-b hover:bg-blue-50 transition">
              <td class="text-center font-bold py-3 text-xl"><?php echo $rank; ?></td>
              <td class="flex items-center gap-3 py-3">
                <img src="<?php echo $row['photo'] ? htmlspecialchars($row['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-10 h-10 rounded-full border shadow" alt="">
                <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($row['name']); ?></span>
              </td>
              <td class="text-center">
                <span class="font-bold text-yellow-600"><?php echo $row['avg_rating']; ?></span>
                <?php for ($i=1; $i<=5; $i++): ?>
                  <svg class="inline w-4 h-4" fill="<?php echo $i<=$row['avg_rating'] ? '#F59E42' : 'none'; ?>" stroke="#F59E42" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"></polygon></svg>
                <?php endfor; ?>
              </td>
              <td class="text-center"><?php echo $row['skills_count']; ?></td>
              <td class="text-center"><?php echo $row['review_count']; ?></td>
            </tr>
          <?php $rank++; endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="text-center text-gray-500 py-10 text-xl">No leaderboard data found.</div>
      <?php endif; ?>
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
