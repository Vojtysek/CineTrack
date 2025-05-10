<?php
require 'db.php';
require 'user_required.php';

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$sort = $_GET['sort'] ?? 'title_asc';

$sortOptions = [
  'title_asc' => 'm.title ASC',
  'year_desc' => 'm.year DESC',
  'year_asc' => 'm.year ASC',
  'studio_asc' => 's.name ASC',
  'studio_desc' => 's.name DESC',
  'director_asc' => 'm.director_name ASC',
  'director_desc' => 'm.director_name DESC'
];

$orderBy = $sortOptions[$sort] ?? 'm.title ASC';

$count = $db->query("SELECT COUNT(movie_id) FROM movies")->fetchColumn();

$stmt = $db->prepare("SELECT m.*, s.name AS studio_name
                      FROM movies m
                      LEFT JOIN studios s ON m.studio_id = s.studio_id
                      ORDER BY $orderBy
                      LIMIT 10 OFFSET ?");
$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CineTrack – Movies</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans text-gray-800">

  <?php include 'navbar.php'; ?>

  <div class="max-w-screen-xl mx-auto px-6 sm:px-8 py-8">
    <h1 class="text-3xl font-bold mb-6">Movie Library</h1>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
      <a href="movie_new.php" class="bg-blue-600 text-white px-5 py-3 rounded-md text-base hover:bg-blue-700 transition">
        + Add New Movie
      </a>
      <!-- <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
      <?php endif; ?> -->

      <form method="get" class="flex items-center gap-3">
        <label for="sort" class="text-base text-gray-700 font-medium">Sort by:</label>
        <select name="sort" id="sort" onchange="this.form.submit()"
                class="border border-gray-300 rounded px-4 py-2 text-base">
          <option value="title_asc" <?php if ($sort === 'title_asc') echo 'selected'; ?>>Title (A–Z)</option>
          <option value="year_desc" <?php if ($sort === 'year_desc') echo 'selected'; ?>>Year (Newest)</option>
          <option value="year_asc" <?php if ($sort === 'year_asc') echo 'selected'; ?>>Year (Oldest)</option>
          <option value="studio_asc" <?php if ($sort === 'studio_asc') echo 'selected'; ?>>Studio (A–Z)</option>
          <option value="studio_desc" <?php if ($sort === 'studio_desc') echo 'selected'; ?>>Studio (Z–A)</option>
          <option value="director_asc" <?php if ($sort === 'director_asc') echo 'selected'; ?>>Director (A–Z)</option>
          <option value="director_desc" <?php if ($sort === 'director_desc') echo 'selected'; ?>>Director (Z–A)</option>
        </select>
      </form>
    </div>

    <?php if ($count > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($movies as $movie): ?>
          <div class="bg-white rounded shadow overflow-hidden flex flex-col">
            <a href="movie_detail.php?movie_id=<?php echo $movie['movie_id']; ?>">
              <img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'https://via.placeholder.com/400x600?text=No+Image'); ?>"
                   alt="<?php echo htmlspecialchars($movie['title'] ?? ''); ?> Poster"
                   class="w-full h-[360px] object-cover">
            </a>
            <div class="p-4 flex flex-col justify-between flex-1">
              <div class="mb-3 space-y-1">
                <h2 class="text-lg font-semibold">
                  <a href="movie_detail.php?movie_id=<?php echo $movie['movie_id']; ?>"
                     class="text-blue-700 hover:underline">
                    <?php echo htmlspecialchars($movie['title'] ?? ''); ?>
                  </a>
                </h2>
                <p class="text-base text-gray-600"><strong>Year:</strong> <?php echo htmlspecialchars($movie['year'] ?? ''); ?></p>
                <p class="text-base text-gray-600"><strong>Studio:</strong> <?php echo htmlspecialchars($movie['studio_name'] ?? ''); ?></p>
                <p class="text-base text-gray-700"><strong>Director:</strong> <?php echo htmlspecialchars($movie['director_name'] ?? ''); ?></p>
                <p class="text-base text-gray-700 truncate"><strong>Description:</strong> <?php echo htmlspecialchars($movie['description'] ?? ''); ?></p>
              </div>

              <div class="mt-auto pt-4 flex justify-between items-center">
                <a href="add_to_watchlist.php?movie_id=<?php echo $movie['movie_id']; ?>"
                   class="text-blue-600 hover:underline text-base">+ Add to Watchlist</a>

                <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                  <a href="movie_delete.php?id=<?php echo $movie['movie_id']; ?>"
                     class="text-red-600 hover:underline text-base">Delete</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <div class="mt-10 flex flex-wrap justify-center gap-2">
        <?php for ($i = 1; $i <= ceil($count / 10); $i++): ?>
          <a href="index.php?offset=<?php echo ($i - 1) * 10; ?>&sort=<?php echo urlencode($sort); ?>"
             class="px-4 py-2 rounded border border-gray-300 text-base <?php echo ($offset / 10 + 1 == $i) ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100'; ?>">
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-600 mt-6">No movies found.</p>
    <?php endif; ?>
  </div>

</body>
</html>
