<?php
session_start();
header('Content-Type: text/plain');

// 1) Database connection
$host = 'localhost';
$user = "chessfan_TestUser";
$pass = "TestPassword";
$db = "chessfan_TestUsers";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die('DB Conn Error: ' . $conn->connect_error);
}

// 2) Fetch and sanitize
$username = trim($_POST['username']);
$password = $_POST['password'];

if (strlen($username) < 3 || strlen($password) < 6) {
  echo 'Username ≥3 chars; Password ≥6 chars.';
  exit;
}

// 3) Check if username exists
$stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
  echo 'Username already taken.';
  exit;
}

// 4) Hash password and insert
$hash = password_hash($password, PASSWORD_DEFAULT);
$ins  = $conn->prepare(
  'INSERT INTO users (username, password) VALUES (?, ?)'
);
$ins->bind_param('ss', $username, $hash);
if ($ins->execute()) {
  echo 'Account created! You can now log in.';
} else {
  echo 'Registration error. Try again.';
}
?>
