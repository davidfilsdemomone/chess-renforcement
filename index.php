<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <title>IA d'échecs : Entraînement et Jeu (modif chess 1)</title>
    <!-- CSS de Chessboard.js -->
    <link rel="stylesheet" href="https://qualitesite.com/popular_chess_deep/css/chessboard.css" />
    <!-- Inclusion de jQuery (obligatoire pour Chessboard.js) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Chargement de Chess.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>
    <!-- Chargement de TensorFlow.js -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
    <!-- Chargement de Chessboard.js via votre lien personnalisé -->
    <script src="https://qualitesite.com/popular_chess_deep/js/chessboard.js"></script>
    <style>
      body {
        font-size: 10px;
      } 
      .scorestr {
        font-size: 13px;
      }
      /* Conteneur de l'échiquier */
      #board { width: 400px; margin: 20px auto; }
      /* Contrôles */
      #controls { text-align: center; margin-top: 10px; }
      /* Sections d'entraînement et de jeu */
      #training-section, #play-section { text-align: center; margin-top: 20px; }
      /* Barre de progression */
      #progress-container { margin: 10px auto; width: 300px; }
      progress { width: 100%; height: 20px; }
      progress::-webkit-progress-bar { background-color: #eee; border-radius: 5px; }
      progress::-webkit-progress-value { background-color: #76c7c0; border-radius: 5px; }
      /* Forcer l'affichage en 8 colonnes pour les cases de l'échiquier */
      .chessboard .square-55d63 { width: 12.5% !important; float: left; }
      .chessboard { overflow: hidden; }
      /* Style du mode IA vs IA */
      #mode-info { margin-top: 10px; font-weight: bold; color: #333; }
      /* Scoreboard */
      #scoreboard { margin-top: 10px; font-weight: bold; color: #333; }
      /* Boutons supplémentaires */
      #start-play, #reset-training { margin: 5px; }
    </style>
  </head>
  <body>
    <!-- Section d'entraînement -->
    <div id="training-section">
      <h2>Entraînement de l'IA</h2>

      <br />
      Profondeur de calcul pour l'entraînement : <input type="text" name="prof_entrainement" id="prof_entrainement" value="3" size="3"><br /><br />

      <input name="entierement_prof_train" id="entierement_prof_train" type="checkbox">
      Parcourir entièrement la profondeur pour l'ia du réseau neuronaux
      <br /><br />
      <input name="plus_rapide" id="plus_rapide" type="checkbox">
      Rendre moins facilement accessible cette page pendant les calculs (les parties seront jouées plus rapidement) 

      <br /><br />
      <b>Nombre de parties blanches gagnées (avec calculs normaux) : <input type="text" size="5" id="tot_blanc" value="0"></b><br />

      Valeur moyenne des blancs (avec calculs normaux) : <input type="text" size="18" id="val_blanc" value="0"><br />

      <br />

      <b>Nombre de parties noires gagnées (avec réseau de neurones) : <input type="text" size="5" id="tot_noir" value="0"></b><br />

      Valeur moyenne des noirs (avec réseau de neurones) : <input type="text" size="18" id="val_noir" value="0"><br />

      <br />

      Nombre de parties jouées : <input type="text" size="5" id="totaux" value="0"><br />
      Nombre de parties totales à jouer : <input type="text" size="5" id="nb_parties_totales" value="100"><br />

      <br />
      Durée moyenne par partie : <input type="text" size="45" id="time_moyen_per_partie" value="À calculer..."><br />
      <br />
      <div id="time_moyen_parties_restantes"></div>
      <br />
      <br />
      <button id="start-training">Commencer / Recommencer l'entraînement</button>

      <br /><br />

      Nombre de parties totales déjà jouées par l'IA : <input type="text" size="5" id="nb_parties_totales_IA" value="0" readonly><br />

      <button id="reset-training" style="display:none;">Remettre à zéro tout le réseau neuronaux</button>
      <div id="training-status">Statut : en attente</div>
      <div id="progress-container">
        <progress id="progressBar" value="0" max="100"></progress>
      </div>

      <!-- Bouton pour jouer directement si le modèle existe déjà -->
      <button id="start-play" style="display:none;">Interrompre l'entraînement / Commencer une partie</button>
      <!-- Bouton pour supprimer l'entraînement enregistré -->

    </div>

    <!-- Section de jeu -->
    <div id="play-section" style="display:none;">
      <div id="mode-info">Mode : Joueur vs IA entraînée (IA = Noirs)</div>
      <div id="scoreboard" class="scorestr">Score: Blancs: 0 | Noirs: 0</div>
      <br />
      Tps par coup (ms) : <input type="text" size="5" id="tps_per_coup" value="1000"> - Profondeur : <input type="text" size="2" id="prof_max" value="0"> / Profondeur max : <input type="text" size="2" id="profondeur_max" value="4">
      <br /><input name="check_tps" id="check_tps" type="checkbox" checked="checked">
      Ignorer le temps (calculer jusqu'à la profondeur max)
      <br /><input name="entierement_prof" id="entierement_prof" type="checkbox">
      Parcourir entièrement la profondeur pour l'ia du réseau neuronaux
      <br /><input name="fct_eval" id="fct_eval" type="checkbox">
      Fonction d'évaluation plus précise (réfléchi plus longtemps)
      <br /><input name="break_en_moins" id="break_en_moins" type="checkbox">
      Fonction alphabeta plus précise mais plus longue (on retire la coupure break)
    <div style="display: flex; justify-content: center;">
    <div id="value_chessboard">Valeur de l'échiquier : 0 pts</div>
    <div>&nbsp;/&nbsp;</div>
    <div id="value_chessboard_hybrid">Valeur de l'échiquier avec réseau de neurones : 0 pts</div>
    </div>

      <b><font color=red><div id="tour_joueur">C'est aux blancs de jouer !</div></font></b>
      <div id="board"></div>
      <div id="promotionModal" style="display:none; position: absolute; background: white; border: 1px solid #333; padding: 10px; z-index: 1000;">
        <p>Choisissez votre promotion :</p>
        <button data-piece="q">Dame</button>
        <button data-piece="r">Tour</button>
        <button data-piece="b">Fou</button>
        <button data-piece="n">Cavalier</button>
      </div>

      <div id="controls">
        <button id="resetBtn">Nouvelle partie (Joueur vs IA entraînée)</button>
        <button id="start-ai-vs-ai">Mode IA entraînée (Noire) vs IA réfléchissante (Blanche)</button>
        <input name="check" id="check" type="checkbox">Parties d'ia en boucle
      </div>
      <br />
      <button id="start-training2">Recommencer l'entraînement</button>
    </div>

    <script>
      // Variable globale indiquant si l'on est en phase d'entraînement
      var is_entrainement = true;
      var model; // Le réseau de neurones

      var nb_parties_totales_IA = 0; // par défaut 0 parties jouées par l'ia

      // Paramètres pour la stabilité
      const LEARNING_RATE = 0.0001;
      const optimizer = tf.train.adam(LEARNING_RATE);
      const BATCH_SIZE = 4;
      const REWARD_SCALE = 1;

      // Pour la détection de match nul par nombre total de demi-coups
      const MAX_TOTAL_MOVES = 200; // Vous pouvez ajuster cette valeur

      var game_courant = 'w';

      var time_terminee = 0;
      var time_debut = 0;
      var time_per_partie = 0;
      var time_moyenne = 0;
      var time_cumul = 0;
      var time_nb = 0;

      var interrompre_partie_en_cours_entrainement = false;

      var tot_blanc = 0;
      var tot_noir = 0;
      var val_blanc = 0;
      var val_noir = 0;
      var totaux = 0;
      var totaux_nul = 0;

      // Replay buffer global pour accumuler les expériences de plusieurs parties
      let replayBuffer = [];

      // Fonction pour charger ou créer le modèle
      async function getOrCreateModel() {
        const inputSize = 64;
        const numMoves = 4608;
        try {
      
          const loadedModel = await tf.loadLayersModel('localstorage://chess-model');
  
          if (loadedModel.userDefinedMetadata && loadedModel.userDefinedMetadata.nb_parties_totales) {
            nb_parties_totales_IA = loadedModel.userDefinedMetadata.nb_parties_totales;
            document.getElementById('nb_parties_totales_IA').value = nb_parties_totales_IA;

          } else {

            nb_parties_totales_IA = 0;

            document.getElementById('nb_parties_totales_IA').value = 0;  
          }
          
          console.log("Modèle chargé depuis le stockage local avec nb_parties_totales = ", nb_parties_totales_IA);
          
          return loadedModel;

        } catch (e) {
          console.log("Aucun modèle sauvegardé, création d'un nouveau modèle.");
          const newModel = tf.sequential();
          newModel.add(tf.layers.dense({ inputShape: [inputSize], units: 128, activation: 'relu' }));
          newModel.add(tf.layers.dense({ units: 64, activation: 'relu' }));
          newModel.add(tf.layers.dense({ units: numMoves, activation: 'softmax' }));
          newModel.compile({ optimizer: tf.train.adam(LEARNING_RATE), loss: 'categoricalCrossentropy' });
          let testTensor = tf.zeros([1, inputSize]);
          let testPrediction = newModel.predict(testTensor);
          console.log("Test Prediction (nouveau modèle) :", testPrediction.arraySync());
          return newModel;
        }
      }

      // Wrappers pour mettre à jour le compteur de coups dans game
      function customMove(game, move) {
        const result = game.move(move);
        if (result) {
          if (typeof game.totalMoves !== 'number') {
            game.totalMoves = 0;
          }
          game.totalMoves++;
        }
        return result;
      }

      function formatDuration(ms) {
          // Convertir le temps en différentes unités
          const seconds = Math.floor(ms / 1000);
          const minutes = Math.floor(seconds / 60);
          const hours = Math.floor(minutes / 60);
          const days = Math.floor(hours / 24);
          const weeks = Math.floor(days / 7);
          const months = Math.floor(days / 30);
          const years = Math.floor(days / 365);

          // Calculer les restes pour chaque unité
          const secondsLeft = seconds % 60;
          const minutesLeft = minutes % 60;
          const hoursLeft = hours % 24;
          const daysLeft = days % 7;
          const weeksLeft = weeks % 4;
          const monthsLeft = months % 12;

          // Construire la chaîne de résultat
          let result = [];
          if (years > 0) result.push(`${years} année${years > 1 ? 's' : ''}`);
          if (monthsLeft > 0) result.push(`${monthsLeft} mois`);
          if (weeksLeft > 0) result.push(`${weeksLeft} semaine${weeksLeft > 1 ? 's' : ''}`);
          if (daysLeft > 0) result.push(`${daysLeft} jour${daysLeft > 1 ? 's' : ''}`);
          if (hoursLeft > 0) result.push(`${hoursLeft} heure${hoursLeft > 1 ? 's' : ''}`);
          if (minutesLeft > 0) result.push(`${minutesLeft} minute${minutesLeft > 1 ? 's' : ''}`);
          if (secondsLeft > 0) result.push(`${secondsLeft} seconde${secondsLeft > 1 ? 's' : ''}`);

          return result.join(', ');
      }

      function customUndo(game) {
        const result = game.undo();
        if (result && typeof game.totalMoves === 'number' && game.totalMoves > 0) {
          game.totalMoves--;
        }
        return result;
      }

      // Initialisation d'une partie avec compteur
      function initGame() {
        let game = new Chess();
        game.totalMoves = 0;
        return game;
      }

      document.addEventListener("DOMContentLoaded", async function() {
        console.log("Chessboard:", window.Chessboard);

        // Charger ou créer le modèle
        model = await getOrCreateModel();
        try {
          await tf.loadLayersModel('localstorage://chess-model');
          document.getElementById('start-play').style.display = "inline-block";
          document.getElementById('reset-training').style.display = "inline-block";
          let testTensor = tf.zeros([1, 64]);
          let testPrediction = model.predict(testTensor);
          console.log("Après chargement, Test Prediction :", testPrediction.arraySync());
        } catch (e) {
          // Aucun modèle existant, seuls le bouton d'entraînement sera affiché
        }

        // Variables globales
        let aiTimeoutId = null;
        let whiteScore = 0;
        let blackScore = 0;

        function updateScoreboard() {
          document.getElementById("scoreboard").innerText =
            "Score: Blancs: " + whiteScore + " | Noirs: " + blackScore;
        }

        function stopAI() {
          if (aiTimeoutId) {
            clearTimeout(aiTimeoutId);
            aiTimeoutId = null;
          }
        }

        function uciToMove(uci) {
          return {
            from: uci.substring(0, 2),
            to: uci.substring(2, 4),
            promotion: (uci.length > 4 ? uci.substring(4) : undefined)
          };
        }

        function pieceToNumber(piece) {
          const map = { p: 1, r: 2, n: 3, b: 4, q: 5, k: 6 };
          return piece.color === 'w' ? map[piece.type] : -map[piece.type];
        }

        function boardToTensor(game) {
          const board = game.board();
          let flatBoard = [];
          board.forEach(row => {
            row.forEach(square => {
              flatBoard.push(square ? pieceToNumber(square) : 0);
            });
          });
          return tf.tensor2d([flatBoard]);
        }

        // Mapping entre coups et indices
        const files = ['a','b','c','d','e','f','g','h'];
        const ranks = ['1','2','3','4','5','6','7','8'];
        const moveToIndex = {};
        const indexToMove = {};
        let moveIndex = 0;
        for (let fromFile of files) {
          for (let fromRank of ranks) {
            const fromSquare = fromFile + fromRank;
            for (let toFile of files) {
              for (let toRank of ranks) {
                const toSquare = toFile + toRank;
                const move = fromSquare + toSquare;
                moveToIndex[move] = moveIndex;
                indexToMove[moveIndex] = move;
                moveIndex++;
                if ((fromRank === '7' && toRank === '8') || (fromRank === '2' && toRank === '1')) {
                  for (let promo of ['q','r','b','n']) {
                    const promoMove = move + promo;
                    moveToIndex[promoMove] = moveIndex;
                    indexToMove[moveIndex] = promoMove;
                    moveIndex++;
                  }
                }
              }
            }
          }
        }
        console.log("Total moves mapped:", moveIndex);

        function maskIllegalMoves(predictions, legalMoves) {
          const legalIndices = legalMoves.map(move => moveToIndex[move]).filter(idx => idx !== undefined);
          const maskArray = new Array(4608).fill(0);
          legalIndices.forEach(idx => { maskArray[idx] = 1; });
          const mask = tf.tensor1d(maskArray);
          const maskedPredictions = predictions.mul(mask);
          const sum = maskedPredictions.sum().arraySync();
          if (sum === 0) return predictions;
          return maskedPredictions.div(tf.scalar(sum));
        }

        function sampleFromDistribution(probabilities) {
          let total = probabilities.reduce((a, b) => a + b, 0);
          let r = Math.random() * total;
          for (let i = 0; i < probabilities.length; i++) {
            r -= probabilities[i];
            if (r < 0) return i;
          }
          return probabilities.length - 1;
        }

        function evaluatePosition(game, ignorer_mat_and_pat=false) {
          const board = game.board();
          let score = 0;
          const values = { "p": 100, "n": 320, "b": 330, "r": 500, "q": 900, "k": 20000, 
          "P": 100, "N": 320, "B": 330, "R": 500, "Q": 900, "K": 20000 };

          if (!ignorer_mat_and_pat) {

            // Utilisation du compteur totalMoves plutôt que game.history().length
            if (game.totalMoves >= MAX_TOTAL_MOVES) {
              return 0; // Forcer un match nul
            }

            if (game.in_checkmate()) {
              return game.turn() === 'w' ? -10000 : 10000;
            } else if (game.game_over()) {
              return 0;
            }
          }
            // Évaluation matérielle
          for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
              const piece = board[i][j];
              if (piece) {
                score += piece.color === 'w' ? values[piece.type] : -values[piece.type];
              }
            }
          }

          if ((is_entrainement) || (!document.getElementById('fct_eval').checked)) {
            return score;
          }

          // Pénalisation des coups redondants (globalement)
          const history = game.history();
          const moveCounts = history.reduce((acc, move) => {
            acc[move] = (acc[move] || 0) + 1;
            return acc;
          }, {});
          const redundantPenalty = Object.values(moveCounts)
            .filter(count => count > 1)
            .reduce((acc, count) => acc + Math.pow(count - 1, 2) * 0.1, 0);
          score -= redundantPenalty;

          // Pénalité pour répétition consécutive sur les derniers coups (ici, les 4 derniers)
          const recent = history.slice(-4);
          const consecutiveCount = recent.reduce((acc, move, i, arr) =>
            i === 0 || move === arr[i - 1] ? acc + 1 : 1, 0);
          const consecutivePenalty = consecutiveCount > 2 ? (consecutiveCount - 2) * 0.5 : 0;
          score -= consecutivePenalty;

          // Bonus/Malus liés au confinement du roi
          const whiteKingMobility = kingMobility(game, 'w');
          const blackKingMobility = kingMobility(game, 'b');
          const mobilityThreshold = 3;
          const confinementFactor = 0.25;
          if (blackKingMobility < mobilityThreshold) {
            score += (mobilityThreshold - blackKingMobility) * confinementFactor;
          }
          if (whiteKingMobility < mobilityThreshold) {
            score -= (mobilityThreshold - whiteKingMobility) * confinementFactor;
          }

          // Évaluation tactique basée sur les cases attaquées
          const allMoves = game.moves({ verbose: true });
          const attackedWhite = new Set();
          const attackedBlack = new Set();
          allMoves.forEach(move => {
            if (move.flags.indexOf('c') !== -1) {
              if (move.color === 'b') attackedWhite.add(move.to);
              else if (move.color === 'w') attackedBlack.add(move.to);
            }
          });
          for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
              const file = String.fromCharCode('a'.charCodeAt(0) + j);
              const rank = (8 - i).toString();
              const square = file + rank;
              const piece = board[i][j];
              if (piece) {
                if (piece.color === 'w' && attackedWhite.has(square))
                  score -= values[piece.type] * 0.5;
                if (piece.color === 'b' && attackedBlack.has(square))
                  score += values[piece.type] * 0.5;
              }
            }
          }

          // Calcul des zones attaquées par les pions adverses
          const whitePawnAttacks = new Set();
          const blackPawnAttacks = new Set();
          for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
              const piece = board[i][j];
              if (piece && piece.type === 'p') {
                if (piece.color === 'w') {
                  let row = i - 1;
                  if (row >= 0) {
                    if (j - 1 >= 0) whitePawnAttacks.add(String.fromCharCode('a'.charCodeAt(0) + (j - 1)) + (8 - row));
                    if (j + 1 < 8) whitePawnAttacks.add(String.fromCharCode('a'.charCodeAt(0) + (j + 1)) + (8 - row));
                  }
                } else if (piece.color === 'b') {
                  let row = i + 1;
                  if (row < 8) {
                    if (j - 1 >= 0) blackPawnAttacks.add(String.fromCharCode('a'.charCodeAt(0) + (j - 1)) + (8 - row));
                    if (j + 1 < 8) blackPawnAttacks.add(String.fromCharCode('a'.charCodeAt(0) + (j + 1)) + (8 - row));
                  }
                }
              }
            }
          }

          // Bonus de contrôle du centre (uniquement en début de partie)
          const earlyGameThreshold = 20;
          if (history.length < earlyGameThreshold) {
            const centerSquares = new Set(["d4", "e4", "d5", "e5"]);
            for (let i = 0; i < 8; i++) {
              for (let j = 0; j < 8; j++) {
                const piece = board[i][j];
                if (piece) {
                  const file = String.fromCharCode('a'.charCodeAt(0) + j);
                  const rank = (8 - i).toString();
                  const square = file + rank;
                  if (centerSquares.has(square)) {
                    if (piece.type === 'p' || piece.type === 'n' || piece.type === 'b') {
                      let bonus = piece.color === 'w' ? 0.5 : -0.5;
                      if (piece.color === 'w' && blackPawnAttacks.has(square)) bonus *= 0.5;
                      else if (piece.color === 'b' && whitePawnAttacks.has(square)) bonus *= 0.5;
                      score += bonus;
                    }
                  }
                }
              }
            }

            const grandCenterSquares = new Set([
              "c3", "d3", "e3", "f3",
              "c4",         "f4",
              "c5",         "f5",
              "c6", "d6", "e6", "f6"
            ]);
            for (let i = 0; i < 8; i++) {
              for (let j = 0; j < 8; j++) {
                const piece = board[i][j];
                if (piece) {
                  const file = String.fromCharCode('a'.charCodeAt(0) + j);
                  const rank = (8 - i).toString();
                  const square = file + rank;
                  if (grandCenterSquares.has(square)) {
                    if (piece.type === 'p' || piece.type === 'n' || piece.type === 'b') {
                      let bonus = piece.color === 'w' ? 0.25 : -0.25;
                      if (piece.color === 'w' && blackPawnAttacks.has(square)) bonus *= 0.5;
                      else if (piece.color === 'b' && whitePawnAttacks.has(square)) bonus *= 0.5;
                      score += bonus;
                    }
                  }
                }
              }
            }
          }

          // Bonus / pénalité pour un roi dans le coin
          const corners = new Set(["a1", "h1", "a8", "h8"]);
          for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
              const piece = board[i][j];
              if (piece && piece.type === 'k') {
                const file = String.fromCharCode('a'.charCodeAt(0) + j);
                const rank = (8 - i).toString();
                const square = file + rank;
                if (corners.has(square)) {
                  const kingCornerBonus = 1;
                  score += piece.color === 'w' ? -kingCornerBonus : kingCornerBonus;
                }
              }
            }
          }

          return score;
        }

        // Fonction utilitaire pour mesurer la mobilité du roi
        function kingMobility(game, color) {
          let kingSquare = null;
          const board = game.board();
          for (let i = 0; i < 8; i++) {
            for (let j = 0; j < 8; j++) {
              let piece = board[i][j];
              if (piece && piece.type === 'k' && piece.color === color) {
                kingSquare = String.fromCharCode('a'.charCodeAt(0) + j) + (8 - i);
                break;
              }
            }
            if (kingSquare) break;
          }
          if (!kingSquare) return 0;
          let moves = game.moves({ verbose: true });
          let kingMoves = moves.filter(m => m.from === kingSquare);
          return kingMoves.length;
        }

 // ---------------------------
// Optimisations pour Alpha‑Beta
// ---------------------------

// Valeurs matérielles (en centipions)
const pieceValues = {
  p: 100, n: 320, b: 330, r: 500, q: 900, k: 20000,
  P: 100, N: 320, B: 330, R: 500, Q: 900, K: 20000
};

// Fonction pour cloner le jeu (pour que la recherche ne modifie pas l'état principal)
function cloneGame(game) {
  return new Chess(game.fen());
}

// Fonction pour convertir une notation de case en notation algébrique
function toAlgebraicNotation(square) {
  const file = square.charAt(0);
  const rank = square.charAt(1);
  return `${file}${rank}`;
}

// --- Constantes et variables globales ---
const EXACT = 0, LOWERBOUND = 1, UPPERBOUND = 2;
const transpositionTable = new Map();
const killerMoves = []; // killerMoves[ply] contiendra les coups ayant provoqué un cutoff

// --- Fonction toggleTurn ---
// Renvoie une nouvelle instance de Chess avec le tour inversé (et réinitialise l'en passant)
function toggleTurn(game) {
  let fen = game.fen();
  let parts = fen.split(" ");
  while (parts.length < 6) { parts.push("1"); }
  parts[1] = (parts[1] === 'w') ? 'b' : 'w';
  parts[3] = '-';
  return new Chess(parts.join(" "));
}

 function orderMoves(game, moves, isMaximizingPlayer, useHybrid, ply = 0) {
  const killerBonus = 100000; // Bonus arbitraire pour le killer move
  const currentType = isMaximizingPlayer ? 'max' : 'min';

  // Calculer pour chaque coup son score de base et le bonus killer (le cas échéant)
  moves.forEach(move => {
    game.move(move);
    let baseScore = useHybrid ? evaluatePositionHybrid(game) : evaluatePosition(game);
    game.undo();
    move.order = baseScore;
    
    let bonus = 0;
    if (killerMoves[ply] && killerMoves[ply].some(k => k.from === move.from && k.to === move.to)) {
      const killerEntry = killerMoves[ply].find(k => k.from === move.from && k.to === move.to);
      // Si le killer move a été enregistré dans un nœud du même type, lui donner un bonus positif, sinon négatif
      bonus = (killerEntry.killerType === currentType) ? killerBonus : -killerBonus;
    }
    move.killerBonus = bonus;
  });

  // Tri à deux niveaux : d'abord par la valeur de l'évaluation (ordermove)
  // Puis, en cas d'égalité, par le bonus killer
  moves.sort((a, b) => {
    if (a.order === b.order) {
      // Pour le maximisateur, le bonus le plus élevé est meilleur ; pour le minimisateur, le bonus le plus bas est préférable
      return isMaximizingPlayer ? b.killerBonus - a.killerBonus : a.killerBonus - b.killerBonus;
    }
    return isMaximizingPlayer ? b.order - a.order : a.order - b.order;
  });

  return moves;
}

function isSquareAttacked(game, square, attackerColor) {
  // On récupère les coups du joueur de la couleur attaquante
  let opponentMoves = game.moves({ verbose: true });
  return opponentMoves.some(m => m.to === square && m.color === attackerColor);
}

const TIE_BREAK_FACTOR = 0.001; // Ajustez ce facteur selon vos besoins

async function asyncMinimaxAlphaBetaTimed(node, depth, alpha, beta, isMaximizingPlayer, useHybrid, endTime, ply = 0) {
  
  await nextFrame();  

  if (interrompre_partie_en_cours_entrainement) {

    return 0;
  }

  // Construction de la clé pour la table de transposition
  const key = node.fen() + "_" + depth + "_" + (isMaximizingPlayer ? "max" : "min") + "_" + ply;

  if (transpositionTable.has(key)) {
    const entry = transpositionTable.get(key);
    if (entry.depth >= depth) {
      if (entry.flag === EXACT) return entry.value;
      if (entry.flag === LOWERBOUND) alpha = Math.max(alpha, entry.value);
      else if (entry.flag === UPPERBOUND) beta = Math.min(beta, entry.value);
      if (alpha >= beta) return entry.value;
    }
  }

  // Condition terminale : feuille ou position finale
  if (depth <= 0 || node.game_over()) {
    if (node.game_over()) {
      if (node.in_checkmate()) {
        return (node.turn() === 'w') ? (-10000 - depth) : (10000 + depth);
      }
      return 0;
    }
    const evalScore = useHybrid ? await evaluatePositionHybrid(node) : evaluatePosition(node);
    return evalScore;
  }

  // Null‑move pruning
  if (depth >= 3 && !node.in_check() && ply > 0) {
    const moves = node.moves({ verbose: true });
    const currentHasImmediateThreat = moves.some(m => m.captured || m.check);
    if (!currentHasImmediateThreat) {
      const tempNode = toggleTurn(node);
      const oppMoves = tempNode.moves({ verbose: true });
      const oppHasImmediateThreat = oppMoves.some(m => m.captured || m.check);
      if (!oppHasImmediateThreat) {
        const R = depth >= 6 ? 3 : 2;
        const score = await asyncMinimaxAlphaBetaTimed(tempNode, depth - 1 - R, alpha, beta, !isMaximizingPlayer, useHybrid, endTime, ply + 1);
        if (isMaximizingPlayer) {
          if (score >= beta) {
            transpositionTable.set(key, { value: score, depth: depth, flag: LOWERBOUND });
            return score;
          }
        } else {
          if (score <= alpha) {
            transpositionTable.set(key, { value: score, depth: depth, flag: UPPERBOUND });
            return score;
          }
        }
      }
    }
  }

  // Récupérer et trier les coups avec orderMoves
  let moves = node.moves({ verbose: true });
  moves = orderMoves(node, moves, isMaximizingPlayer, useHybrid, ply);

  let bestEval;
  // Cas maximisant
  if (isMaximizingPlayer) {
    bestEval = -Infinity;

    await nextFrame();

    for (let move of moves) {

      if (interrompre_partie_en_cours_entrainement) {

        return 0;
      }

      customMove(node, move);
      let extension = 0;
      const newDepth = depth - 1 + extension;
      let evalScore = await asyncMinimaxAlphaBetaTimed(node, newDepth, alpha, beta, !isMaximizingPlayer, useHybrid, endTime, ply + 1);
      customUndo(node);
      // Incorporer le tie-break basé sur move.order
      const tieBreak = (typeof move.order !== "undefined" ? move.order : 0) * TIE_BREAK_FACTOR;
      let candidateScore = evalScore + tieBreak;
      
      if (candidateScore > bestEval) {
        bestEval = candidateScore;
      }
      
      if (bestEval >= beta) {
        if (ply > 0) {
          if (!killerMoves[ply]) killerMoves[ply] = [];
          if (!killerMoves[ply].some(k => k.from === move.from && k.to === move.to)) {
            move.killerType = 'max';
            killerMoves[ply].push(move);
          }
        }
        if (!document.getElementById('break_en_moins').checked) {
          let flag = EXACT;
          if (bestEval <= alpha) flag = UPPERBOUND;
          else if (bestEval >= beta) flag = LOWERBOUND;
          transpositionTable.set(key, { value: bestEval, depth: depth, flag: flag });
          return bestEval;
        }
      }
      
      alpha = Math.max(alpha, bestEval);
    }
  } 
  // Cas minimisant
  else {
    bestEval = Infinity;
    for (let move of moves) {
      customMove(node, move);
      let extension = 0;
      const newDepth = depth - 1 + extension;
      let evalScore = await asyncMinimaxAlphaBetaTimed(node, newDepth, alpha, beta, !isMaximizingPlayer, useHybrid, endTime, ply + 1);
      customUndo(node);
      // Incorporer le tie-break : pour Min, on soustrait move.order
      const tieBreak = (typeof move.order !== "undefined" ? move.order : 0) * TIE_BREAK_FACTOR;
      let candidateScore = evalScore + tieBreak;
      
      if (candidateScore < bestEval) {
        bestEval = candidateScore;
      }
      
      if (alpha >= bestEval) {
        if (ply > 0) {
          if (!killerMoves[ply]) killerMoves[ply] = [];
          if (!killerMoves[ply].some(k => k.from === move.from && k.to === move.to)) {
            move.killerType = 'min';
            killerMoves[ply].push(move);
          }
        }
        if (!document.getElementById('break_en_moins').checked) {
          let flag = EXACT;
          if (bestEval <= alpha) flag = UPPERBOUND;
          else if (bestEval >= beta) flag = LOWERBOUND;
          transpositionTable.set(key, { value: bestEval, depth: depth, flag: flag });
          return bestEval;
        }
      }
      
      beta = Math.min(beta, bestEval);
    }
  }

  let flag = EXACT;
  if (isMaximizingPlayer) {
    if (bestEval <= alpha) flag = UPPERBOUND;
    else if (bestEval >= beta) flag = LOWERBOUND;
  } else {
    if (bestEval >= beta) flag = UPPERBOUND;
    else if (bestEval <= alpha) flag = LOWERBOUND;
  }
  transpositionTable.set(key, { value: bestEval, depth: depth, flag: flag });
  return bestEval;
}



async function asyncMinimaxRootAlphaBetaTimed(depth, game, isMaximizingPlayer, useHybrid, endTime) {
  
  killerMoves.splice(0, killerMoves.length);
  killerMoves.length = 0;

  transpositionTable.clear(); 

  let moves = game.moves({ verbose: true });
  if (moves.length === 0) return null;
  moves = orderMoves(game, moves, isMaximizingPlayer, useHybrid, 0);
  
  let bestMoves = [];
  let bestValue = isMaximizingPlayer ? -Infinity : Infinity;
  let alpha = -Infinity, beta = Infinity;
  
  for (let move of moves) {

    if (interrompre_partie_en_cours_entrainement) {

      return null;
    }

    customMove(game, move);
    let boardValue = await asyncMinimaxAlphaBetaTimed(game, depth - 1, alpha, beta, !isMaximizingPlayer, useHybrid && ((document.getElementById("entierement_prof_train").checked && is_entrainement) || (document.getElementById('entierement_prof').checked)), endTime, 1, depth-1);
    customUndo(game);
    if (isMaximizingPlayer) {
      if (boardValue > bestValue) {
        bestValue = boardValue;
        bestMoves = [move];
      } else if (boardValue === bestValue) {
        bestMoves.push(move);
      }
      alpha = Math.max(alpha, bestValue);
    } else {
      if (boardValue < bestValue) {
        bestValue = boardValue;
        bestMoves = [move];
      } else if (boardValue === bestValue) {
        bestMoves.push(move);
      }
      beta = Math.min(beta, bestValue);
    }

    await nextFrame();

    if ((Date.now() > endTime) && (!document.getElementById('check_tps').checked)) break;
  }

  if (bestMoves.length === 0) return null;

  if (!is_entrainement) {
    console.log("%cProf : " + depth + " : Liste des " + bestMoves.length + " coups normaux :", "color:blue");
    bestMoves.forEach(item => {
      console.log(`Coup: ${toAlgebraicNotation(item.from)}-${toAlgebraicNotation(item.to)}, Évaluation: ${bestValue}`);
    });
  }

   // --- Second tri en cas d'égalité ---
  // Si plusieurs coups candidats ont la même évaluation globale, on les trie à nouveau avec orderMoves (calculé en profondeur 0)
 
 /* if (bestMoves.length > 1) {
    let sortedCandidates = orderMoves(game, bestMoves.slice(), isMaximizingPlayer, false, 0);
    // Supposons que orderMoves attribue à chaque coup une propriété 'order'
    let bestOrder = sortedCandidates[0].order;
    bestMoves = sortedCandidates.filter(move => move.order === bestOrder);
    
    if (!is_entrainement) {
      console.log("%cSecond tri (orderMoves) à la profondeur 0 :", "color:green");
      bestMoves.forEach(item => {
        console.log(`Coup: ${toAlgebraicNotation(item.from)}-${toAlgebraicNotation(item.to)}, ordre: ${item.order}`);
      });
    }
  }

  if (bestMoves.length === 0) return null;
  
  */

  // Si on utilise l'évaluation hybride et que l'option correspondante n'est pas entièrement active, on raffine avec evaluatePositionHybrid2 :
  if ((useHybrid) && (!((document.getElementById("entierement_prof_train").checked && is_entrainement) || (document.getElementById('entierement_prof').checked)))) {
    let moveEvaluations = [];
    for (let move of bestMoves) {
      customMove(game, move);
      let pts = evaluatePositionHybrid2(game, false, bestValue);
      customUndo(game);
      moveEvaluations.push({ move: move, evaluation: pts });
    }
    moveEvaluations.sort((a, b) => b.evaluation - a.evaluation);
    bestValue = moveEvaluations[0].evaluation;
    bestMoves = moveEvaluations.filter(item => item.evaluation === bestValue)
                               .map(item => item.move);
    if (!is_entrainement) {
      console.log("%c\nProf = " + depth + " : Liste des " + bestMoves.length + " coups hybrides : ", "color:red");
      bestMoves.forEach(item => {
        console.log(`Coup: ${toAlgebraicNotation(item.from)}-${toAlgebraicNotation(item.to)}, Évaluation hybride: ${bestValue}`);
      });
    }
  }
  
  if (bestMoves.length === 0) return null;
  return bestMoves[Math.floor(Math.random() * bestMoves.length)];
}

// --- Recherche itérative en fonction du temps ---
async function iterativeDeepeningTimed(game, maxDepth, useHybrid, timePerMove) {
  let bestMove = null;
  transpositionTable.clear();
  const ignoreTime = document.getElementById('check_tps').checked;
  const endTime = ignoreTime ? Infinity : Date.now() + timePerMove;
  if (is_entrainement) {
    maxDepth = parseInt(document.getElementById('prof_entrainement').value, 10);
  }
  let debut_depth = is_entrainement ? maxDepth : 1;
  if (ignoreTime && !is_entrainement) {
    maxDepth = parseInt(document.getElementById('profondeur_max').value, 10);
    debut_depth = maxDepth;
    console.log("maxDepth = " + maxDepth);
  }
  
  let searchGame = cloneGame(game);
  
  for (let depth = debut_depth; depth <= maxDepth; depth++) {
    if (!is_entrainement) await new Promise(resolve => requestAnimationFrame(resolve));
    bestMove = await asyncMinimaxRootAlphaBetaTimed(depth, searchGame, (game.turn() === "w"), useHybrid, endTime) 
    if (!is_entrainement) document.getElementById('prof_max').value = depth;
    if ((Date.now() > endTime) && (!ignoreTime)) break;
    if ((!is_entrainement) && (!ignoreTime)) await new Promise(resolve => setTimeout(resolve, 50));
  }
  return bestMove;
}

// --- Fonction principale pour jouer un coup avec délai ---
async function jouerCoupAvecDelaiTimed(game, maxDepth, useHybrid, timePerMove) {
  const bestMove = await iterativeDeepeningTimed(game, maxDepth, useHybrid, timePerMove);
  if (bestMove) {
    if ((!is_entrainement) && (!document.getElementById('check_tps').checked)) {
      const tps = parseInt(document.getElementById('tps_per_coup').value, 10);
      await new Promise(resolve => setTimeout(resolve, tps));
    }
    return bestMove;
  }
  return null;
}


        function evaluateMaterial(game) {
          let board = game.board();
          let score = 0;
          const values = { "p": 1, "n": 3, "b": 3, "r": 5, "q": 9, "k": 100 };
          for (let row of board) {
            for (let piece of row) {
              if (piece) {
                score += (piece.color === 'w' ? values[piece.type] : -values[piece.type]);
              }
            }
          }
          return score;
        }

        function evaluatePositionHybrid(game, ignorer_mat_and_pat=false) {
          return tf.tidy(() => {
            const traditional = evaluatePosition(game, ignorer_mat_and_pat);
            const stateTensor = boardToTensor(game);
            const prediction = model.predict(stateTensor);
            const maxProbTensor = prediction.max();
            let maxProbArray = maxProbTensor.arraySync();
            let maxProb;
            if (Array.isArray(maxProbArray)) {
              if (maxProbArray.length === 1 && typeof maxProbArray[0] === "number") {
                maxProb = maxProbArray[0];
              } else {
                maxProb = Math.max(...maxProbArray.flat());
              }
            } else {
              maxProb = maxProbArray;
            }
            if (!isFinite(maxProb)) {
              console.warn("maxProb is not finite:", maxProb, ". Fallback to traditional evaluation.");
              return traditional;
            }
            const bonusFactor = 100;
            const hybrid = traditional + bonusFactor * maxProb;
            if (!isFinite(hybrid)) {
              console.warn("Hybrid evaluation computed is not finite. Fallback to traditional evaluation.");
              return traditional;
            }
            return hybrid;
          });
        }

        function evaluatePositionHybrid2(game, ignorer_mat_and_pat=false, bestValue) {
          return tf.tidy(() => {
            const traditional = bestValue;
            const stateTensor = boardToTensor(game);
            const prediction = model.predict(stateTensor);
            const maxProbTensor = prediction.max();
            let maxProbArray = maxProbTensor.arraySync();
            let maxProb;
            if (Array.isArray(maxProbArray)) {
              if (maxProbArray.length === 1 && typeof maxProbArray[0] === "number") {
                maxProb = maxProbArray[0];
              } else {
                maxProb = Math.max(...maxProbArray.flat());
              }
            } else {
              maxProb = maxProbArray;
            }
            if (!isFinite(maxProb)) {
              console.warn("maxProb is not finite:", maxProb, ". Fallback to traditional evaluation.");
              return traditional;
            }
            const bonusFactor = 100;
            const hybrid = traditional + bonusFactor * maxProb;
            if (!isFinite(hybrid)) {
              console.warn("Hybrid evaluation computed is not finite. Fallback to traditional evaluation.");
              return traditional;
            }
            return hybrid;
          });
        }

        async function chooseMoveWhite(game, color_piece) {
          let legalVerbose = game.moves({ verbose: true });
          for (let move of legalVerbose) {
            let gameCopy = new Chess(game.fen());
            // Note : Ici on ne met pas à jour totalMoves sur gameCopy, ce qui est acceptable pour la détection immédiate de mate.
            gameCopy.move(move);
            if (gameCopy.in_checkmate()) {
              return move.from + move.to + (move.promotion ? move.promotion : "");
            }
          }
          let depth = is_entrainement ? (Math.floor(Math.random() * 2) + 1) : 5;
          var tps = parseInt(document.getElementById('tps_per_coup').value, 10);
          let best = await jouerCoupAvecDelaiTimed(game, depth, true, tps);
          if (best) {
            return best.from + best.to + (best.promotion ? best.promotion : "");
          }
          let legalMoves = legalVerbose.map(m => m.from + m.to + (m.promotion ? m.promotion : ""));
          return legalMoves[Math.floor(Math.random() * legalMoves.length)];
        }

        async function chooseMoveBlack(game, color_piece) {
          let depth = is_entrainement ? (Math.floor(Math.random() * 2) + 1) : 5;
          var tps = parseInt(document.getElementById('tps_per_coup').value, 10);
          let best = await jouerCoupAvecDelaiTimed(game, depth, false, tps);
          let chosenMove = best ? best.from + best.to + (best.promotion ? best.promotion : "") : "";
          if (!chosenMove || chosenMove.length < 4) {
            let legalMoves = game.moves({ verbose: true }).map(m => m.from + m.to + (m.promotion ? m.promotion : ""));
            chosenMove = legalMoves[Math.floor(Math.random() * legalMoves.length)];
            console.log("%cle coup black était erroné... on prend au random", "color:red");
          }
          return chosenMove;
        }

        async function chooseMove(game) {
          let turn = game.turn();
          document.getElementById('tour_joueur').innerText =
            (turn === 'w') ? "C'est aux blancs de jouer !" : "C'est aux noirs de jouer !";
          if (!is_entrainement) {
            (turn === 'w') ? console.log("%cTour : ordi blanc", "color:orange") : console.log("%cTour : ordi noir", "color:green");
          }
          if (is_entrainement) {
            if (turn === 'b') {
              return await chooseMoveWhite(game, 'w');
            } else {
              return await chooseMoveBlack(game, "w");
            }
          }
          if (mode === "ai") {
            if (turn === 'b') {
              return await chooseMoveWhite(game, 'w');
            } else {
              return await chooseMoveBlack(game, "w");
            }
          } else {
            if (turn === 'b') {
              return await chooseMoveWhite(game, 'w');
            } else {
              return null;
            }
          }
        }

        // Replay buffer pour accumuler l'expérience de plusieurs parties
        replayBuffer = [];

        function storeMove(game, move, stateTensor, color) {
          const moveIdx = moveToIndex[move];
          if (moveIdx !== undefined) {
            replayBuffer.push({ state: stateTensor, action: moveIdx, color: color, result: null });
          } else {
            console.warn("Coup non mappé :", move);
          }
        }

        function sampleBatch(buffer, batchSize) {
          let sample = [];
          let copy = buffer.slice();
          for (let i = 0; i < batchSize; i++) {
            let index = Math.floor(Math.random() * copy.length);
            sample.push(copy[index]);
            copy.splice(index, 1);
          }
          return sample;
        }

        const epsilon = tf.scalar(1e-7);

        function updateModelFromBuffer() {
          if (replayBuffer.length < BATCH_SIZE) {
            console.warn("Replay buffer trop petit (" + replayBuffer.length + "), mise à jour ignorée.");
            return;
          }
          const batch = sampleBatch(replayBuffer, BATCH_SIZE);
          const states = batch.map(item => item.state);
          const actions = batch.map(item => item.action);
          const rewards = batch.map(item => item.result * (item.color === 'w' ? 1 : -1) * REWARD_SCALE);
          const statesTensor = tf.concat(states);
          const actionsTensor = tf.tensor1d(actions, 'int32');
          const rewardsTensor = tf.tensor1d(rewards);
          const actionsOneHot = tf.oneHot(actionsTensor, 4608);
          let loss;
          const gradsAndLoss = optimizer.computeGradients(() => {
            const logits = model.predict(statesTensor);
            const epsilon = tf.scalar(1e-7);
            const logSoftmax = tf.logSoftmax(logits.add(epsilon));
            const selectedLogProbs = tf.sum(tf.mul(actionsOneHot, logSoftmax), 1);
            const negLogProbs = tf.mul(tf.scalar(-1), selectedLogProbs);
            loss = tf.mean(tf.mul(negLogProbs, rewardsTensor));
            return loss;
          });
          const lossVal = loss.arraySync();
          if (isNaN(lossVal)) {
            console.warn("Perte NaN détectée (" + lossVal + "), mise à jour ignorée.");
            statesTensor.dispose();
            actionsTensor.dispose();
            rewardsTensor.dispose();
            actionsOneHot.dispose();
            replayBuffer = [];
            return;
          }
          const clipValue = 1;
          const clippedGrads = {};
          for (const varName in gradsAndLoss.grads) {
            clippedGrads[varName] = tf.clipByValue(gradsAndLoss.grads[varName], -clipValue, clipValue);
          }
          optimizer.applyGradients(clippedGrads);
          statesTensor.dispose();
          actionsTensor.dispose();
          rewardsTensor.dispose();
          actionsOneHot.dispose();
          replayBuffer = [];
        }

        function updateModelAfterGame(result) {
          replayBuffer.forEach(record => {
            record.result = result;
          });
          updateModelFromBuffer();
        }

        async function playTrainingGame() {
          let game = initGame();
          let moveCount = 0;
          const maxMoves = MAX_TOTAL_MOVES;
          while (moveCount < maxMoves) {
            if (game.game_over() || interrompre_partie_en_cours_entrainement) break;
            const stateTensor = boardToTensor(game);
            const move = await chooseMove(game);
            if (!move) break;
            storeMove(game, move, stateTensor, game.turn());
            if (!customMove(game, uciToMove(move))) {
              console.log("Le coup", uciToMove(move), "n'a pas pu être joué.");
              break;
            }
            moveCount++;
          }
          let result;
          if (game.in_checkmate()) {
            result = game.turn() === 'w' ? -1 : 1;
            console.log("%cechec et mat !", "color:red");

            const movesCount = game.totalMoves;
            if (movesCount > 0) {
              const bonusFactor = 1 + (10 / movesCount);
              result *= bonusFactor;
              console.log("Mat bonus appliqué :", bonusFactor);
            }

          } else {
            result = 0;
          }

          model = await getOrCreateModel();

          nb_parties_totales_IA++;

          updateModelAfterGame(result); 
          
          model.userDefinedMetadata = {
            nb_parties_totales: nb_parties_totales_IA
          };

          await model.save('localstorage://chess-model');

          console.log("%cModèle sauvegardé dans le stockage local. nb_parties_totales_IA = " + nb_parties_totales_IA, "color:green");
      
          if (result === 0) {
            whiteScore += 0.5;
            blackScore += 0.5;
          } else if (result < 0) {
            blackScore += 1;
          } else if (result > 0) {
            whiteScore += 1;
          }
          updateScoreboard();
          return game;
        }

        async function handleGameOver() {
          if ((gameInstance.game_over()) || (gameInstance.totalMoves >= MAX_TOTAL_MOVES) || (interrompre_partie_en_cours_entrainement)) {

            let result;

            if (gameInstance.totalMoves >= MAX_TOTAL_MOVES) {

              console.log("%c On a atteint la limite en demi-coups possibles : " + MAX_TOTAL_MOVES + " !", "color:red");

              if (mode == 'user') {

                alert("On atteint la limite en demi-coups possibles : " + MAX_TOTAL_MOVES + " ! ");
              }

             } else {

              if (mode == 'user') {

                var fin_str = "nulle";

                if (gameInstance.in_checkmate()) {

                  fin_str = "mat";

                  if (gameInstance.turn() == "w") {

                    fin_str += " des noirs !";
                  
                  } else {

                    fin_str += " des blancs !";
                  }
                }

                alert("Partie terminée : " + fin_str);
              }

             }

            if (gameInstance.in_checkmate()) {
              result = gameInstance.turn() === 'w' ? -1 : 1;
            } else {
              result = 0;
            }
            const movesCount = gameInstance.totalMoves;
            if (movesCount > 0) {
              const bonusFactor = 1 + (10 / movesCount);
              result *= bonusFactor;
              console.log("Mat bonus appliqué :", bonusFactor);
            }
            replayBuffer.forEach(record => record.result = result);
            console.log("Historique des coups retenus pour l'entraînement :");
            replayBuffer.forEach((item, index) => {
              let result_bon = (index % 2 === 1) ? result : -result;
              console.log("Coup " + (index + 1) + ": " + indexToMove[item.action] + " bonus = " + result_bon);
            });

            if (interrompre_partie_en_cours_entrainement) {

                console.log("La partie est terminée prématurément (on l'a interrompu) !");
            
            } else {

                console.log("La partie est terminée normalement !");
            }

            model = await getOrCreateModel();

            nb_parties_totales_IA++;
            
            document.getElementById('nb_parties_totales_IA').value = nb_parties_totales_IA;

            updateModelAfterGame(result); 
          
            model.userDefinedMetadata = {
              nb_parties_totales: nb_parties_totales_IA
            };

            await model.save('localstorage://chess-model');
            console.log("Modèle sauvegardé dans le stockage local.");            

            if (result === 0) {
              whiteScore += 0.5;
              blackScore += 0.5;
            } else if (result < 0) {
              blackScore += 1;
            } else if (result > 0) {
              whiteScore += 1;
            }
            updateScoreboard();
            if (document.getElementById('check').checked) {
              stopAI();
              document.getElementById('mode-info').innerText = "Mode : IA entraînée (Noirs) vs IA réfléchissante (Blanche)";
              gameInstance.reset();
              gameInstance.totalMoves = 0;
              board.start();
              aiTimeoutId = setTimeout(aiMove, 100);
            }
            return true;
          }
          return false;
        } 

        async function trainLoop() {

          var total = parseInt(document.getElementById('nb_parties_totales').value, 10);
          var current_vrai = parseInt(document.getElementById('totaux').value, 10);

          var current = current_vrai;

          if (current >= total) {
            document.getElementById('training-status').innerText = "Entraînement terminé.";
            document.getElementById('training-section').style.display = 'none';
            document.getElementById('play-section').style.display = 'block';
            initBoard();
            is_entrainement = false;
            model = await getOrCreateModel();

            model.userDefinedMetadata = {
              nb_parties_totales: nb_parties_totales_IA
            };

            await model.save('localstorage://chess-model');
            console.log("Modèle sauvegardé dans le stockage local.");  

            return;
          }
         
          document.getElementById('training-status').innerText = `Partie ${current_vrai} sur ${total} jouée`;
          document.getElementById('progressBar').value = ((current_vrai) / total) * 100;
          game_courant = await playTrainingGame();
          current_vrai = parseInt(document.getElementById('totaux').value, 10);
          document.getElementById('training-status').innerText = `Partie ${current_vrai} sur ${total} jouée`;
          document.getElementById('progressBar').value = ((current_vrai) / total) * 100;
          
          if (interrompre_partie_en_cours_entrainement) {

            console.log(`Partie ${current_vrai+1} terminée prématurément (on l'a interrompu).`);
         
          } else {

               console.log(`Partie ${current_vrai} terminée normalement.`);
          }
          
          document.getElementById('nb_parties_totales_IA').value = nb_parties_totales_IA;

          var parties_restantes = total-current_vrai;

          time_terminee = Date.now();

          if (time_debut != 0) {

            time_per_partie = time_terminee-time_debut;

            time_cumul = time_cumul + time_per_partie;
            time_nb++;

            time_moyenne = Math.floor(time_cumul / time_nb);

            document.getElementById("time_moyen_per_partie").value = formatDuration(time_moyenne);
          }

          var duree_parties_restantes = time_moyenne * parties_restantes;

          if (time_moyenne != 0) {

            document.getElementById("time_moyen_parties_restantes").innerHTML = "Il reste encore <b>" + formatDuration(duree_parties_restantes) + "</b> à attendre avant la fin de l'entraînement de l'IA...";
          }

          if ((game_courant.turn() === 'b') && (game_courant.in_checkmate())) {
            tot_blanc++;
            val_blanc += evaluatePosition(game_courant, true);
            totaux++;
          } else if (game_courant.in_checkmate()) {
            totaux++;
            tot_noir++;
            val_noir += evaluatePosition(game_courant, true);
          } else {
            totaux += 1;
            totaux_nul++;
            val_blanc += evaluatePosition(game_courant, true);
            val_noir += evaluatePositionHybrid(game_courant, true);
            tot_blanc += 0.5;
            tot_noir += 0.5;
          }
          document.getElementById('tot_blanc').value = tot_blanc;
          document.getElementById('val_blanc').value = (totaux==0) ? 0:val_blanc/totaux;
          document.getElementById('tot_noir').value = tot_noir;
          document.getElementById('val_noir').value = (totaux==0) ? 0:val_noir/totaux;
          document.getElementById('totaux').value = totaux;
          
          time_debut = Date.now();

          if (is_entrainement) {

            interrompre_partie_en_cours_entrainement = true; // on interromp la partie en cours si elle est lancée

            await new Promise(resolve => setTimeout(resolve, 1500));

            interrompre_partie_en_cours_entrainement = false; // on la remet en false pour jouer la partie
            await trainLoop();
          }
        }

        async function train(totalGames) { await trainLoop(); }

        document.getElementById('start-training').addEventListener('click', async function() {
          is_entrainement = true;
          document.getElementById('play-section').style.display = 'none';
          document.getElementById('training-section').style.display = 'block';
          time_debut = Date.now();
          time_cumul = 0;
          time_moyenne = 0;
          time_nb = 0;
          totaux = 0;
          tot_blanc = 0;
          tot_noir = 0;
          val_noir = 0;
          val_blanc = 0;
          tot_noir = 0;
          tot_blanc = 0;
          document.getElementById('val_blanc').value = 0;
          document.getElementById('val_noir').value = 0;
          document.getElementById('tot_blanc').value = 0;
          document.getElementById('tot_noir').value = 0;
          document.getElementById('totaux').value = 0;
          train();
        });

        document.getElementById('start-training2').addEventListener('click', function() {
          is_entrainement = true;
          document.getElementById('play-section').style.display = 'none';
          document.getElementById('training-section').style.display = 'block';    
          document.getElementById('totaux').value = 0;
          time_debut = 0;    
        });

        // Bouton pour commencer une partie avec le modèle déjà entraîné
        document.getElementById('start-play').addEventListener('click', async function() {
          
          interrompre_partie_en_cours_entrainement = true; // on interromp la partie en cours si elle est lancée

          await new Promise(resolve => setTimeout(resolve, 1500));

          interrompre_partie_en_cours_entrainement = false; // on la remet en false pouir jouer la partie

          is_entrainement = false;
          document.getElementById('training-section').style.display = 'none';
          document.getElementById('play-section').style.display = 'block';
          initBoard();
        });

        // Bouton pour supprimer tout l'entraînement (supprime le modèle du stockage local)
        document.getElementById('reset-training').addEventListener('click', async function() {
          document.getElementById('nb_parties_totales_IA').value = 0;
          nb_parties_totales_IA = 0;
          try {
            await tf.io.removeModel('localstorage://chess-model');
            console.log("Modèle supprimé du stockage local.");
          } catch(e) {
            console.log("Aucun modèle à supprimer ou erreur lors de la suppression :", e.message);
          }
          const models = await tf.io.listModels();
          console.log("Modèles présents après suppression :", models);
          model = await getOrCreateModel();
        });

        var gameInstance, board;
        let mode = "user"; // "user" pour Joueur vs IA, "ai" pour IA vs IA
        function initBoard() {
          stopAI();
          gameInstance = initGame();
          board = Chessboard('board', {
            draggable: mode === "user",
            position: 'start',
            onDrop: mode === "user" ? onDrop : null
          });
          if (mode === "user") {
            document.getElementById('mode-info').innerText = "Mode : Joueur vs IA entraînée (IA = Noirs)";
          } else if (mode === "ai") {
            document.getElementById('mode-info').innerText = "Mode : IA entraînée (Noirs) vs IA réfléchissante (Blancs)";
            aiTimeoutId = setTimeout(aiMove, 100);
          }
        }

        function onDrop(source, target) {
          stopAI();
          const moves = gameInstance.moves({ verbose: true });
          
          // Vérifier si le coup (sans promotion forcée) est légal
          const coupLegal = moves.some(move =>
            move.from === source &&
            move.to === target &&
            // Si le coup implique une promotion, on accepte seulement si la promotion par défaut est "dame"
            (!move.promotion || move.promotion === 'q')
          );
          
          if (!coupLegal) {
            alert("Veuillez jouer un coup légal, merci !");
            return 'snapback';
          }
          
          // Si le coup implique une promotion, afficher la modale pour laisser l'utilisateur choisir
          const promotionMoves = moves.filter(move =>
            move.from === source &&
            move.to === target &&
            move.flags.indexOf('p') !== -1
          );
          
          if (promotionMoves.length > 0) {
            showPromotionModal(source, target);
            return 'snapback'; // Annuler le déplacement en attendant le choix de promotion
          }
          
          // Si pas de promotion, jouer le coup (promotion forcée en dame par défaut)
          const move = gameInstance.move({ from: source, to: target, promotion: 'q' });
          if (!move) return 'snapback';
          
          board.position(gameInstance.fen());
          
          if (gameInstance.turn() === 'b') {
            setTimeout(aiMove, 100);
          }
        }

        function showPromotionModal(source, target) {
          const modal = document.getElementById('promotionModal');
          // Positionnement de la modale (à ajuster selon vos besoins)
          modal.style.display = 'block';
          modal.style.left = '50%';
          modal.style.top = '50%';
          
          // Ajoute des écouteurs sur les boutons de la modale
          modal.querySelectorAll('button').forEach(button => {
            button.onclick = function() {
              const promotionPiece = this.getAttribute('data-piece');
              // Jouer le coup avec le type de promotion choisi
              const move = gameInstance.move({ from: source, to: target, promotion: promotionPiece });
              board.position(gameInstance.fen());
              modal.style.display = 'none';
              
              // Si c'est le tour de l'IA, déclencher son coup après un léger délai
              if (gameInstance.turn() === 'b') {
                setTimeout(aiMove, 100);
              }
            };
          });
        }

        let lastNextFrameTime = Date.now();

        function nextFrame() {
        
          const now = Date.now();
          const elapsed = now - lastNextFrameTime;
          if ((elapsed >= 100) && (!document.getElementById('plus_rapide').checked)) {
            // On met à jour le timestamp et on renvoie une promesse qui se résout immédiatement (ou après 0ms)
            lastNextFrameTime = now;
          
            return new Promise(resolve => setTimeout(resolve, 0));
          
          } else if (document.getElementById('plus_rapide').checked) {

            return true;
          }
          // Si moins de 500 ms se sont écoulées, on ne renvoie rien (=> undefined, await se résout immédiatement)
        }

        async function updateUI() {
          board.position(gameInstance.fen());

          document.getElementById("value_chessboard").innerText =
            "Valeur de l'échiquier : " + evaluatePosition(gameInstance, false) + " pts.";
          let hybridVal = await evaluatePositionHybrid(gameInstance, false);
          document.getElementById("value_chessboard_hybrid").innerText =
            "Valeur de l'échiquier avec réseau de neurones : " + hybridVal.toFixed(6) + " pts.";
        }

        async function aiMove() {
          if ((gameInstance.game_over()) || (gameInstance.totalMoves >= MAX_TOTAL_MOVES)) {
            await handleGameOver();
            return;
          }
          let ai_move = await chooseMove(gameInstance);
          if (ai_move) {
            const stateTensor = boardToTensor(gameInstance);
            storeMove(gameInstance, ai_move, stateTensor, gameInstance.turn());
            customMove(gameInstance, uciToMove(ai_move));
            if (!is_entrainement) {
              await new Promise(resolve => requestAnimationFrame(resolve));
              await updateUI();
            }
          }
          if (!is_entrainement) {
            await new Promise(resolve => setTimeout(resolve, 100));
          }
          if (mode === "ai") {
            aiTimeoutId = setTimeout(() => {
              requestAnimationFrame(aiMove);
            }, is_entrainement ? 0 : 100);
          
          } else {

            await handleGameOver();
          }
        }

        document.getElementById('resetBtn').addEventListener('click', function() {
          stopAI();
          gameInstance.reset();
          gameInstance.totalMoves = 0;
          board.start();
          mode = "user";
        });
        document.getElementById('start-ai-vs-ai').addEventListener('click', function() {
          mode = "ai";
          stopAI();
          document.getElementById('mode-info').innerText = "Mode : IA entraînée (Noirs) vs IA réfléchissante (Blancs)";
          gameInstance.reset();
          gameInstance.totalMoves = 0;
          board.start();
          aiTimeoutId = setTimeout(aiMove, 100);
        });
      });

      function uciToMove(uci) {
        return {
          from: uci.substring(0, 2),
          to: uci.substring(2, 4),
          promotion: (uci.length > 4 ? uci.substring(4) : undefined)
        };
      }
    </script>
  </body>
</html>
