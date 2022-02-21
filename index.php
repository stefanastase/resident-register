<?php
session_start();
?>


<html>
    <head>
        <meta charset="UTF-8">
        <title>Evidenta Populatiei</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body class="home">
	<?php include "header.php" ?>
	<br>
	<br>
        <div class="center">
            <h2>Scopul aplicatiei</h2> 
            <div class="horizontal-line"></div>
            <p>
            &nbsp;&nbsp;&nbsp;&nbsp;
            Pentru a putea mentine evidenta tuturor persoanelor dintr-o localitate, dar si informatii referitoare la proprietatiile, datoriile, actele de identitate emise, Directia Locala de Evidenta a Populatiei va folosi o baza de date relationala cotinand toate informatiile necesare.
            </p>
            <p>
            &nbsp;&nbsp;&nbsp;&nbsp;
            Platforma faciliteaza accesul la informatiile referitoare la taxele, impozitele sau proprietatiile unei persoane si permite utilizatorului sa modifice cu usurinta datele pastrate in baza de date, inlocuind sintaxa SQL cu butoane si formulare HTML.
            </p>
            <p>
            &nbsp;&nbsp;&nbsp;&nbsp;
            Astfel, utilizatorul poate inregistra anumite evenimente ce modifica componenta bazei de date sau poate modifica inregistrari deja existente in baza de date.
            </p>
            <br>
        </div>
        <div class="center">
            <h2>Detalii de implementare</h2>
            <div class="horizontal-line"></div>
            <p> Partea de frontend a proiectului a fost implementata folosind <b>HTML</b>, <b>CSS</b>, <b>JavaScript</b> si <b>jQuery</b>, iar pentru cea de backend s-a folosit <b>PHP8</b> si <b>MySQL</b>.</p>
            <br>
        </div>
    </body>
</html>