<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ChessFantazy – Home</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f9f9f9;
      padding-top: 50px;
      text-align: center;
    }
    .btn {
      margin: 15px;
      width: 200px;
    }
  </style>
</head>
<body>
  <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

  <div class="container">
    <a href="game.html" class="btn btn-primary">Board with Analysis</a>
    <a href="multiplayer.php" class="btn btn-success">Play Multi-player</a>
    <a href="profile.php" class="btn btn-info">Profile</a>
    <a href="play.php" class="btn btn-success">Play Online</a>
    <a href="logout.php" class="btn btn-danger">Logout</a>
  </div>
</body>
</html>
