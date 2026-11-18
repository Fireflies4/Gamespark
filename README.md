# GameSpark

Een webapplicatie waarin Liam de Waal zijn games bijhoudt: welke games hij gespeeld en gereviewd heeft, welke op zijn wishlist staan en welke hij aangeraden heeft gekregen. Per game geeft hij een rating, zodat collega's die als inspiratie kunnen gebruiken.

Dit is een duo-challenge voor de opleiding Software Developer.

## Functies

- **Overzicht:** alle games met naam, afbeelding, platform, status en rating
- **Detail:** alle gegevens van één game bekijken
- **Toevoegen:** nieuwe game met naam, afbeelding (upload of URL), platform, genre, jaar, status en rating
- **Wijzigen:** alle velden van een game aanpassen
- **Verwijderen:** een game verwijderen, pas na bevestiging

### Regels

- De naam mag niet leeg zijn
- Het jaar moet een geldig jaar zijn
- De rating moet binnen de afgesproken schaal vallen
- De status is Wishlist, In bezit, Speelt nu of Uitgespeeld
- Een game met dezelfde naam én hetzelfde releasejaar kan niet nog een keer worden toegevoegd

### Optioneel

- Filteren op platform, genre, status of rating
- Sorteren op naam, jaar of rating
- Notities per game
- Responsive design

## Beveiliging

- Prepared statements tegen SQL-injectie
- Output escapen tegen XSS
- CSRF-tokens op formulieren
- Geüploade afbeeldingen worden gecontroleerd voordat ze worden opgeslagen

## Tools

- Visual Studio Code
- Herd (lokale server)
- MySQL
- Git / GitHub

## Installatie

1. Clone de repository in je Herd-map:
   ```
   git clone <repo-url> Gamespark
   ```
2. Maak een database aan en importeer het SQL-bestand.
3. Kopieer `config/config.example.php` naar `config/config.php` en vul je eigen databasegegevens in (dit bestand staat in `.gitignore`).
4. Open `http://gamespark.test` in je browser.

## Samenwerken

- Werk altijd op een eigen branch, nooit direct op `main`
- Maak een pull request en laat de ander die controleren
- Eerst `git pull`, dan pas verder werken

## Planning

Zie [planning.txt](planning.txt).
