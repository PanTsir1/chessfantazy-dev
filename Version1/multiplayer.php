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
  <title>Multiplayer Chess</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.css">
  <style>
    body {
      text-align: center;
      background-color: #f0f0f0;
      padding-top: 20px;
    }
    #board {
      width: 400px;
      height: 400px;
      margin: 20px auto;
    }
    #status {
      margin-bottom: 10px;
      font-size: 1.2rem;
      font-weight: bold;
    }
    .white-1e1d7 {
      background-image: url('https://chessfantazy.com/img/chessboards/light.png') !important;
      background-size: cover;
    }
    .black-3c85d {
      background-image: url('https://chessfantazy.com/img/chessboards/dark.png') !important;
      background-size: cover;
    }
    #controls {
      margin: 20px auto;
    }
    .time-btn, .action-btn {
      margin: 5px;
      padding: 10px 15px;
      font-size: 1rem;
      cursor: pointer;
    }
    #time-box {
      margin: 15px auto;
      font-weight: bold;
    }
    body {
      overscroll-behavior: none;
      touch-action: manipulation;
    }
    .clock-box {
      width: 160px;
      margin: 10px auto;
      padding: 10px;
      font-size: 1.4rem;
      font-weight: bold;
      border-radius: 8px;
      background-color: #333;
      color: white;
    }
  </style>
</head>
<body>
  <h2>Multiplayer Chess</h2>
  <div id="status">Connecting...</div>

  <!-- Time Control Selection -->
  <div id="controls">
    <div>
      <button class="time-btn" data-time="180+2">3+2</button>
      <button class="time-btn" data-time="180+0">3+0</button>
      <button class="time-btn" data-time="300+5">5+5</button>
      <button class="time-btn" data-time="300+0">5+0</button>
      <button class="time-btn" data-time="600+2">10+2</button>
      <button class="time-btn" data-time="600+0">10+0</button>
      <button class="time-btn" data-time="900+10">15+10</button>
      <button class="time-btn" data-time="900+0">15+0</button>
    </div>
  </div>

  <div id="time-box" style="display:none;"></div>
  <div id="timers-container" style="display: none;">
    <div id="opponent-timer" class="clock-box">Opponent: 00:00</div>
    <div id="player-timer" class="clock-box">You: 00:00</div>
  </div>
  <div id="board"></div>
  
  <div>
    <button id="drawBtn" class="action-btn">Offer Draw</button>
    <button id="resignBtn" class="action-btn">Resign</button>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.12.1/chess.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chessboard-js/1.0.0/chessboard-1.0.0.min.js"></script>
  
  <script>
    let board, game, socket, username = <?php echo json_encode($username); ?>;
    let selectedTimeControl = null;

    
    const startGame = (base, increment) => {
      selectedTimeControl = { base, inc: increment };
      $('#controls').hide();
      $('#time-box').text(`Waiting for opponent...`).show();
      socket.emit('register', username);
      socket.emit('startGame', { time: base, increment });
    };
    function formatTime(seconds) {
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      return `${m}:${s.toString().padStart(2, '0')}`;
    }

    function updateClockDisplay(whiteTime, blackTime) {
      if (playerColor === 'white') {
        document.getElementById('player-timer').textContent = `You: ${formatTime(whiteTime)}`;
        document.getElementById('opponent-timer').textContent = `Opponent: ${formatTime(blackTime)}`;
      } else {
        document.getElementById('player-timer').textContent = `You: ${formatTime(blackTime)}`;
        document.getElementById('opponent-timer').textContent = `Opponent: ${formatTime(whiteTime)}`;
      }
    }
    function startOpponentClock() {
  clearInterval(whiteInterval);
  clearInterval(blackInterval);

  if (game.turn() === 'w') {
    whiteInterval = setInterval(() => {
      whiteTime--;
      updateClockDisplay(whiteTime, blackTime);
    }, 1000);
  } else {
    blackInterval = setInterval(() => {
      blackTime--;
      updateClockDisplay(whiteTime, blackTime);
    }, 1000);
  }
}



    window.onload = function () {
      console.log("Connecting as:", username);
    // Prevent scrolling on touch drag (mobile fix)
      const boardElement = document.getElementById('board');

      boardElement.addEventListener('touchstart', (e) => {
        e.stopPropagation();
      }, { passive: false });

      boardElement.addEventListener('touchmove', (e) => {
        e.preventDefault(); // Block scroll while dragging
      }, { passive: false });

      socket = io("https://multiplayer-server-lzrf.onrender.com", {
        query: { username }
      });

      socket.on('connect', () => {
        document.getElementById('status').innerText = "Connected as " + username;
      });
      let playerColor = 'white'; // default
      let whiteTime = 0;
      let blackTime = 0;
      let whiteInterval = null;
      let blackInterval = null;
      let increment = 0;

      socket.on('init', data => {
        playerColor = data.color;
        document.getElementById('status').innerText = "Game started vs " + data.opponent;
        // Show timers
        document.getElementById('timers-container').style.display = 'block';

        // Flip clock display if player is black
        if (playerColor === 'black') {
          const container = document.getElementById('timers-container');
          container.insertBefore(
            document.getElementById('player-timer'),
            document.getElementById('opponent-timer')
          );
        }
game = new Chess();

board = Chessboard('board', {
  draggable: true,
  position: 'start',
  orientation: playerColor,
  pieceTheme: 'https://chessfantazy.com/img/chesspieces/{piece}.png',
  onDrop: function (source, target) {
    if (game.turn() !== playerColor[0]) return 'snapback';

    const move = game.move({ from: source, to: target, promotion: 'q' });
    if (move === null) return 'snapback';

    socket.emit('move', move);

    if (playerColor === 'white') whiteTime += increment;
    else blackTime += increment;

    updateClockDisplay(whiteTime, blackTime);
    startOpponentClock();

    // ✅ This will finalize the piece drop
    setTimeout(function () {
      board.position(game.fen(), false);
    }, 0);

    return undefined;
  }
});

        const base = selectedTimeControl.base;
        increment = selectedTimeControl.inc; // ❗ REMOVE `const`, use global `increment`
        whiteTime = base;
        blackTime = base;
        updateClockDisplay(whiteTime, blackTime);
        $('#time-box').text(`Time Control: ${base / 60}+${increment}`);
      });

// Replace your socket.on('move') with:
socket.on('move', move => {
  game.move(move);
  board.position(game.fen());
  updateClockDisplay(whiteTime, blackTime);
  startOpponentClock();
});
      socket.on('drawOffered', () => {
        if (confirm("Opponent offered a draw. Accept?")) {
          socket.emit('drawAccepted');
        } else {
          socket.emit('drawDeclined');
        }
      });

      socket.on('drawAccepted', () => {
        alert("Draw agreed.");
        document.getElementById('status').innerText = "Game drawn.";
      });

      socket.on('drawDeclined', () => {
        alert("Draw declined.");
      });

      socket.on('resigned', () => {
        alert("Opponent resigned. You win!");
        document.getElementById('status').innerText = "You win by resignation.";
      });

      document.querySelectorAll('.time-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const [base, inc] = btn.dataset.time.split('+').map(Number);
          selectedTimeControl = { base, inc };
          startGame(base, inc);
        });
      });

      document.getElementById('drawBtn').addEventListener('click', () => {
        socket.emit('offerDraw');
      });

      document.getElementById('resignBtn').addEventListener('click', () => {
        socket.emit('resign');
        alert("You resigned.");
        document.getElementById('status').innerText = "You resigned.";
      });
    };
    // Prevent scrolling on touch drag (mobile fix)
    const boardElement = document.getElementById('board');

    boardElement.addEventListener('touchstart', (e) => {
      e.stopPropagation();
    }, { passive: false });

    boardElement.addEventListener('touchmove', (e) => {
      e.preventDefault(); // Block scroll while dragging
    }, { passive: false });
  </script>
</body>
</html>
