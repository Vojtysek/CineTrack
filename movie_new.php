<?php
require 'db.php';
require 'admin_required.php';

$studios = $db->query("SELECT studio_id, name FROM studios ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$formErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $director = trim($_POST['director'] ?? '');
  $year = intval($_POST['year'] ?? 0);
  $description = trim($_POST['description'] ?? '');
  $poster_url = trim($_POST['poster_url'] ?? '');
  $studio_id = $_POST['studio_id'] ?? null;
  $new_studio = trim($_POST['new_studio'] ?? '');

  if (empty($title)) $formErrors[] = "Title is required.";
  if ($year < 1888 || $year > intval(date('Y')) + 2) $formErrors[] = "Enter a valid year.";
  if (!empty($poster_url) && !filter_var($poster_url, FILTER_VALIDATE_URL)) $formErrors[] = "Poster URL must be valid.";

  // Pokud je zadáno nové studio, vlož ho a získej jeho ID
  if ($new_studio !== '') {
    $stmt = $db->prepare("INSERT INTO studios (name) VALUES (?)");
    $stmt->execute([$new_studio]);
    $studio_id = $db->lastInsertId();
  }

  if (empty($formErrors)) {
    $stmt = $db->prepare("INSERT INTO movies (title, director_name, year, description, poster_url, studio_id)
                              VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $director, $year, $description, $poster_url, $studio_id ?: null]);

    header('Location: index.php');
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Create New Movie</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
  <?php include 'navbar.php'; ?>

  <div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Create New Movie</h1>

    <?php if (!empty($formErrors)): ?>
      <div class="mb-4 bg-red-100 text-red-700 p-4 rounded">
        <ul class="list-disc pl-6">
          <?php foreach ($formErrors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-6" id="movieForm">
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <div class="flex gap-2">
          <input type="text" name="title" id="title" required
            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
            class="flex-1 mt-1 block w-full border border-gray-300 rounded px-3 py-2">
          <button type="button" onclick="fetchOMDbData()" class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600">
            Autofill
          </button>
        </div>
      </div>

      <div>
        <label for="director" class="block text-sm font-medium text-gray-700">Director</label>
        <input type="text" name="director" id="director"
          value="<?php echo htmlspecialchars($_POST['director'] ?? ''); ?>"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
      </div>

      <div>
        <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
        <input type="number" name="year" id="year" required min="1888" max="<?php echo date('Y') + 2; ?>"
          value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
      </div>

      <div>
        <label for="studio_id" class="block text-sm font-medium text-gray-700">Select Existing Studio</label>
        <select name="studio_id" id="studio_id"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
          <option value="">— None —</option>
          <?php foreach ($studios as $studio): ?>
            <option value="<?php echo $studio['studio_id']; ?>"
              <?php echo (isset($_POST['studio_id']) && $_POST['studio_id'] == $studio['studio_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($studio['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="new_studio" class="block text-sm font-medium text-gray-700">Or Add New Studio</label>
        <input type="text" name="new_studio" id="new_studio"
          value="<?php echo htmlspecialchars($_POST['new_studio'] ?? ''); ?>"
          placeholder="Leave blank if selecting from list"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
      </div>

      <div>
        <label for="poster_url" class="block text-sm font-medium text-gray-700">Poster URL</label>
        <input type="url" name="poster_url" id="poster_url"
          value="<?php echo htmlspecialchars($_POST['poster_url'] ?? ''); ?>"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="4"
          class="mt-1 block w-full border border-gray-300 rounded px-3 py-2"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
      </div>

      <div class="flex gap-4 items-center">
        <button type="submit"
          class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
          Save Movie
        </button>
        <a href="index.php" class="text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>

  <script>
    async function fetchOMDbData() {
      const title = document.getElementById('title').value;
      if (!title.trim()) {
        alert('Please enter a movie title first.');
        return;
      }

      try {
        const response = await fetch(`https://www.omdbapi.com/?t=${encodeURIComponent(title)}&apikey=9ff4a593`);
        const data = await response.json();

        if (data.Response === "False") {
          alert("Movie not found: " + data.Error);
          return;
        }


        document.getElementById('title').value = data.Title || '';
        document.getElementById('director').value = data.Director || '';
        document.getElementById('year').value = data.Year || '';
        document.getElementById('description').value = data.Plot || '';
        document.getElementById('poster_url').value = data.Poster || '';
      } catch (error) {
        alert("Failed to fetch data from OMDb API.");
        console.error(error);
      }
    }
  </script>

</body>

</html>