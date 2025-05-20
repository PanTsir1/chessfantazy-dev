<?php
session_start();
$host = "localhost";
$user = "chessfan_TestUser";
$pass = "TestPassword";
$db = "chessfan_TestUsers";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($storedHash);
    $stmt->fetch();

    if (password_verify($password, $storedHash)) {
        $_SESSION['username'] = $username;
        header("Location: home.php");
        exit;
    } else {
        $error = true;
    }
} else {
    $error = true;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Failed</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
  <h2 class="text-danger">Login Failed</h2>
  <p>Invalid username or password. <a href="login.html">Try again</a></p>
</body>
</html>