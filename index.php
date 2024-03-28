<?php
session_start();

// Include the game logic file
require_once 'blackjack_logic.php';

// Initialize or reset the game
if (!isset($_SESSION['game_started']) || isset($_POST['reset'])) {
    initializeGame();
}

// Handle user actions
if (isset($_POST['action'])) {
    handleUserAction($_POST['action']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blackjack Game</title>
    <!-- Add styles or links to stylesheets here -->
</head>
<body>
    <h1>PHP Blackjack</h1>

    <?php if ($_SESSION['game_over']) : ?>
        <p>Game over! You <?php echo $_SESSION['game_result']; ?>.</p>
        <form method="post">
            <input type="submit" name="reset" value="Play Again">
        </form>
    <?php else: ?>
        <div>
            <h2>Your Hand (<?php echo getHandValue($_SESSION['player_hand']); ?>):</h2>
            <?php echo displayHand($_SESSION['player_hand']); ?>
        </div>
        <div>
            <h2>Dealer's Hand:</h2>
            <?php echo displayHand($_SESSION['dealer_hand'], $_SESSION['dealer_show_card']); ?>
        </div>
        <form method="post">
            <input type="submit" name="action" value="hit" <?php echo $_SESSION['player_stands'] ? 'disabled' : ''; ?>>
            <input type="submit" name="action" value="stand">
        </form>
    <?php endif; ?>
</body>
</html>
