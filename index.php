<?php
// Homepage van GameSpark

// Verbinding met de database
require_once 'db.php';

// Tel hoeveel games er in de database staan
$stmt = $pdo->query('SELECT COUNT(*) FROM games');
$totaal = $stmt->fetchColumn();

// Haal de 4 nieuwste games op
$stmt = $pdo->query('SELECT * FROM games ORDER BY id DESC LIMIT 4');
$games = $stmt->fetchAll();

// Gegevens voor de header
$paginaTitel = 'Home';
$actief = 'home';
require 'includes/header.php';
?>

<div class="hero">
    <h1>Welkom bij GameSpark</h1>
    <p>Houd al je games bij op één plek: wat je speelt, wat je hebt uitgespeeld en wat er nog op je wishlist staat.</p>
    <p>Er staan op dit moment <strong><?php echo $totaal; ?></strong> games in de lijst.</p>

    <a class="btn btn-primary" href="formulier.php">+ Game toevoegen</a>
    <a class="btn" href="games.php">Bekijk alle games</a>
</div>

<h2>Nieuw toegevoegd</h2>

<?php if (count($games) == 0) { ?>

    <p class="leeg">Er staan nog geen games in de lijst.</p>

<?php } else { ?>

    <div class="kaarten">
        <?php foreach ($games as $game) { ?>

            <?php
            // Als er geen plaatje is, gebruiken we het placeholder-plaatje
            if ($game['afbeelding_url'] == '') {
                $plaatje = 'img/placeholder.svg';
            } else {
                $plaatje = $game['afbeelding_url'];
            }

            // Als er geen rating is, tonen we "Geen rating"
            if ($game['rating'] == '') {
                $rating = 'Geen rating';
            } else {
                $rating = $game['rating'] . '/5';
            }
            ?>

            <div class="kaart">
                <img src="<?php echo htmlspecialchars($plaatje); ?>" alt="<?php echo htmlspecialchars($game['naam']); ?>">
                <div class="kaart-tekst">
                    <strong><?php echo htmlspecialchars($game['naam']); ?></strong>
                    <span><?php echo htmlspecialchars($game['platform']); ?></span>
                    <span><?php echo htmlspecialchars($game['status']); ?></span>
                    <span><?php echo $rating; ?></span>
                </div>
            </div>

        <?php } ?>
    </div>

<?php } ?>

<?php require 'includes/footer.php'; ?>
