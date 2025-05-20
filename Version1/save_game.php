<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['pgn'], $data['fen'], $data['winner'])) {
    http_response_code(400);
    echo "Invalid input";
    exit;
}

$username = $_SESSION['username'];
$pgn = $data['pgn'];
$fen = $data['fen'];
$winner = $data['winner'];
$played_at = date('Y-m-d H:i:s');

$conn = new mysqli("localhost", "chessfan_TestUser", "TestPassword", "chessfan_TestUsers");
if ($conn->connect_error) {
    http_response_code(500);
    echo "DB connection failed";
    exit;
}

$stmt = $conn->prepare("INSERT INTO games (username, pgn, fen, winner, played_at) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $pgn, $fen, $winner, $played_at);

if ($stmt->execute()) {
    echo "Game saved successfully";
} else {
    http_response_code(500);
    echo "Failed to save game";
}

$stmt->close();
$conn->close();
?>
