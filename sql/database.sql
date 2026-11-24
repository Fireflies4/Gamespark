CREATE DATABASE IF NOT EXISTS gamespark;
USE gamespark;

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    afbeelding_url VARCHAR(255),
    platform VARCHAR(150) NOT NULL,
    genre VARCHAR(255),
    releasedatum DATE NOT NULL,
    status VARCHAR(20) NOT NULL,
    rating INT,
    notitie VARCHAR(280),
    aangeraden_door VARCHAR(80),
    UNIQUE (naam, releasedatum)
);

INSERT INTO games (naam, platform, genre, releasedatum, status, rating) VALUES
    ('God of War', 'PlayStation', 'Actie-avontuur', '2018-04-20', 'In bezit', 5),
    ('Hollow Knight', 'PC', 'Platformer', '2017-02-24', 'Speelt nu', 4),
    ('Stardew Valley', 'PC', 'Simulatie', '2016-02-26', 'Uitgespeeld', 5);
