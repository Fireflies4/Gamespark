function bijwerken(kiezerId, legeTekst) {
    var kiezer = document.getElementById(kiezerId);

    // Staat deze keuzelijst niet op de pagina? Dan doen we niks.
    if (kiezer == null) {
        return;
    }

    var vinkjes = kiezer.getElementsByTagName('input');
    var titel = kiezer.getElementsByTagName('summary')[0];
    var namen = [];

    for (var i = 0; i < vinkjes.length; i++) {
        if (vinkjes[i].checked) {
            namen.push(vinkjes[i].value);
        }
    }

    if (namen.length > 0) {
        titel.textContent = namen.join(', ');
    } else {
        titel.textContent = legeTekst;
    }
}


// Alle keuzelijsten bijwerken
function alleBijwerken() {
    bijwerken('platform-kiezer', '-- Kies platforms --');
    bijwerken('genre-kiezer', '-- Kies genres --');
}


// Klik buiten een keuzelijst? Dan gaat die lijst dicht
function sluitLijsten(event) {
    var kiezers = document.getElementsByClassName('multiselect');

    for (var i = 0; i < kiezers.length; i++) {
        if (!kiezers[i].contains(event.target)) {
            kiezers[i].removeAttribute('open');
        }
    }
}


// Bij elk vinkje: als je het aan- of uitvinkt, de balken bijwerken
var alleVinkjes = document.querySelectorAll('.multiselect input');

for (var i = 0; i < alleVinkjes.length; i++) {
    alleVinkjes[i].addEventListener('change', alleBijwerken);
}

// Bij elke klik op de pagina kijken of er een lijst dicht moet
document.addEventListener('click', sluitLijsten);

// Eén keer bijwerken als de pagina geladen is
alleBijwerken();
