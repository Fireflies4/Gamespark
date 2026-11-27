<?php
// Homepage van GameSpark

//1. Verbinding
require_once 'config/conn.php';

// Alle statussen ophalen, zodat we kunnen tellen hoeveel games er per status zijn
$query = "SELECT status FROM games";
$statement = $pdo->prepare($query);
$statement->execute([]);
$alleGames = $statement->fetchAll();

$totaal = count($alleGames);
$speeltNu = 0;
$wishlist = 0;
$uitgespeeld = 0;

foreach ($alleGames as $game) {
    if ($game['status'] == 'Speelt nu') {
        $speeltNu++;
    }
    if ($game['status'] == 'Wishlist') {
        $wishlist++;
    }
    if ($game['status'] == 'Uitgespeeld') {
        $uitgespeeld++;
    }
}

// De 4 nieuwste games ophalen
$query = "SELECT * FROM games ORDER BY id DESC LIMIT 4";
$statement = $pdo->prepare($query);
$statement->execute([]);
$games = $statement->fetchAll();

// Gegevens voor de header
$paginaTitel = 'Home';
$actief = 'home';
require 'includes/header.php';
?>

<section class="home-hero">
    <div class="home-hero-tekst">
        <span class="home-label">Jouw gamecollectie</span>
        <h1>Al je games op één plek.</h1>
        <p>Houd bij wat je speelt, wat je hebt uitgespeeld en wat er nog op je wishlist staat. Geef elke game een rating en ontdek nieuwe favorieten.</p>

        <div class="home-knoppen">
            <a class="btn btn-primary" href="formulier.php">+ Game toevoegen</a>
            <a class="btn" href="games.php">Bekijk alle games</a>
        </div>
    </div>

    <div class="home-hero-logo">
        <img src="img/gamesparklogo.png" alt="GameSpark logo">
    </div>
</section>

<section class="stats">
    <div class="stat">
        <span class="stat-getal"><?php echo $totaal; ?></span>
        <span class="stat-label">Games in totaal</span>
    </div>
    <div class="stat">
        <span class="stat-getal"><?php echo $speeltNu; ?></span>
        <span class="stat-label">Speelt nu</span>
    </div>
    <div class="stat">
        <span class="stat-getal"><?php echo $wishlist; ?></span>
        <span class="stat-label">Op de wishlist</span>
    </div>
    <div class="stat">
        <span class="stat-getal"><?php echo $uitgespeeld; ?></span>
        <span class="stat-label">Uitgespeeld</span>
    </div>
</section>

<div class="sectie-kop">
    <h2>Nieuw toegevoegd</h2>
    <a href="games.php">Bekijk alle games &rarr;</a>
</div>

<?php if (count($games) == 0) { ?>

    <p class="leeg">Er staan nog geen games in de lijst.</p>

<?php } else { ?>

    <div class="game-kaarten">
        <?php foreach ($games as $game) { ?>

            <?php
            // Als er geen plaatje is, gebruiken we het placeholder-plaatje
            if ($game['afbeelding_url'] == '') {
                $plaatje = 'img/dummypicture.png';
            } else {
                $plaatje = $game['afbeelding_url'];
            }

            // Als er geen rating is, tonen we een streepje
            if ($game['rating'] == '') {
                $rating = '-';
            } else {
                $rating = $game['rating'] . '/5';
            }

            // Class voor de kleur van het status-label: "In bezit" wordt "label-in-bezit"
            $statusClass = 'label-' . str_replace(' ', '-', strtolower($game['status']));
            ?>

            <a class="game-kaart" href="game-bekijken.php?id=<?php echo $game['id']; ?>">
                <img src="<?php echo htmlspecialchars($plaatje); ?>" alt="<?php echo htmlspecialchars($game['naam']); ?>">
                <div class="game-kaart-info">
                    <span class="game-kaart-naam"><?php echo htmlspecialchars($game['naam']); ?></span>
                    <span class="game-kaart-platform"><?php echo htmlspecialchars($game['platform']); ?></span>
                    <div class="game-kaart-onder">
                        <span class="label <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($game['status']); ?></span>
                        <span class="game-kaart-rating">&#9733; <?php echo $rating; ?></span>
                    </div>
                </div>
            </a>

        <?php } ?>
    </div>

<?php } ?>

<?php require 'includes/footer.php'; ?>
