<?php
// Verwacht: $paginaTitel (tekst) en $actief ('games', 'over-ons', ...)
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($paginaTitel) ?> - GameSpark</title>
    <link rel="stylesheet" href="style/style.css">
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
