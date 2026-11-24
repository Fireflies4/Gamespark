<?php
session_start();

// CSRF-token aanmaken (beveiliging van het formulier)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$platformen = ['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Nintendo Switch 2', 'Mobiel', 'Anders'];
$statussen  = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];

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

    // De platforms staan als "PC, Mobiel" in de database. Dit maakt er een lijst van.
    $gekozen = explode(', ', $game['platform']);

    // Huidige afbeelding, of het dummy-plaatje als er geen is
    if ($game['afbeelding_url'] == '') {
        $plaatje = 'img/dummypicture.png';
    } else {
        $plaatje = $game['afbeelding_url'];
    }

    $paginaTitel = 'Game bewerken';
    $actief = 'games';
    require 'includes/header.php';
    ?>

    <div class="title-row">
        <h1>Game bewerken</h1>
        <a class="btn" href="game-bekijken.php?id=<?php echo $id; ?>">&larr; Terug naar game</a>
    </div>

    <?php if (isset($_GET['msg'])) {
        echo "<div class='melding melding-fout'>" . htmlspecialchars($_GET['msg']) . "</div>";
    } ?>

    <form action="<?php echo $base_url; ?>/app/Http/Controllers/gamesController.php" method="POST" enctype="multipart/form-data" class="formulier">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="veld">
            <label for="naam">Naam *</label>
            <input type="text" name="naam" id="naam" value="<?php echo htmlspecialchars($game['naam']); ?>">
        </div>

        <div class="veld">
            <span class="veld-titel">Platforms *</span>
            <div class="platform-lijst">
                <?php foreach ($platformen as $platform) { ?>
                    <label>
                        <input type="checkbox" name="platform[]" value="<?php echo $platform; ?>"
                            <?php if (in_array($platform, $gekozen)) { echo "checked"; } ?>>
                        <?php echo $platform; ?>
                    </label>
                <?php } ?>
            </div>
        </div>

        <div class="veld">
            <label for="genre">Genre</label>
            <input type="text" name="genre" id="genre" value="<?php echo htmlspecialchars($game['genre'] ?? ''); ?>">
        </div>

        <div class="veld">
            <label for="jaar">Releasejaar *</label>
            <input type="number" name="jaar" id="jaar" value="<?php echo $game['releasejaar']; ?>">
        </div>

        <div class="veld">
            <label for="status">Status *</label>
            <select name="status" id="status">
                <?php foreach ($statussen as $status) { ?>
                    <option value="<?php echo $status; ?>" <?php if ($game['status'] == $status) { echo "selected"; } ?>>
                        <?php echo $status; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="veld">
            <label for="rating">Rating (1 t/m 5)</label>
            <select name="rating" id="rating">
                <option value="">Geen rating</option>
                <?php for ($i = 1; $i <= 5; $i++) { ?>
                    <option value="<?php echo $i; ?>" <?php if ($game['rating'] == $i) { echo "selected"; } ?>>
                        <?php echo $i; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="veld">
            <span class="veld-titel">Huidige afbeelding</span>
            <img class="thumb" src="<?php echo htmlspecialchars($plaatje); ?>" alt="Huidige afbeelding">

            <label for="afbeelding">Nieuwe afbeelding uploaden (max 2 MB)</label>
            <input type="file" name="afbeelding" id="afbeelding" accept="image/*">

            <label for="afbeelding_url">Of een link naar een afbeelding</label>
            <input type="url" name="afbeelding_url" id="afbeelding_url" placeholder="https://...">
        </div>

        <div class="veld">
            <label for="notities">Notities</label>
            <textarea name="notities" id="notities" rows="4"><?php echo htmlspecialchars($game['notitie'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Wijzigingen opslaan</button>
        <a class="btn" href="game-bekijken.php?id=<?php echo $id; ?>">Annuleren</a>
    </form>

    <?php
    require 'includes/footer.php';
}
else {
    echo "Ongeldige game ID.";
}
?>
