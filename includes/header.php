<?php
// Verwacht: $paginaTitel (tekst) en $actief ('games', 'over-ons', ...)
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($paginaTitel) ?> - GameSpark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css?v=<?php echo filemtime(__DIR__ . '/../style/style.css'); ?>">
</head>
<body>
    <header class="site-header">
        <div class="wrap header-inner">
            <a class="brand" href="index.php"><img class="logo" src="img/gamesparklogo.png" alt="GameSpark logo">GameSpark</a>
            <nav>
                <a href="index.php"<?= $actief === 'home' ? ' class="active"' : '' ?>>Home</a>
                <a href="games.php"<?= $actief === 'games' ? ' class="active"' : '' ?>>Games</a>
                <a href="over-ons.php"<?= $actief === 'over-ons' ? ' class="active"' : '' ?>>Over ons</a>
                <a class="account" href="account.php" aria-label="Account"></a>
            </nav>
        </div>
    </header>
    <main class="wrap">
