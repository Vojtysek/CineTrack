<nav class="bg-white shadow mb-6">
  <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-wrap justify-between items-center gap-4">
    
    <div class="flex flex-wrap items-center gap-4 text-base">
      <a href="index.php" class="text-gray-700 font-medium hover:text-blue-600 transition">🎬 Movie Library</a>
      <a href="watchlist.php" class="text-gray-700 font-medium hover:text-blue-600 transition">⭐ My Watchlist</a>
    </div>

    <div class="text-sm sm:text-base text-gray-600 flex items-center gap-2">
      <span>Signed in as <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong></span>
      <span class="hidden sm:inline">|</span>
      <a href="signout.php" class="text-red-600 hover:underline">Sign out</a>
    </div>
  </div>
</nav>
