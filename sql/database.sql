CREATE DATABASE IF NOT EXISTS gamespark;
USE gamespark;

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    afbeelding_url VARCHAR(255),
    platform VARCHAR(20) NOT NULL,
    genre VARCHAR(40),
    releasejaar INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    rating INT,
    notitie VARCHAR(280),
    aangeraden_door VARCHAR(80),
    UNIQUE (naam, releasejaar)
);

INSERT INTO games (naam, platform, genre, releasejaar, status, rating) VALUES
    ('God of War', 'PlayStation', 'Actie-avontuur', 2018, 'In bezit', 5),
    ('Hollow Knight', 'PC', 'Platformer', 2017, 'Speelt nu', 4),
    ('Stardew Valley', 'PC', 'Simulatie', 2016, 'Uitgespeeld', 5);
