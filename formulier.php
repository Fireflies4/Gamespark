<?php
// Formulier: nieuwe game toevoegen
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Instellingen ----
$statussen   = ['Wishlist', 'In bezit', 'Speelt nu', 'Uitgespeeld'];
$platformen  = ['PC', 'PlayStation 5', 'PlayStation 4', 'Xbox Series X/S', 'Xbox One', 'Nintendo Switch', 'Mobiel', 'Anders'];
$minJaar     = 1970;
$maxJaar     = (int) date('Y') + 2;
$maxUpload   = 2 * 1024 * 1024; // 2 MB
$toegestaan  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
$uploadMap   = __DIR__ . '/img/uploads/';

// ---- CSRF-token ----
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$fouten = [];
$waarden = [
    'naam'           => '',
    'afbeelding_url' => '',
    'platform'       => '',
    'genre'          => '',
    'jaar'           => '',
    'status'         => '',
    'rating'         => '',
    'notities'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF controleren
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Ongeldig formulier (CSRF). Ga terug en probeer het opnieuw.');
    }

    // Invoer ophalen
    foreach ($waarden as $veld => $leeg) {
        $waarden[$veld] = trim($_POST[$veld] ?? '');
    }

    // ---- Validatie ----
    if ($waarden['naam'] === '') {
        $fouten['naam'] = 'De naam mag niet leeg zijn.';
    } elseif (mb_strlen($waarden['naam']) > 150) {
        $fouten['naam'] = 'De naam mag maximaal 150 tekens zijn.';
    }

    if ($waarden['platform'] === '') {
        $fouten['platform'] = 'Kies een platform.';
    } elseif (!in_array($waarden['platform'], $platformen, true)) {
        $fouten['platform'] = 'Ongeldig platform.';
    }

    if (mb_strlen($waarden['genre']) > 80) {
        $fouten['genre'] = 'Het genre mag maximaal 80 tekens zijn.';
    }

    if ($waarden['jaar'] === '' || !ctype_digit($waarden['jaar'])
        || (int) $waarden['jaar'] < $minJaar || (int) $waarden['jaar'] > $maxJaar) {
        $fouten['jaar'] = "Vul een geldig jaar in ($minJaar t/m $maxJaar).";
    }

    if (!in_array($waarden['status'], $statussen, true)) {
        $fouten['status'] = 'Kies een geldige status.';
    }

    // Rating is optioneel (1 t/m 5)
    if ($waarden['rating'] !== '') {
        if (!ctype_digit($waarden['rating']) || (int) $waarden['rating'] < 1 || (int) $waarden['rating'] > 5) {
            $fouten['rating'] = 'De rating moet tussen 1 en 5 liggen.';
        }
    }

    // ---- Afbeelding: upload of URL ----
    $afbeelding = null;
    $heeftUpload = isset($_FILES['afbeelding']) && $_FILES['afbeelding']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($heeftUpload) {
        $bestand = $_FILES['afbeelding'];
        if ($bestand['error'] !== UPLOAD_ERR_OK) {
            $fouten['afbeelding'] = 'Het uploaden is mislukt. Probeer het opnieuw.';
        } elseif ($bestand['size'] > $maxUpload) {
            $fouten['afbeelding'] = 'De afbeelding mag maximaal 2 MB zijn.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($bestand['tmp_name']);
            if (!isset($toegestaan[$mime]) || @getimagesize($bestand['tmp_name']) === false) {
                $fouten['afbeelding'] = 'Alleen JPG, PNG, WEBP of GIF is toegestaan.';
            } else {
                $afbeelding = ['tmp' => $bestand['tmp_name'], 'ext' => $toegestaan[$mime]];
            }
        }
    } elseif ($waarden['afbeelding_url'] !== '') {
        $urlOk = filter_var($waarden['afbeelding_url'], FILTER_VALIDATE_URL)
            && in_array(strtolower(parse_url($waarden['afbeelding_url'], PHP_URL_SCHEME)), ['http', 'https'], true)
            && mb_strlen($waarden['afbeelding_url']) <= 500;
        if (!$urlOk) {
            $fouten['afbeelding_url'] = 'Vul een geldige http(s)-link in.';
        }
    }

    // ---- Dubbele game (zelfde naam + jaar) ----
    if (!isset($fouten['naam']) && !isset($fouten['jaar'])) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM games WHERE naam = :naam AND jaar = :jaar');
        $stmt->execute([':naam' => $waarden['naam'], ':jaar' => (int) $waarden['jaar']]);
        if ((int) $stmt->fetchColumn() > 0) {
            $fouten['naam'] = 'Deze game (zelfde naam en jaar) staat al in de lijst.';
        }
    }

    // ---- Opslaan ----
    if (count($fouten) === 0) {
        $opslagUrl = $waarden['afbeelding_url'] !== '' ? $waarden['afbeelding_url'] : null;

        if ($afbeelding !== null) {
            if (!is_dir($uploadMap)) {
                mkdir($uploadMap, 0755, true);
            }
            $bestandsnaam = bin2hex(random_bytes(16)) . '.' . $afbeelding['ext'];
            if (move_uploaded_file($afbeelding['tmp'], $uploadMap . $bestandsnaam)) {
                $opslagUrl = 'img/uploads/' . $bestandsnaam;
            } else {
                $fouten['afbeelding'] = 'De afbeelding kon niet worden opgeslagen.';
            }
        }
    }

    if (count($fouten) === 0) {
        $stmt = $pdo->prepare(
            'INSERT INTO games (naam, afbeelding_url, platform, genre, jaar, status, rating, notities)
             VALUES (:naam, :afbeelding_url, :platform, :genre, :jaar, :status, :rating, :notities)'
        );
        $stmt->execute([
            ':naam'           => $waarden['naam'],
            ':afbeelding_url' => $opslagUrl,
            ':platform'       => $waarden['platform'],
            ':genre'          => $waarden['genre'] !== '' ? $waarden['genre'] : null,
            ':jaar'           => (int) $waarden['jaar'],
            ':status'         => $waarden['status'],
            ':rating'         => $waarden['rating'] !== '' ? (int) $waarden['rating'] : null,
            ':notities'       => $waarden['notities'] !== '' ? $waarden['notities'] : null,
        ]);

        // Nieuw token na succesvol versturen, daarna terug naar het overzicht
        unset($_SESSION['csrf_token']);
        header('Location: games.php');
        exit;
    }
}

// Kleine helper om output te escapen (XSS)
function e(string $tekst): string
{
    return htmlspecialchars($tekst, ENT_QUOTES, 'UTF-8');
}

$paginaTitel = 'Game toevoegen';
$actief = 'games';
require __DIR__ . '/includes/header.php';
?>

<div class="title-row">
    <h1>Game toevoegen</h1>
    <a class="btn" href="games.php">&larr; Terug naar overzicht</a>
</div>

<?php if (count($fouten) > 0): ?>
    <div class="melding melding-fout" role="alert">
        <strong>Controleer het formulier:</strong>
        <ul>
            <?php foreach ($fouten as $fout): ?>
                <li><?= e($fout) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data" class="formulier" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

    <div class="veld">
        <label for="naam">Naam *</label>
        <input type="text" id="naam" name="naam" maxlength="150" required value="<?= e($waarden['naam']) ?>">
    </div>

    <div class="veld">
        <label for="platform">Platform *</label>
        <select id="platform" name="platform" required>
            <option value="">-- Kies een platform --</option>
            <?php foreach ($platformen as $p): ?>
                <option value="<?= e($p) ?>" <?= $waarden['platform'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="veld">
        <label for="genre">Genre</label>
        <input type="text" id="genre" name="genre" maxlength="80" placeholder="Bijv. RPG, Shooter, Puzzel" value="<?= e($waarden['genre']) ?>">
    </div>

    <div class="veld">
        <label for="jaar">Releasejaar *</label>
        <input type="number" id="jaar" name="jaar" min="<?= $minJaar ?>" max="<?= $maxJaar ?>" required value="<?= e($waarden['jaar']) ?>">
    </div>

    <div class="veld">
        <label for="status">Status *</label>
        <select id="status" name="status" required>
            <option value="">-- Kies een status --</option>
            <?php foreach ($statussen as $s): ?>
                <option value="<?= e($s) ?>" <?= $waarden['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="veld">
        <label for="rating">Rating (1 t/m 5)</label>
        <select id="rating" name="rating">
            <option value="">Geen rating</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $waarden['rating'] === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <fieldset class="veld">
        <legend>Afbeelding (kies upload of link)</legend>

        <label for="afbeelding">Bestand uploaden (JPG, PNG, WEBP, GIF, max 2 MB)</label>
        <input type="file" id="afbeelding" name="afbeelding" accept="image/jpeg,image/png,image/webp,image/gif">

        <label for="afbeelding_url">Of een link naar een afbeelding</label>
        <input type="url" id="afbeelding_url" name="afbeelding_url" maxlength="500" placeholder="https://..." value="<?= e($waarden['afbeelding_url']) ?>">
    </fieldset>

    <div class="veld">
        <label for="notities">Notities</label>
        <textarea id="notities" name="notities" rows="4"><?= e($waarden['notities']) ?></textarea>
    </div>

    <div class="acties">
        <button type="submit" class="btn btn-primary">Game opslaan</button>
        <a class="btn" href="games.php">Annuleren</a>
    </div>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>