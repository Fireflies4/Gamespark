<?php
// Formulier: nieuwe game toevoegen
require_once __DIR__ . '/db.php';
session_start();

function e($tekst) {
    return htmlspecialchars($tekst, ENT_QUOTES, 'UTF-8');
}

$statussen  = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];
$platformen = ['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Nintendo Switch 2', 'Mobiel', 'Anders'];
$toegestaan = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$maxJaar    = date('Y') + 2; // huidig jaar + 2, zodat aankomende games ook kunnen

// CSRF-token aanmaken
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$fouten = [];
$naam = $platform = $genre = $releasedatum = $status = $rating = $url = $notities = $aangeraden_door = '';
$gekozen = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF controleren
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        exit('Ongeldig formulier. Ga terug en probeer het opnieuw.');
    }

    $naam     = trim($_POST['naam'] ?? '');
    $gekozen  = $_POST['platform'] ?? [];
    if (!is_array($gekozen)) {
        $gekozen = [];
    }
    $genre    = trim($_POST['genre'] ?? '');
    $releasedatum     = trim($_POST['releasedatum'] ?? '');
    $status   = $_POST['status'] ?? '';
    $rating   = $_POST['rating'] ?? '';
    $url      = trim($_POST['afbeelding_url'] ?? '');
    $notities = trim($_POST['notities'] ?? '');
    $aangeraden_door = trim($_POST['aangeraden_door'] ?? '');

    // Alleen naam, platform, genre en releasedatum ingevuld? Dan wordt de status automatisch Wishlist
    $geenUpload = !isset($_FILES['afbeelding']) || $_FILES['afbeelding']['error'] === UPLOAD_ERR_NO_FILE;
    if ($status === '' && $genre !== '' && $rating === '' && $url === '' && $notities === '' && $geenUpload) {
        $status = 'Wishlist';
    }

    // Validatie
    if ($naam === '' || strlen($naam) > 100) {
        $fouten[] = 'Vul een naam in (maximaal 100 tekens).';
    }
    if (!$gekozen || array_diff($gekozen, $platformen)) {
        $fouten[] = 'Kies minstens één geldig platform.';
    }
    $platform = implode(', ', $gekozen);
    if (strlen($genre) > 40) {
        $fouten[] = 'Het genre mag maximaal 40 tekens zijn.';
    }
    if ($releasedatum == '') {
        $fouten[] = "Vul een releasedatum in.";
    }
    if (!in_array($status, $statussen)) {
        $fouten[] = 'Kies een geldige status.';
    }
    if ($rating !== '' && !in_array($rating, ['1', '2', '3', '4', '5'])) {
        $fouten[] = 'De rating moet tussen 1 en 5 liggen.';
    }
    if (strlen($notities) > 280) {
        $fouten[] = 'De notities mogen maximaal 280 tekens zijn.';
    }
    if (strlen($aangeraden_door) > 80) {
        $fouten[] = 'Aangeraden door mag maximaal 80 tekens zijn.';
    }
    if (strlen($url) > 255) {
        $fouten[] = 'De link mag maximaal 255 tekens zijn.';
    }

    // Afbeelding: upload heeft voorrang op de link
    $afbeelding = $url ?: null;
    $upload = $_FILES['afbeelding'] ?? null;

    if ($upload && $upload['error'] !== UPLOAD_ERR_NO_FILE) {
        $mime = $upload['error'] === UPLOAD_ERR_OK ? mime_content_type($upload['tmp_name']) : '';
        if (!isset($toegestaan[$mime]) || $upload['size'] > 2 * 1024 * 1024) {
            $fouten[] = 'Upload een JPG, PNG, WEBP of GIF van maximaal 2 MB.';
        }
    } elseif ($url !== '' && !preg_match('#^https?://#i', $url)) {
        $fouten[] = 'Vul een geldige link in (begint met http:// of https://).';
    }

    // Dubbele game (zelfde naam en releasedatum)
    if (!$fouten) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM games WHERE naam = ? AND releasedatum = ?');
        $stmt->execute([$naam, $releasedatum]);
        if ($stmt->fetchColumn() > 0) {
            $fouten[] = 'Deze game (zelfde naam en releasedatum) staat al in de lijst.';
        }
    }

    // Opslaan
    if (!$fouten && $upload && $upload['error'] === UPLOAD_ERR_OK) {
        @mkdir(__DIR__ . '/img/uploads', 0755, true);
        $bestand = 'img/uploads/' . bin2hex(random_bytes(16)) . '.' . $toegestaan[$mime];

        // Alleen opslaan als het bestand echt verplaatst is
        if (move_uploaded_file($upload['tmp_name'], __DIR__ . '/' . $bestand)) {
            $afbeelding = $bestand;
        } else {
            $fouten[] = 'De afbeelding kon niet worden opgeslagen. Probeer het opnieuw.';
        }
    }

    if (!$fouten) {
        $stmt = $pdo->prepare(
            'INSERT INTO games (naam, afbeelding_url, platform, genre, releasedatum, status, rating, notitie, aangeraden_door)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$naam, $afbeelding, $platform, $genre ?: null, $releasedatum, $status, $rating ?: null, $notities ?: null, $aangeraden_door ?: null]);

        unset($_SESSION['csrf_token']);
        header('Location: games.php');
        exit;
    }
}

$paginaTitel = 'Game toevoegen';
$actief = 'games';
require __DIR__ . '/includes/header.php';
?>

<div class="title-row">
    <h1>Game toevoegen</h1>
    <a class="btn" href="games.php">&larr; Terug naar overzicht</a>
</div>

<?php if ($fouten): ?>
    <div class="melding melding-fout">
        <ul>
            <?php foreach ($fouten as $fout): ?>
                <li><?= e($fout) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="formulier">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

    <div class="veld">
        <label for="naam">Naam *</label>
        <input type="text" id="naam" name="naam" value="<?= e($naam) ?>">
    </div>

    <div class="veld">
        <span class="veld-titel">Platforms *</span>
        <details class="multiselect" id="platform-kiezer">
            <summary>-- Kies platforms --</summary>
            <div class="opties">
                <?php foreach ($platformen as $p): ?>
                    <label>
                        <input type="checkbox" name="platform[]" value="<?= e($p) ?>"
                            <?= in_array($p, $gekozen) ? 'checked' : '' ?>>
                        <?= e($p) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
    </div>

    <script>
    // Toont de gekozen platforms in de balk en sluit de lijst bij een klik erbuiten
    (function () {
        var kiezer = document.getElementById('platform-kiezer');
        var titel = kiezer.querySelector('summary');
        var vinkjes = kiezer.querySelectorAll('input[type="checkbox"]');

        function bijwerken() {
            var namen = [];
            vinkjes.forEach(function (v) { if (v.checked) namen.push(v.value); });
            titel.textContent = namen.length ? namen.join(', ') : '-- Kies platforms --';
        }

        vinkjes.forEach(function (v) { v.addEventListener('change', bijwerken); });
        document.addEventListener('click', function (ev) {
            if (!kiezer.contains(ev.target)) kiezer.removeAttribute('open');
        });
        bijwerken();
    })();
    </script>

    <div class="veld">
        <label for="genre">Genre</label>
        <input type="text" id="genre" name="genre" value="<?= e($genre) ?>">
    </div>

    <div class="veld">
        <label for="releasedatum">Releasedatum *</label>
        <input type="date" id="releasedatum" name="releasedatum" value="<?= e($releasedatum) ?>">
    </div>

    <div class="veld">
        <label for="status">Status *</label>
        <select id="status" name="status">
            <option value="">-- Kies een status --</option>
            <?php foreach ($statussen as $s): ?>
                <option <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="veld">
        <label for="rating">Rating (1 t/m 5)</label>
        <select id="rating" name="rating">
            <option value="">Geen rating</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option <?= $rating == $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="veld">
        <label for="afbeelding">Afbeelding uploaden (max 2 MB)</label>
        <input type="file" id="afbeelding" name="afbeelding" accept="image/*">

        <label for="afbeelding_url">Of een link naar een afbeelding</label>
        <input type="url" id="afbeelding_url" name="afbeelding_url" placeholder="https://..." value="<?= e($url) ?>">
    </div>

    <div class="veld veld-notities">
        <label for="notities">Notities</label>
        <textarea id="notities" name="notities" rows="4"><?= e($notities) ?></textarea>
    </div>

    <div class="veld">
        <label for="aangeraden_door">Aangeraden door</label>
        <input type="text" id="aangeraden_door" name="aangeraden_door" value="<?= e($aangeraden_door) ?>">
    </div>

    <a class="btn" href="games.php">Annuleren</a>
    <button type="submit" class="btn btn-primary">Game opslaan</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>