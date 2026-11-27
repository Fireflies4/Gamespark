<?php

$action = $_POST['action'] ?? '';

if ($action == 'edit') {

    if (is_numeric($_POST['id'] ?? null)) {
        $id = $_POST['id'];
    } else {
        echo "Je hebt geen geldige id meegegeven";
        exit();
    }

    //Variabelen vullen
    $naam     = trim($_POST['naam'] ?? '');
    $gekozen  = $_POST['platform'] ?? [];
    $gekozenGenres = $_POST['genre'] ?? [];
    $releasedatum     = trim($_POST['releasedatum'] ?? '');
    $status   = $_POST['status'] ?? '';
    $rating   = $_POST['rating'] ?? '';
    $url      = trim($_POST['afbeelding_url'] ?? '');
    $notities = trim($_POST['notities'] ?? '');
    $aangeraden_door = trim($_POST['aangeraden_door'] ?? '');

    $allowed_platformen = ['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Nintendo Switch 2', 'Mobiel', 'Anders'];
    $allowed_statussen  = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];
    $allowed_ratings    = ['1', '2', '3', '4', '5'];
    $allowed_plaatjes   = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $maxJaar = date('Y') + 2;

    // Controleren of alles goed is ingevuld. Fouten komen in deze lijst.
    $fouten = [];

    if ($naam == '' || strlen($naam) > 100) {
        $fouten[] = "Vul een naam in (maximaal 100 tekens).";
    }

    if (!is_array($gekozen) || count($gekozen) == 0) {
        $fouten[] = "Kies minstens één platform.";
        $gekozen = [];
    }
    foreach ($gekozen as $platform) {
        if (!in_array($platform, $allowed_platformen)) {
            $fouten[] = "Kies een geldig platform.";
        }
    }
    $platform = implode(', ', $gekozen); // ['PC', 'Mobiel'] wordt "PC, Mobiel"

    $allowed_genres = ['Actie', 'Actie-avontuur', 'Avontuur', 'Battle royale', 'Educatief', 'Horror', 'Metroidvania', 'MMO', 'Open wereld', 'Party', 'Platformer', 'Puzzel', 'Racen', 'Ritme', 'Roguelike', 'RPG', 'Sandbox', 'Shooter', 'Simulatie', 'Sport', 'Strategie', 'Survival', 'Vechtspel', 'Visual novel', 'Anders'];

    if (!is_array($gekozenGenres)) {
        $gekozenGenres = [];
    }
    foreach ($gekozenGenres as $gekozenGenre) {
        if (!in_array($gekozenGenre, $allowed_genres)) {
            $fouten[] = "Kies alleen geldige genres.";
        }
    }
    $genre = implode(', ', $gekozenGenres); // ['RPG', 'Actie'] wordt "RPG, Actie"

    if ($releasedatum == '') {
        $fouten[] = "vul een releasedatum in.";
    }

    if (!in_array($status, $allowed_statussen)) {
        $fouten[] = "Kies een geldige status.";
    }

    if ($rating != '' && !in_array($rating, $allowed_ratings)) {
        $fouten[] = "De rating moet tussen 1 en 5 liggen.";
    }

    if (strlen($notities) > 280) {
        $fouten[] = "De notities mogen maximaal 280 tekens zijn.";
    }

    if (strlen($aangeraden_door) > 80) {
        $fouten[] = "Aangeraden door mag maximaal 80 tekens zijn.";
    }

    if ($url != '' && (strlen($url) > 255 || !preg_match('#^https?://#i', $url))) {
        $fouten[] = "Vul een geldige link in (begint met http:// of https://, max 255 tekens).";
    }

    // Nieuwe afbeelding geüpload? Dan controleren of het een plaatje van max 2 MB is.
    $upload = $_FILES['afbeelding'] ?? null;
    $nieuwePlaatje = false;
    $mime = '';

    if ($upload && $upload['error'] == UPLOAD_ERR_OK) {
        $mime = mime_content_type($upload['tmp_name']);
        if (isset($allowed_plaatjes[$mime]) && $upload['size'] <= 2 * 1024 * 1024) {
            $nieuwePlaatje = true;
        } else {
            $fouten[] = "Upload een JPG, PNG, WEBP of GIF van maximaal 2 MB.";
        }
    } elseif ($upload && $upload['error'] != UPLOAD_ERR_NO_FILE) {
        $fouten[] = "Het uploaden van de afbeelding is mislukt.";
    }

    // Bestaat er al een ANDERE game met dezelfde naam en dezelfde releasedatum? (Model vult $aantalDubbel)
    $modelActie = 'dubbel';
    require __DIR__.'/../../models/gamesModel.php';

    if ($aantalDubbel > 0) {
        $fouten[] = "Er staat al een andere game met dezelfde naam en dezelfde releasedatum in de lijst.";
    }

    // Zijn er fouten? Dan terug naar het formulier met de foutmelding.
    if (count($fouten) > 0) {
        header('Location: ' . $base_url . '/game-bewerken.php?id=' . $id . '&msg=' . urlencode(implode(' ', $fouten)));
        exit();
    }

    // De huidige afbeelding ophalen, die houden we als er niks nieuws is gekozen (Model vult $afbeelding)
    $modelActie = 'afbeelding';
    require __DIR__.'/../../models/gamesModel.php';

    if ($nieuwePlaatje) {
        // Het plaatje opslaan in img/uploads met een willekeurige naam
        $bestand = 'img/uploads/' . bin2hex(random_bytes(16)) . '.' . $allowed_plaatjes[$mime];
        @mkdir('../../../img/uploads', 0755, true);
        move_uploaded_file($upload['tmp_name'], '../../../' . $bestand);
        $afbeelding = $bestand;
    } elseif ($url != '') {
        $afbeelding = $url;
    }

    // Lege velden slaan we op als NULL (leeg in de database)
    if ($genre == '') {
        $genre = null;
    }
    if ($rating == '') {
        $rating = null;
    }
    if ($notities == '') {
        $notities = null;
    }
    if ($aangeraden_door == '') {
        $aangeraden_door = null;
    }

    // Opslaan via het Model
    $modelActie = 'edit';
    require __DIR__.'/../../models/gamesModel.php';

    header('Location: ' . $base_url . '/game-bekijken.php?id=' . $id . '&msg=' . urlencode('De game is bewerkt'));
    exit();
}

if ($action == 'delete') {

    if (is_numeric($_POST['id'] ?? null)) {
        $id = $_POST['id'];
    } else {
        echo "Je hebt geen geldige id meegegeven";
        exit();
    }

    // Eerst de afbeelding van de game ophalen (Model vult $afbeelding)
    $modelActie = 'afbeelding';
    require __DIR__.'/../../models/gamesModel.php';

    // Is het een geüpload bestand (en geen link)? Dan het bestand ook weghalen.
    if ($afbeelding && str_starts_with($afbeelding, 'img/uploads/')) {
        @unlink('../../../' . $afbeelding);
    }

    // Verwijderen via het Model
    $modelActie = 'delete';
    require __DIR__.'/../../models/gamesModel.php';

    header('Location: ' . $base_url . '/games.php?msg=' . urlencode('De game is verwijderd'));
    exit();
}
