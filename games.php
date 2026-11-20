<?php
// Overzichtspagina: alle games in een tabel
require_once __DIR__ . '/db.php';

// Alle games ophalen, alfabetisch op naam
$stmt = $pdo->query('SELECT id, naam, afbeelding_url, platform, status, rating, notitie FROM games ORDER BY naam');
$games = $stmt->fetchAll();

$paginaTitel = 'Games';
$actief = 'games';
require __DIR__ . '/includes/header.php';
?>

<div class="title-row">
    <h1>Games</h1>
    <a class="btn btn-primary" href="formulier.php">+ Game toevoegen</a>
</div>

<?php if (count($games) === 0): ?>
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