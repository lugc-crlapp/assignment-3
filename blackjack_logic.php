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

function createDeck() {
    $suits = ['Hearts', 'Diamonds', 'Clubs', 'Spades'];
    $values = [
        'Two' => 2, 'Three' => 3, 'Four' => 4, 'Five' => 5, 
        'Six' => 6, 'Seven' => 7, 'Eight' => 8, 'Nine' => 9, 
        'Ten' => 10, 'Jack' => 10, 'Queen' => 10, 'King' => 10, 'Ace' => 11
    ];
    $deck = [];
    foreach ($suits as $suit) {
        foreach ($values as $value => $points) {
            $deck[] = ['value' => $value, 'suit' => $suit, 'points' => $points];
        }
    }
    shuffle($deck);
    return $deck;
}

function dealCard(&$deck) {
    return array_shift($deck);
}

function getHandValue($hand) {
    $value = 0;
    $aces = 0;
    foreach ($hand as $card) {
        $value += $card['points'];
        if ($card['value'] == 'Ace') {
            $aces++;
        }
    }
    while ($value > 21 && $aces > 0) {
        $value -= 10;
        $aces--;
    }
    return $value;
}

function initializeGame() {
    $_SESSION['deck'] = createDeck();
    $_SESSION['player_hand'] = [dealCard($_SESSION['deck']), dealCard($_SESSION['deck'])];
    $_SESSION['dealer_hand'] = [dealCard($_SESSION['deck']), dealCard($_SESSION['deck'])];
    $_SESSION['dealer_show_card'] = false; // Hide dealer's first card
    $_SESSION['game_started'] = true;
    $_SESSION['game_over'] = false;
    $_SESSION['player_stands'] = false;
    $_SESSION['game_result'] = '';
}

function handleUserAction($action) {
    if ($action === 'hit' && !$_SESSION['player_stands']) {
        $_SESSION['player_hand'][] = dealCard($_SESSION['deck']);
        if (getHandValue($_SESSION['player_hand']) > 21) {
            $_SESSION['game_over'] = true;
            $_SESSION['game_result'] = 'lost';
        }
    } elseif ($action === 'stand') {
        $_SESSION['player_stands'] = true;
        playDealerHand();
    }
}

function playDealerHand() {
    $_SESSION['dealer_show_card'] = true; // Reveal dealer's first card
    while (getHandValue($_SESSION['dealer_hand']) < 17) {
        $_SESSION['dealer_hand'][] = dealCard($_SESSION['deck']);
    }
    endGame();
}

function endGame() {
    $playerValue = getHandValue($_SESSION['player_hand']);
    $dealerValue = getHandValue($_SESSION['dealer_hand']);

    if ($playerValue > 21 || ($dealerValue <= 21 && $dealerValue > $playerValue)) {
        $_SESSION['game_result'] = 'lost';
    } elseif ($playerValue == $dealerValue) {
        $_SESSION['game_result'] = 'tied';
    } else {
        $_SESSION['game_result'] = 'won';
    }
    $_SESSION['game_over'] = true;
}

function displayHand($hand, $hideFirstCard = false) {
    $output = '<ul>';
    foreach ($hand as $index => $card) {
        if ($hideFirstCard && $index == 0) {
            $output .= '<li>Hidden Card</li>';
        } else {
            $output .= "<li>{$card['value']} of {$card['suit']}</li>";
        }
    }
    $output .= '</ul>';
    return $output;
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
