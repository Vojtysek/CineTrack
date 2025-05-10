<?php
require 'db.php';
require 'user_required.php';

$userId = $_SESSION['user_id'];
$movieId = $_GET['movie_id'] ?? null;

if (!$movieId || !is_numeric($movieId)) {
  header('Location: watchlist.php');
  exit();
}

// Načti záznam uživatele k filmu
$stmt = $db->prepare("SELECT * FROM user_movie WHERE user_id = ? AND movie_id = ?");
$stmt->execute([$userId, $movieId]);
$watch = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$watch) {
  echo "Watchlist entry not found.";
  exit();
}

$formErrors = [];
$formSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $_POST['status'] ?? '';
  $rating = $_POST['rating'] ?? null;
  $comment = trim($_POST['comment'] ?? '');
  $is_favorite = isset($_POST['is_favorite']) ? 1 : 0;

  if (!in_array($status, ['seen', 'want_to_see'])) {
    $formErrors[] = "Invalid status.";
  }

  if ($rating !== '' && ($rating < 1 || $rating > 10)) {
    $formErrors[] = "Rating must be between 1 and 10.";
  }

  if (empty($formErrors)) {
    $stmt = $db->prepare("
      UPDATE user_movie 
      SET status = ?, rating = ?, comment = ?, is_favorite = ? 
      WHERE user_id = ? AND movie_id = ?
    ");
    $stmt->execute([
      $status,
      $rating !== '' ? $rating : null,
      $comment,
      $is_favorite,
      $userId,
      $movieId
    ]);

    $formSuccess = "Watchlist updated successfully.";
    // refresh data
    $stmt = $db->prepare("SELECT * FROM user_movie WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$userId, $movieId]);
    $watch = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Location: watchlist.php');
    exit();
  }
}

// Získání názvu filmu
$stmt = $db->prepare("SELECT title FROM movies WHERE movie_id = ?");
$stmt->execute([$movieId]);
$movieTitle = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Watchlist – <?php echo htmlspecialchars($movieTitle); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">

  <?php include 'navbar.php'; ?>

  <div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Watchlist: <span class="text-blue-700"><?php echo htmlspecialchars($movieTitle); ?></span></h1>

    <?php if ($formSuccess): ?>
      <div class="mb-4 p-4 rounded bg-green-100 text-green-800 text-sm font-medium">
        <?php echo $formSuccess; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($formErrors)): ?>
      <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
        <ul class="list-disc pl-5 text-sm">
          <?php foreach ($formErrors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-6 bg-white p-6 rounded shadow-md">
      <div>
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
          <option value="seen" <?php if ($watch['status'] === 'seen') echo 'selected'; ?>>Seen</option>
          <option value="want_to_see" <?php if ($watch['status'] === 'want_to_see') echo 'selected'; ?>>Want to See</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Rating (1–10)</label>
        <input type="number" name="rating" min="1" max="10"
          value="<?php echo htmlspecialchars($watch['rating'] ?? ''); ?>"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Comment</label>
        <textarea name="comment" rows="3"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2"><?php echo htmlspecialchars($watch['comment']); ?></textarea>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_favorite" id="is_favorite"
          <?php if ($watch['is_favorite']) echo 'checked'; ?>>
        <label for="is_favorite" class="text-sm text-gray-700">Mark as Favorite</label>
      </div>

      <div class="pt-4 flex gap-3 items-center">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
          Save Changes
        </button>
        <a href="watchlist.php" class="text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>

</body>

</html>