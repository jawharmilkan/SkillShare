<?php
session_start();
require 'db.php';

// Handle search/filter (basic demo)
$where = "WHERE s.status='active'";
$search = '';
$category = '';

if (isset($_GET['search']) && $_GET['search'] != '') {
    $search = $conn->real_escape_string($_GET['search']);
    // Prefix columns to avoid ambiguity!
    $where .= " AND (s.title LIKE '%$search%' OR s.location LIKE '%$search%' OR s.description LIKE '%$search%' OR u.name LIKE '%$search%' OR u.location LIKE '%$search%')";
}
if (isset($_GET['category']) && $_GET['category'] != '') {
    $category = $conn->real_escape_string($_GET['category']);
    $where .= " AND s.category='$category'";
}

$sql = "SELECT s.*, u.name as user_name FROM skills s JOIN users u ON s.user_id = u.id $where ORDER BY s.created_at DESC";
$res = $conn->query($sql);

// Pre-fetch all ratings for visible skills in one query for efficiency
$skill_ids = [];
$skills = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $skills[] = $row;
        $skill_ids[] = $row['id'];
    }
}
$ratings_map = [];
if (count($skill_ids)) {
    $ids_csv = implode(',', $skill_ids);
    $rating_res = $conn->query("SELECT skill_id, COUNT(*) as n, AVG(rating) as avg_rating FROM reviews WHERE skill_id IN ($ids_csv) GROUP BY skill_id");
    if ($rating_res) {
        while ($r = $rating_res->fetch_assoc()) {
            $ratings_map[$r['skill_id']] = ['n' => $r['n'], 'avg' => round($r['avg_rating'],1)];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Browse Skills</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-50 to-teal-50 min-h-screen">
  <!-- Header -->
  <header class="flex items-center justify-between px-6 py-4 bg-white shadow-lg">
    <div class="text-2xl font-bold text-blue-700">SkillShare</div>
    <nav class="space-x-4 hidden md:flex">
      <a href="dashboard.php" class="hover:text-blue-500">Home</a>
      <a href="skills.php" class="text-blue-600 font-semibold">Browse Skills</a>
      <a href="post_skill.php" class="hover:text-blue-500">Post Skill</a>
      <a href="profile.php" class="hover:text-blue-500">Profile</a>
    </nav>
    <button id="menu-btn" class="block md:hidden text-2xl text-blue-700 focus:outline-none">&#9776;</button>
  </header>
  <div id="mobile-menu" class="md:hidden bg-white px-6 py-4 space-y-2 hidden shadow-lg">
    <a href="dashboard.php" class="block hover:text-blue-500">Home</a>
    <a href="skills.php" class="block text-blue-600 font-semibold">Browse Skills</a>
    <a href="post_skill.php" class="block hover:text-blue-500">Post Skill</a>
    <a href="profile.php" class="block hover:text-blue-500">Profile</a>
  </div>

  <main class="max-w-6xl mx-auto py-10 px-4">
    <!-- Title and Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
      <h1 class="text-3xl font-bold text-blue-800">Browse Skills</h1>
      <form class="flex items-center gap-2" method="GET" action="skills.php">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search skills, user or location..." class="px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 w-60 shadow-sm" />
        <select name="category" class="px-4 py-2 rounded-xl border border-gray-300 focus:outline-blue-400 shadow-sm">
          <option value="">All</option>
          <option value="Design" <?php if($category=='Design') echo 'selected'; ?>>Design</option>
          <option value="Music" <?php if($category=='Music') echo 'selected'; ?>>Music</option>
          <option value="Cooking" <?php if($category=='Cooking') echo 'selected'; ?>>Cooking</option>
          <option value="IT" <?php if($category=='IT') echo 'selected'; ?>>IT</option>
          <option value="Other" <?php if($category=='Other') echo 'selected'; ?>>Other</option>
        </select>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">Search</button>
      </form>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="mb-4 text-center <?php echo $_GET['msg']=='request_sent'?'text-green-600':'text-orange-600'; ?> font-bold">
        <?php
        if ($_GET['msg'] == 'request_sent') echo "Request sent!";
        if ($_GET['msg'] == 'already_requested') echo "You have already sent a request for this skill.";
        ?>
      </div>
    <?php endif; ?>

    <!-- Skills Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8" id="skills-grid">
      <?php if ($skills): ?>
        <?php foreach ($skills as $row): ?>
        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col items-center hover:scale-105 transition-transform duration-300 group">
          <img src="<?php echo htmlspecialchars($row['image']); ?>" class="w-16 h-16 rounded-full mb-4 shadow-lg group-hover:rotate-6 transition-transform duration-300" alt="profile">
          <h3 class="font-bold text-lg mb-1">
            <a href="skill_detail.php?id=<?php echo $row['id']; ?>" class="text-blue-700 hover:underline">
              <?php echo htmlspecialchars($row['title']); ?>
            </a>
          </h3>
          <p class="text-gray-500 mb-2"><?php echo htmlspecialchars($row['user_name'] . ', ' . $row['location']); ?></p>
          <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1 rounded-full font-semibold mb-2"><?php echo htmlspecialchars($row['category']); ?></span>
          <div class="text-gray-500 text-sm mb-3 text-center"><?php echo htmlspecialchars($row['description']); ?></div>

          <!-- Average Rating & Count -->
          <?php
          $rating = isset($ratings_map[$row['id']]) ? $ratings_map[$row['id']]['avg'] : null;
          $count = isset($ratings_map[$row['id']]) ? $ratings_map[$row['id']]['n'] : 0;
          ?>
          <div class="flex items-center gap-1 mb-2">
            <?php if ($rating): ?>
              <?php for ($i=1; $i<=5; $i++): ?>
                <svg class="inline w-4 h-4" fill="<?php echo $i<=$rating ? '#F59E42' : 'none'; ?>" stroke="#F59E42" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 19,21 12,17 5,21 7,14 2,9 9,9"></polygon></svg>
              <?php endfor; ?>
              <span class="text-yellow-700 font-bold ml-1"><?php echo number_format($rating,1); ?></span>
              <span class="text-gray-400 text-xs ml-1">(<?php echo $count; ?>)</span>
            <?php else: ?>
              <span class="text-gray-400 text-xs">No reviews</span>
            <?php endif; ?>
          </div>
          <a href="skill_detail.php?id=<?php echo $row['id']; ?>" class="mt-2 bg-blue-600 text-white px-5 py-2 rounded-full shadow hover:bg-blue-700 transition-all font-bold">
            View
          </a>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-3 text-center text-gray-500 py-10 text-xl">No skills found.</div>
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
