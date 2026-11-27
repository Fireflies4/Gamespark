<?php

//Variabelen vullen (komen uit de controller)
$modelActie = $modelActie ?? '';
$id = $id ?? '';
$naam = $naam ?? '';
$afbeelding = $afbeelding ?? null;
$platform = $platform ?? '';
$genre = $genre ?? null;
$releasedatum = $releasedatum ?? '';
$status = $status ?? '';
$rating = $rating ?? null;
$notities = $notities ?? null;
$aangeraden_door = $aangeraden_door ?? null;

//1. Verbinding
require_once __DIR__.'/../../config/conn.php';

if($modelActie == 'dubbel') {

//2. Query: bestaat er al een ANDERE game met dezelfde naam en dezelfde releasedatum?
$query = "SELECT COUNT(*) FROM games WHERE naam = :naam AND releasedatum = :releasedatum AND id != :id";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute([
    ":naam" => $naam,
    ":releasedatum" => $releasedatum,
    ":id" => $id
]);

//5. Fetch
$aantalDubbel = $statement->fetchColumn();
}

if($modelActie == 'afbeelding') {

//2. Query: de huidige afbeelding van de game
$query = "SELECT afbeelding_url FROM games WHERE id = :id";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute([
    ":id" => $id
]);

//5. Fetch
$afbeelding = $statement->fetchColumn();
}

if($modelActie == 'edit') {

//2. Query
$query = "
        UPDATE games
        SET
            naam = :naam,
            afbeelding_url = :afbeelding_url,
            platform = :platform,
            genre = :genre,
            releasedatum = :releasedatum,
            status = :status,
            rating = :rating,
            notitie = :notitie,
            aangeraden_door = :aangeraden_door
        WHERE
            id = :id
";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute([
    ":naam" => $naam,
    ":afbeelding_url" => $afbeelding,
    ":platform" => $platform,
    ":genre" => $genre,
    ":releasedatum" => $releasedatum,
    ":status" => $status,
    ":rating" => $rating,
    ":notitie" => $notities,
    ":aangeraden_door" => $aangeraden_door,
    ":id" => $id
]);
}

if($modelActie == 'delete') {

//2. Query
$query = "DELETE FROM games WHERE id = :id";

//3. Prepare
$statement = $pdo->prepare($query);

//4. Execute
$statement->execute([
    ":id" => $id
]);
}
