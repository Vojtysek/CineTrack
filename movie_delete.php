<?php
  require 'db.php';
  require 'admin_required.php';

  $stmt = $db->prepare("DELETE FROM movies WHERE movie_id=?");
  $stmt->execute([$_GET['id']]);

  header('Location: index.php');