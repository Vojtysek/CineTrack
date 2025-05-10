<?php
require 'db.php';
require 'user_required.php';

$movieId = $_GET['movie_id'] ?? null;

if (!$movieId || !is_numeric($movieId)) {
  header('Location: index.php');
  exit();
}

$stmt = $db->prepare("SELECT m.*, s.name AS studio_name FROM movies m LEFT JOIN studios s ON m.studio_id = s.studio_id WHERE m.movie_id = ?");
$stmt->execute([$movieId]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
  echo "Movie not found.";
  exit();
}

$stmt = $db->prepare("
  SELECT um.*, u.name AS user_name
  FROM user_movie um
  JOIN users u ON um.user_id = u.user_id
  WHERE um.movie_id = ?
  ORDER BY um.created_at DESC
");
$stmt->execute([$movieId]);
$ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($movie['title']); ?> – CineTrack</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">

  <?php include 'navbar.php'; ?>

  <div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <a href="index.php" class="text-base text-blue-600 hover:underline mb-4 block">&larr; Back to Library</a>

    <div class="bg-white rounded shadow flex flex-col lg:flex-row overflow-hidden">
      <div class="w-full lg:w-1/3">
        <img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'https://via.placeholder.com/400x600?text=No+Image'); ?>"
          alt="<?php echo htmlspecialchars($movie['title']); ?> Poster"
          class="w-full h-auto object-cover">
      </div>

      <div class="p-6 lg:w-2/3">
        <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($movie['title']); ?></h1>
        <p class="text-base text-gray-700 mb-1"><strong>Year:</strong> <?php echo htmlspecialchars($movie['year']); ?></p>
        <p class="text-base text-gray-700 mb-1"><strong>Studio:</strong> <?php echo htmlspecialchars($movie['studio_name'] ?? ''); ?></p>
        <p class="text-base text-gray-700 mb-1"><strong>Director:</strong> <?php echo htmlspecialchars($movie['director_name']); ?></p>
        <p class="text-base text-gray-800 mt-3 leading-relaxed"><?php echo htmlspecialchars($movie['description']); ?></p>
      </div>
    </div>

    <div class="mt-10">
      <h2 class="text-2xl font-semibold mb-4">User Reviews</h2>

      <?php if (count($ratings) === 0): ?>
        <p class="text-gray-600">No one has reviewed this movie yet.</p>
      <?php else: ?>
        <div class="space-y-4">
          <?php foreach ($ratings as $entry): ?>
            <div class="bg-white p-5 rounded shadow-sm">
              <div class="flex justify-between flex-wrap text-sm sm:text-base">
                <span class="font-medium text-gray-800"><?php echo htmlspecialchars($entry['user_name']); ?></span>
                <span class="text-gray-500">
                  <?php
                  $date = new DateTime($entry['created_at']);
                  echo $date->format('j.n.Y – H:i');
                  ?>
                </span>
              </div>
              <p class="text-gray-700 mt-2"><strong>Status:</strong> <?php echo htmlspecialchars($entry['status']); ?></p>
              <?php if ($entry['rating']): ?>
                <p class="text-yellow-600"><strong>Rating:</strong> <?php echo (int)$entry['rating']; ?>/10</p>
              <?php endif; ?>
              <?php if ($entry['comment']): ?>
                <p class="mt-2 italic text-gray-800">"<?php echo htmlspecialchars($entry['comment']); ?>"</p>
              <?php endif; ?>
              <?php if ($entry['is_favorite']): ?>
                <span class="inline-block mt-3 px-2 py-1 bg-pink-100 text-pink-700 text-xs rounded">★ Favorite</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</body>

</html>