<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    header('Location: profile.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$request_id = intval($_GET['request_id']);

// Check access: user must be skill owner or requester, and request must be accepted
$res = $conn->query("SELECT r.*, s.user_id as skill_owner FROM requests r JOIN skills s ON r.skill_id=s.id WHERE r.id='$request_id'");
if (!$res || $res->num_rows == 0) { die('Invalid chat link.'); }
$row = $res->fetch_assoc();

if ($row['status'] != 'accepted' || ($user_id != $row['requester_id'] && $user_id != $row['skill_owner'])) {
    die('Access denied.');
}

$other_id = ($user_id == $row['requester_id']) ? $row['skill_owner'] : $row['requester_id'];
$otherUserRes = $conn->query("SELECT name, photo FROM users WHERE id='$other_id'");
$otherUser = $otherUserRes->fetch_assoc();

// Handle new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && trim($_POST['message']) != '') {
    $msg = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO messages (request_id, sender_id, receiver_id, message) VALUES ('$request_id','$user_id','$other_id','$msg')");
    header("Location: chat.php?request_id=$request_id");
    exit();
}

// AJAX for auto-refresh
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $msgs = $conn->query("SELECT * FROM messages WHERE request_id='$request_id' ORDER BY created_at ASC");
    ob_start();
    if ($msgs && $msgs->num_rows > 0) {
        while ($m = $msgs->fetch_assoc()) {
            ?>
            <div class="flex <?php echo $m['sender_id']==$user_id ? 'justify-end' : 'justify-start'; ?>">
              <div class="px-4 py-2 rounded-2xl shadow text-sm
                <?php echo $m['sender_id']==$user_id ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?>">
                <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                <div class="text-[10px] mt-1 text-right text-gray-300">
                  <?php echo date('H:i', strtotime($m['created_at'])); ?>
                </div>
              </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="text-center text-gray-400">No messages yet. Say hello!</div>';
    }
    echo ob_get_clean();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SkillShare - Chat</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-blue-50 min-h-screen">
  <main class="max-w-xl mx-auto py-10 px-2">
    <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
      <div class="flex items-center gap-4 mb-4">
        <img src="<?php echo $otherUser['photo'] ? htmlspecialchars($otherUser['photo']) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; ?>" class="w-12 h-12 rounded-full border shadow" alt="">
        <div>
          <div class="font-bold text-blue-800 text-lg"><?php echo htmlspecialchars($otherUser['name']); ?></div>
          <div class="text-xs text-gray-400">Chat for request #<?php echo $request_id; ?></div>
        </div>
      </div>
      <div id="messages" class="overflow-y-auto max-h-96 mb-4 flex flex-col gap-2 bg-gray-50 p-2 rounded">
        <!-- Messages will load here -->
      </div>
      <form method="POST" class="flex gap-2">
        <textarea name="message" required rows="1" class="flex-1 px-4 py-2 rounded-xl border border-gray-300" placeholder="Type a message..."></textarea>
        <button type="submit" class="px-5 py-2 rounded-full bg-blue-600 text-white font-bold hover:bg-blue-700">Send</button>
      </form>
      <div class="mt-6 text-center">
        <a href="profile.php" class="text-blue-700 hover:underline">&larr; Back to Profile</a>
      </div>
    </div>
  </main>
  <script>
    function fetchMessages() {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', 'chat.php?request_id=<?php echo $request_id; ?>&ajax=1', true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          var messagesDiv = document.getElementById('messages');
          var atBottom = messagesDiv.scrollHeight - messagesDiv.scrollTop <= messagesDiv.clientHeight + 20;
          messagesDiv.innerHTML = xhr.responseText;
          if (atBottom) {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
          }
        }
      };
      xhr.send();
    }
    setInterval(fetchMessages, 3000); // fetch every 3 seconds
    window.onload = fetchMessages;
  </script>
</body>
</html>
