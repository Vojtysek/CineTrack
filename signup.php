<?php
session_start();
require 'db.php';

$formError = null;

if (!empty($_POST)) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');

    // Ověření, zda je e-mail již použit
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $formError = "Email is already registered.";
    } elseif (strlen($password) < 6) {
        $formError = "Password must be at least 6 characters.";
    } elseif (empty($name)) {
        $formError = "Please enter your name.";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Uložení uživatele
        $stmt = $db->prepare("INSERT INTO users(email, password, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $passwordHash, $name]);

        // Získání ID a přihlášení
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $_SESSION['user_id'] = (int)$stmt->fetchColumn();

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CineTrack – Sign Up</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">

  <div class="w-full max-w-md mx-4 sm:mx-0 p-6 sm:p-8 bg-white rounded-lg shadow-lg">
    <h1 class="text-3xl sm:text-4xl font-bold text-center mb-4 text-gray-800">CineTrack</h1>
    <h2 class="text-lg sm:text-xl font-semibold mb-6 text-center text-gray-700">Create your account</h2>

    <?php if (!empty($formError)): ?>
      <div class="mb-4 text-red-600 font-medium bg-red-100 p-3 rounded text-sm">
        <?php echo htmlspecialchars($formError); ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" id="name" required
               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" required
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
      </div>

      <div>
        <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition text-sm font-medium">
          Create Account
        </button>
      </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
      Already have an account?
      <a href="signin.php" class="text-blue-600 hover:underline">Sign in here</a>
    </p>
  </div>

</body>
</html>
