<?php
require 'db.php';
require 'user_required.php';

$userId = $_SESSION['user_id'];
$formErrors = [];
$formSuccess = '';

// Načteme filmy pro výběr
$movies = $db->query("SELECT movie_id, title FROM movies ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

// Zjistíme, jestli je něco předvyplněné
$prefilledMovieId = $_GET['movie_id'] ?? ($_POST['movie_id'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = $_POST['movie_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = trim($_POST['comment'] ?? '');
    $is_favorite = isset($_POST['is_favorite']) ? 1 : 0;

    if (!$movie_id || !$status) {
        $formErrors[] = "Please select a movie and status.";
    }

    if ($rating !== '' && ($rating < 1 || $rating > 10)) {
        $formErrors[] = "Rating must be between 1 and 10.";
    }

    $stmt = $db->prepare("SELECT * FROM user_movie WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$userId, $movie_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $formErrors[] = "This movie is already in your watchlist.";
    }

    if (empty($formErrors)) {
        $stmt = $db->prepare("INSERT INTO user_movie (user_id, movie_id, status, rating, comment, is_favorite)
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $movie_id, $status, $rating !== '' ? $rating : null, $comment, $is_favorite]);

        $prefilledMovieId = '';
        $_POST = [];
        header('Location: watchlist.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add to Watchlist – CineTrack</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">

<?php include 'navbar.php'; ?>

<div class="max-w-screen-sm mx-auto px-4 sm:px-6 py-8">
  <h1 class="text-3xl font-bold mb-6 text-center">Add Movie to Watchlist</h1>

  <?php if (!empty($formErrors)): ?>
    <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
      <ul class="list-disc pl-6 text-sm">
        <?php foreach ($formErrors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php elseif ($formSuccess): ?>
    <div class="bg-green-100 text-green-700 p-4 rounded mb-6">
      <?php echo htmlspecialchars($formSuccess); ?>
    </div>
  <?php endif; ?>

  <form method="post" class="space-y-6 bg-white shadow-md rounded p-6">
    <div>
      <label for="movie_id" class="block text-base font-medium text-gray-700">Movie</label>
      <select name="movie_id" id="movie_id" required
              class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 text-base">
        <option value="">-- Select a movie --</option>
        <?php foreach ($movies as $movie): ?>
          <option value="<?php echo $movie['movie_id']; ?>"
            <?php if ($prefilledMovieId == $movie['movie_id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($movie['title']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="status" class="block text-base font-medium text-gray-700">Status</label>
      <select name="status" id="status" required
              class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 text-base">
        <option value="">-- Choose --</option>
        <option value="seen" <?php if ($_POST['status'] ?? '' === 'seen') echo 'selected'; ?>>Seen</option>
        <option value="want_to_see" <?php if ($_POST['status'] ?? '' === 'want_to_see') echo 'selected'; ?>>Want to See</option>
      </select>
    </div>

    <div>
      <label for="rating" class="block text-base font-medium text-gray-700">Rating (1–10, optional)</label>
      <input type="number" name="rating" id="rating" min="1" max="10"
             value="<?php echo htmlspecialchars($_POST['rating'] ?? ''); ?>"
             class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 text-base">
    </div>

    <div>
      <label for="comment" class="block text-base font-medium text-gray-700">Comment (optional)</label>
      <textarea name="comment" id="comment" rows="3"
                class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 text-base"><?php echo htmlspecialchars($_POST['comment'] ?? ''); ?></textarea>
    </div>

    <div class="flex items-center gap-3">
      <input type="checkbox" name="is_favorite" id="is_favorite" <?php if (!empty($_POST['is_favorite'])) echo 'checked'; ?>>
      <label for="is_favorite" class="text-base text-gray-700">Mark as Favorite</label>
    </div>

    <div class="pt-4 flex flex-wrap items-center gap-4">
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition text-base">
        Add to Watchlist
      </button>
      <a href="index.php" class="text-gray-600 hover:underline text-base">← Back to Library</a>
    </div>
  </form>
</div>

</body>
</html>
