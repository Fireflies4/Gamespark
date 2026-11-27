<?php

$id = $_GET['id'] ?? null;

if (is_numeric($id)) {
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

    if (!$game) {
        echo "Deze game bestaat niet.";
        exit();
    }

    $paginaTitel = 'Game verwijderen';
    $actief = 'games';
    require 'includes/header.php';
    ?>

    <a class="terug" href="game-bekijken.php?id=<?php echo $id; ?>">&larr; Terug naar game</a>

    <div class="hero">
        <h1>Game verwijderen</h1>
        <p> Weet je zeker dat je <strong><?php echo htmlspecialchars($game['naam']); ?> (<?php echo $game['releasedatum']; ?>)</strong> wilt verwijderen? Dit kan niet ongedaan worden.</p>

        <form action="<?php echo $base_url; ?>/app/Http/Controllers/gamesController.php" method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <button type="submit" class="btn btn-primary">Ja, verwijderen</button>
            <a class="btn" href="game-bekijken.php?id=<?php echo $id; ?>">Annuleren</a>
        </form>
    </div>

    <?php
    require 'includes/footer.php';
}
else {
    echo "Ongeldige game ID.";
}
?>
