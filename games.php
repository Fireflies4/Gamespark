<?php
// Overzichtspagina: alle games in een tabel, met filters

//1. Verbinding
require_once 'config/conn.php';

$platformen = ['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Nintendo Switch 2', 'Mobiel', 'Anders'];
$statussen  = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];
$ratings    = ['1', '2', '3', '4', '5'];

// Alle genres die in de database staan (voor de genre-filter)
$query = "SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL AND genre != '' ORDER BY genre";
$statement = $pdo->prepare($query);
$statement->execute([]);
$genres = $statement->fetchAll(PDO::FETCH_COLUMN);

// De gekozen filters uit de link halen, bijvoorbeeld: games.php?status=Wishlist&rating_van=3
$filterPlatform  = $_GET['platform'] ?? '';
$filterGenre     = $_GET['genre'] ?? '';
$filterStatus    = $_GET['status'] ?? '';
$filterRatingVan = $_GET['rating_van'] ?? '';
$filterRatingTot = $_GET['rating_tot'] ?? '';

// Alleen waardes uit onze eigen lijsten gebruiken, de rest negeren we
if (!in_array($filterPlatform, $platformen)) {
    $filterPlatform = '';
}
if (!in_array($filterGenre, $genres)) {
    $filterGenre = '';
}
if (!in_array($filterStatus, $statussen)) {
    $filterStatus = '';
}
if (!in_array($filterRatingVan, $ratings)) {
    $filterRatingVan = '';
}
if (!in_array($filterRatingTot, $ratings)) {
    $filterRatingTot = '';
}

// "Van" hoger dan "tot"? Dan draaien we ze om (van 5 tot 2 wordt van 2 tot 5)
if ($filterRatingVan != '' && $filterRatingTot != '' && $filterRatingVan > $filterRatingTot) {
    $tijdelijk = $filterRatingVan;
    $filterRatingVan = $filterRatingTot;
    $filterRatingTot = $tijdelijk;
}

//2. Query: we beginnen met alle games en plakken er per gekozen filter een voorwaarde aan
$query = "SELECT id, naam, afbeelding_url, platform, status, rating, notitie FROM games WHERE 1 = 1";
$parameters = [];

if ($filterPlatform != '') {
    // Platform staat als "PC, Mobiel" in de database. FIND_IN_SET zoekt een exact platform in die lijst,
    // zodat "Nintendo Switch" niet ook "Nintendo Switch 2" vindt.
    $query .= " AND FIND_IN_SET(:platform, REPLACE(platform, ', ', ','))";
    $parameters[':platform'] = $filterPlatform;
}
if ($filterGenre != '') {
    $query .= " AND genre = :genre";
    $parameters[':genre'] = $filterGenre;
}
if ($filterStatus != '') {
    $query .= " AND status = :status";
    $parameters[':status'] = $filterStatus;
}
if ($filterRatingVan != '') {
    $query .= " AND rating >= :rating_van";
    $parameters[':rating_van'] = $filterRatingVan;
}
if ($filterRatingTot != '') {
    $query .= " AND rating <= :rating_tot";
    $parameters[':rating_tot'] = $filterRatingTot;
}

$query .= " ORDER BY naam";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute($parameters);

//5. Fetchall
$games = $statement->fetchAll();

// Is er minstens één filter gekozen?
$filterActief = count($parameters) > 0;

$paginaTitel = 'Games';
$actief = 'games';
require __DIR__ . '/includes/header.php';
?>

<div class="title-row">
    <h1>Games</h1>
    <a class="btn btn-primary" href="formulier.php">+ Game toevoegen</a>
</div>

<?php if (isset($_GET['msg'])) {
    echo "<div class='msg'>" . htmlspecialchars($_GET['msg']) . "</div>";
} ?>

<!-- Klik op "Filters" om de filters te openen. Staat er een filter aan? Dan blijft hij open. -->
<details class="filter-uitklap" <?php if ($filterActief) { echo "open"; } ?>>
<summary class="btn filter-knop">
    <img src="img/filtericon.png" alt="" class="filter-icoon">
    Filters
</summary>

<form method="GET" action="games.php" class="filters">
    <div class="filter-veld">
        <label for="platform">Platform</label>
        <select name="platform" id="platform">
            <option value="">Alle platforms</option>
            <?php foreach ($platformen as $platform) { ?>
                <option value="<?php echo $platform; ?>" <?php if ($filterPlatform == $platform) { echo "selected"; } ?>><?php echo $platform; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-veld">
        <label for="genre">Genre</label>
        <select name="genre" id="genre">
            <option value="">Alle genres</option>
            <?php foreach ($genres as $genre) { ?>
                <option value="<?php echo htmlspecialchars($genre); ?>" <?php if ($filterGenre == $genre) { echo "selected"; } ?>><?php echo htmlspecialchars($genre); ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-veld">
        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="">Alle statussen</option>
            <?php foreach ($statussen as $status) { ?>
                <option value="<?php echo $status; ?>" <?php if ($filterStatus == $status) { echo "selected"; } ?>><?php echo $status; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-veld">
        <label for="rating_van">Rating van</label>
        <select name="rating_van" id="rating_van">
            <option value="">-</option>
            <?php foreach ($ratings as $rating) { ?>
                <option value="<?php echo $rating; ?>" <?php if ($filterRatingVan == $rating) { echo "selected"; } ?>><?php echo $rating; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-veld">
        <label for="rating_tot">Rating tot</label>
        <select name="rating_tot" id="rating_tot">
            <option value="">-</option>
            <?php foreach ($ratings as $rating) { ?>
                <option value="<?php echo $rating; ?>" <?php if ($filterRatingTot == $rating) { echo "selected"; } ?>><?php echo $rating; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="filter-knoppen">
        <button type="submit" class="btn btn-primary">Filteren</button>
        <a class="btn" href="games.php">Wis filters</a>
    </div>
</form>
</details>

<?php if ($filterActief) { ?>
    <p class="filter-aantal"><?php echo count($games); ?> game(s) gevonden met deze filters.</p>
<?php } ?>

<?php if (count($games) === 0 && $filterActief): ?>
    <p class="leeg">Geen games gevonden met deze filters. <a href="games.php">Wis de filters</a> om alle games te zien.</p>
<?php elseif (count($games) === 0): ?>
    <p class="leeg">Er staan nog geen games in de lijst.</p>
<?php else: ?>
    <table class="games">
        <thead>
            <tr>
                <th>Afbeelding</th>
                <th>Naam</th>
                <th>Platform</th>
                <th>Status</th>
                <th>Rating</th>
                <th>Korte notities</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($games as $game): ?>
                <tr>
                    <td>
                        <?php
                        // Geen plaatje? Dan tonen we het placeholder-icoon
                        $plaatje = $game['afbeelding_url'] ?: 'img/dummypicture.png';
                        ?>
                        <img class="thumb" src="<?= htmlspecialchars($plaatje) ?>" alt="Afbeelding van <?= htmlspecialchars($game['naam']) ?>"
                             onerror="this.onerror=null; this.src='img/dummypicture.png';">
                    </td>
                    <td><?= htmlspecialchars($game['naam']) ?></td>
                    <td><?= htmlspecialchars($game['platform']) ?></td>
                    <td><?= htmlspecialchars($game['status']) ?></td>
                    <td><?= $game['rating'] === null ? '-' : (int) $game['rating'] . '/5' ?></td>
                    <td><?= htmlspecialchars($game['notitie'] ?? '') ?></td>
                    <td>
                        <a class="btn btn-small" href="game-bekijken.php?id=<?= (int) $game['id'] ?>">Bekijken</a>
                        <a class="btn btn-small" href="game-bewerken.php?id=<?= (int) $game['id'] ?>">Bewerken</a>
                        <a class="btn btn-small" href="game-verwijderen.php?id=<?= (int) $game['id'] ?>">Verwijderen</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>