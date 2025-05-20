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
  <link rel="stylesheet" href="https://unpkg.com/chessground@8.2.2/assets/chessground.base.css">
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

  <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chessground@7.10.1/chessground.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.12.1/chess.min.js"></script>
  <script>
    const username = <?php echo json_encode($username); ?>;
    const game = new Chess();
    const socket = io("https://multiplayer-server-lzrf.onrender.com", { query: { username } });

    let playerColor = 'white';
    let board;
    let selectedTimeControl = null;
    let whiteTime = 0;
    let blackTime = 0;
    let whiteInterval = null;
    let blackInterval = null;
    let increment = 0;

    const formatTime = (seconds) => {
      const m = Math.floor(seconds / 60);
      const s = seconds % 60;
      return `${m}:${s.toString().padStart(2, '0')}`;
    };

    const updateClockDisplay = () => {
      if (playerColor === 'white') {
        document.getElementById('player-timer').textContent = `You: ${formatTime(whiteTime)}`;
        document.getElementById('opponent-timer').textContent = `Opponent: ${formatTime(blackTime)}`;
      } else {
        document.getElementById('player-timer').textContent = `You: ${formatTime(blackTime)}`;
        document.getElementById('opponent-timer').textContent = `Opponent: ${formatTime(whiteTime)}`;
      }
    };

    const startOpponentClock = () => {
      clearInterval(whiteInterval);
      clearInterval(blackInterval);
      if (game.turn() === 'w') {
        whiteInterval = setInterval(() => {
          whiteTime--;
          updateClockDisplay();
        }, 1000);
      } else {
        blackInterval = setInterval(() => {
          blackTime--;
          updateClockDisplay();
        }, 1000);
      }board 
    };

    const startGame = (base, inc) => {
      selectedTimeControl = { base, inc };
      document.getElementById('controls').style.display = 'none';
      document.getElementById('time-box').textContent = 'Waiting for opponent...';
      document.getElementById('time-box').style.display = 'block';
      socket.emit('register', username);
      socket.emit('startGame', { time: base, increment: inc });
    };

    socket.on('connect', () => {
      document.getElementById('status').innerText = "Connected as " + username;
    });

    socket.on('init', data => {
      playerColor = data.color;
      document.getElementById('status').innerText = "Game started vs " + data.opponent;
      document.getElementById('timers-container').style.display = 'block';

      if (playerColor === 'black') {
        const container = document.getElementById('timers-container');
        container.insertBefore(
          document.getElementById('player-timer'),
          document.getElementById('opponent-timer')
        );
      }

      const board = window.chessground.Chessground(document.getElementById('board'), {
      orientation: playerColor,
      turnColor: 'white',
      movable: {
        color: playerColor,
        dests: () => getLegalDests(),
        events: {
          after: (from, to) => {
            const move = game.move({ from, to, promotion: 'q' });
            if (!move) return;
            socket.emit('move', move);
            if (playerColor === 'white') whiteTime += increment;
            else blackTime += increment;
            updateClockDisplay();
            startOpponentClock();
            board.set({ fen: game.fen(), turnColor: game.turn() === 'w' ? 'white' : 'black' });
          }
        }
      }
    });


      const base = selectedTimeControl.base;
      increment = selectedTimeControl.inc;
      whiteTime = base;
      blackTime = base;
      updateClockDisplay();
      document.getElementById('time-box').textContent = `Time Control: ${base / 60}+${increment}`;
    });

    const getLegalDests = () => {
      const dests = new Map();
      game.SQUARES.forEach(s => {
        const moves = game.moves({ square: s, verbose: true });
        if (moves.length) dests.set(s, moves.map(m => m.to));
      });
      return dests;
    };

    socket.on('move', move => {
      game.move(move);
      board.set({ fen: game.fen(), turnColor: game.turn() === 'w' ? 'white' : 'black' });
      updateClockDisplay();
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
  </script>
</body>
</html>
