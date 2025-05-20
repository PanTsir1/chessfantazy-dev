<?php
session_start();
if (isset($_SESSION['username'])) {
  header("Location: home.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ChessFantazy – Welcome</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f0f0; color: #333; }
    .hero { padding: 60px 0; text-align: center; background: #222; color: #fff; }
    .features { padding: 40px 0; }
    footer { background: #111; color: #ccc; padding: 20px 0; text-align: center; }
  </style>
</head>
<body>

  <div class="hero">
    <h1>Welcome to ChessFantazy</h1>
    <p>Join, play, and compete in the world of chess.</p>
    <a href="register.html" class="btn btn-primary mx-2">Register</a>
    <a href="login.html" class="btn btn-success mx-2">Login</a>
    <a href="game.html" class="btn btn-warning mx-2">Play as Guest</a>
  </div>

  <div class="features container text-center">
    <h2>Features</h2>
    <div class="row mt-4">
      <div class="col-md-4"><h4>Live Play</h4><p>Challenge other players in real time.</p></div>
      <div class="col-md-4"><h4>AI Practice</h4><p>Improve your skills with computer opponents.</p></div>
      <div class="col-md-4"><h4>Stats</h4><p>Track your progress and performance.</p></div>
    </div>
  </div>

  <footer>
    <p>ChessFantazy &copy; 2025</p>
  </footer>

</body>
</html>
