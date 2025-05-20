<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION['username'];

$conn = new mysqli("localhost", "chessfan_TestUser", "TestPassword", "chessfan_TestUsers");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT pgn, winner, played_at FROM games WHERE username = ? ORDER BY played_at DESC");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$games = [];
while ($row = $result->fetch_assoc()) {
    $games[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>ChessFantazy – My Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.css" />
  <style>
    body { background: #f5f5f5; color: #333; }
    .container { margin-top: 40px; }
    table { background: #fff; }
    #replayBoard {
      width: 100%;
      max-width: 350px;
      margin: auto;
    }
    #moveList {
      max-height: 200px;
      overflow-y: auto;
      margin-top: 10px;
    }
    #moveList span {
      display: inline-block;
      margin: 2px 4px;
      padding: 2px 6px;
      border-radius: 4px;
    }
    #moveList span.active {
      background-color: #ffc107;
      color: #000;
      font-weight: bold;
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
  <div class="container">
    <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
    <h4 class="mt-4">Completed Games</h4>
    <table class="table table-striped mt-3">
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Winner</th>
          <th>Replay</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($games) === 0): ?>
          <tr><td colspan="4">No completed games yet.</td></tr>
        <?php else: ?>
          <?php foreach ($games as $game): 
            $dt = new DateTime($game['played_at']);
            $date = $dt->format('Y-m-d');
            $time = $dt->format('H:i:s');
            $pgn = htmlspecialchars($game['pgn']);
          ?>
          <tr>
            <td><?= $date ?></td>
            <td><?= $time ?></td>
            <td><?= htmlspecialchars($game['winner']) ?></td>
            <td>
              <button class="btn btn-sm btn-primary" onclick="replayGame(`<?= $pgn ?>`)">Replay</button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <a href="home.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
  </div>

  <!-- Modal for Replay -->
  <div class="modal fade" id="replayModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content p-4">
        <h5 class="text-center mb-3">Replay Game</h5>
        <div id="replayBoard"></div>
        <div id="moveList" class="text-center"></div>
        <div class="mt-3 d-flex justify-content-between">
          <button class="btn btn-outline-secondary" id="prev">Previous</button>
          <button class="btn btn-outline-secondary" id="next">Next</button>
        </div>
        <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.12.1/chess.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.js"></script>

  <script>
    let game = new Chess();
    let board;
    let moves = [];
    let moveIndex = 0;
    const modalEl = document.getElementById('replayModal');
    const replayModal = new bootstrap.Modal(modalEl);

    function renderMoveList() {
      const list = document.getElementById('moveList');
      list.innerHTML = '';
      moves.forEach((move, i) => {
        const span = document.createElement('span');
        span.textContent = move;
        if (i === moveIndex - 1) span.classList.add('active');
        list.appendChild(span);
      });
    }

    function replayGame(pgn) {
      game.reset();
      game.load_pgn(pgn);
      moves = game.history();
      game.reset();
      moveIndex = 0;
      renderMoveList();
      replayModal.show();

      // Wait for modal to be fully shown before initializing the board
      setTimeout(() => {
        if (!board) {
          board = Chessboard('replayBoard', {
            pieceTheme: 'https://chessfantazy.com/img/chesspieces/{piece}.png',
            position: 'start',
            draggable: false
          });
        } else {
          board.position('start');
        }
      }, 400); // Slight delay to ensure DOM is ready
    }

    document.getElementById('next').onclick = function () {
      if (moveIndex < moves.length) {
        game.move(moves[moveIndex]);
        board.position(game.fen());
        moveIndex++;
        renderMoveList();
      }
    };

    document.getElementById('prev').onclick = function () {
      if (moveIndex > 0) {
        game.undo();
        moveIndex--;
        board.position(game.fen());
        renderMoveList();
      }
    };
    document.addEventListener('keydown', function (e) {
  if (!document.getElementById('replayModal').classList.contains('show')) return;

  if (e.key === 'ArrowRight') {
    document.getElementById('next').click();
  } else if (e.key === 'ArrowLeft') {
    document.getElementById('prev').click();
  }
});
  </script>
</body>
</html>
