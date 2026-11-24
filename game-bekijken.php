<?php
// Detailpagina: alle gegevens van één game bekijken

// Het id van de game komt uit de link, bijvoorbeeld: game-bekijken.php?id=3
$id = $_GET['id'] ?? '';

//1. Verbinding
require_once 'config/conn.php';

//2. Query
$query = "SELECT * FROM games WHERE id = :id";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute([
    ":id" => $id
]);

//5. Fetch (één game)
$game = $statement->fetch();

// Gegevens voor de header
if ($game) {
    $paginaTitel = $game['naam'];
} else {
    $paginaTitel = 'Game niet gevonden';
}
$actief = 'games';
require 'includes/header.php';
?>

<a class="terug" href="games.php">&larr; Terug naar games</a>

<?php if (isset($_GET['msg'])) {
    echo "<div class='msg'>" . htmlspecialchars($_GET['msg']) . "</div>";
} ?>

<?php if (!$game) { ?>

    <p class="leeg">Deze game bestaat niet (meer).</p>

<?php } else { ?>

    <?php
    // Als er geen plaatje is, gebruiken we het placeholder-plaatje
    if ($game['afbeelding_url'] == '') {
        $plaatje = 'img/dummypicture.png';
    } else {
        $plaatje = $game['afbeelding_url'];
    }

    // Als er geen genre is, tonen we een streepje
    if ($game['genre'] == '') {
        $genre = '-';
    } else {
        $genre = $game['genre'];
    }

    // Als er geen rating is, tonen we "Geen rating"
    if ($game['rating'] == '') {
        $rating = 'Geen rating';
    } else {
        $rating = $game['rating'] . '/5';
    }
    ?>

    <div class="title-row detail-titel">
        <h1><?php echo htmlspecialchars($game['naam']); ?></h1>
        <div>
            <a class="btn" href="game-bewerken.php?id=<?php echo $game['id']; ?>">Bewerken</a>
            <a class="btn" href="game-verwijderen.php?id=<?php echo $game['id']; ?>">Verwijderen</a>
        </div>
    </div>

    <div class="detail">
        <img class="detail-plaatje" src="<?php echo htmlspecialchars($plaatje); ?>" alt="<?php echo htmlspecialchars($game['naam']); ?>">

        <table class="detail-gegevens">
            <tr>
                <th>Naam game</th>
                <td><?php echo htmlspecialchars($game['naam']); ?></td>
            </tr>
            <tr>
                <th>Platform</th>
                <td><?php echo htmlspecialchars($game['platform']); ?></td>
            </tr>
            <tr>
                <th>Genre</th>
                <td><?php echo htmlspecialchars($genre); ?></td>
            </tr>
            <tr>
                <th>Releasejaar</th>
                <td><?php echo htmlspecialchars($game['releasejaar']); ?></td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="label"><?php echo htmlspecialchars($game['status']); ?></span></td>
            </tr>
            <tr>
                <th>Beoordeling</th>
                <td><?php echo $rating; ?></td>
            </tr>

            <?php if ($game['notitie'] != '') { ?>
                <tr>
                    <th>Notitie</th>
                    <td><?php echo htmlspecialchars($game['notitie']); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>

<?php } ?>

<?php require 'includes/footer.php'; ?>
