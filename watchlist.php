<?php
require 'db.php';
require 'user_required.php';

$userId = $_SESSION['user_id'];

$stmt = $db->prepare("
  SELECT m.*, um.status, um.rating, um.comment, um.is_favorite, s.name AS studio_name
  FROM user_movie um
  JOIN movies m ON um.movie_id = m.movie_id
  LEFT JOIN studios s ON m.studio_id = s.studio_id
  WHERE um.user_id = ?
  ORDER BY um.is_favorite DESC, um.created_at DESC
");
$stmt->execute([$userId]);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>My Watchlist – CineTrack</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 min-h-screen">

  <?php include 'navbar.php'; ?>

  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-3xl font-bold mb-6">My Watchlist</h1>

    <?php if (count($movies) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($movies as $movie): ?>
          <div class="bg-white rounded shadow overflow-hidden flex flex-col">
            <a href="movie_detail.php?movie_id=<?php echo $movie['movie_id']; ?>" class="hover:opacity-90 transition">
              <img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'https://via.placeholder.com/400x600?text=No+Image'); ?>"
                alt="<?php echo htmlspecialchars($movie['title'] ?? ''); ?> Poster"
                class="w-full h-[360px] object-cover" />
            </a>
            <div class="p-4 flex flex-col justify-between flex-1">
              <div class="mb-2 space-y-1 flex flex-col justify-between h-full text-base">
                <div class="space-y-1">
                  <h2 class="text-lg font-semibold">
                    <a href="movie_detail.php?movie_id=<?php echo $movie['movie_id']; ?>" class="text-blue-700 hover:underline">
                      <?php echo htmlspecialchars($movie['title'] ?? ''); ?>
                    </a>
                  </h2>
                  <p class="text-gray-600"><strong>Year:</strong> <?php echo htmlspecialchars($movie['year'] ?? ''); ?></p>
                  <p class="text-gray-600"><strong>Studio:</strong> <?php echo htmlspecialchars($movie['studio_name'] ?? ''); ?></p>
                  <p class="text-gray-600"><strong>Status:</strong>
                    <?php echo $movie['status'] === 'seen' ? 'Seen' : 'Want to See'; ?>
                  </p>
                  <?php if (!empty($movie['rating'])): ?>
                    <p class="text-yellow-600"><strong>Rating:</strong> <?php echo (int)$movie['rating']; ?>/10</p>
                  <?php endif; ?>
                  <?php if (!empty($movie['comment'])): ?>
                    <p class="text-gray-700"><strong>Note:</strong> <?php echo htmlspecialchars($movie['comment']); ?></p>
                  <?php endif; ?>
                </div>

                <div class="mt-2 space-y-2">
                  <?php if ($movie['is_favorite']): ?>
                    <span class="inline-block text-xs px-2 py-1 bg-pink-100 text-pink-700 rounded">★ Favorite</span>
                  <?php endif; ?>

                  <a href="edit_watchlist.php?movie_id=<?php echo $movie['movie_id']; ?>"
                    class="text-blue-600 hover:underline text-sm">✏️ Edit</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600 mt-6 text-base">
        You haven't added any movies yet.
        Go to the <a href="index.php" class="text-blue-600 hover:underline">movie library</a> to get started!
      </p>
    <?php endif; ?>
  </div>

</body>

</html>