    <?php
// Initializam sesiunea
session_start();

// Verificam daca utilizatorul este conectat, daca acesta nu este logat va fi redirectionat catre pagina de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Realizam conectarea la baza de date in cazul in care nu este facuta deja
require_once "config.php";

// Realizam query-urile pentru statistici
// Numar persoane
$query = "SELECT COUNT(*) FROM persoane";
$result = mysqli_query($link, $query);

if($result){
    $row = mysqli_fetch_array($result);
    $pers_count = $row[0];
}
// Numar proprietati
$query = "SELECT COUNT(*) FROM proprietati";
$result = mysqli_query($link, $query);

if($result){
    $row = mysqli_fetch_array($result);
    $prop_count = $row[0];
}
// Rata de colectare
$query = "
    SELECT ROUND(I1.SumaAchitata / I2.SumaTotala * 100, 2) as RataColectare
    FROM 
    (SELECT (SUM(valoare) + SUM(Penalitati)) AS SumaAchitata from Impozite
    WHERE Achitat = 1) I1, 
    (SELECT SUM(Valoare) + Sum(Penalitati) AS SumaTotala from Impozite) I2
    ";

    $result = mysqli_query($link, $query);

    if($result){
        $row = mysqli_fetch_array($result);
        $percentage = $row[0];
    }    
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Acasa</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body class="home">
	<?php include "header.php" ?>
	<!--- Afisare mesaj de bun venit la autentificare --->
    <?php if (isset($_GET["welcome"])) {?>
        <div class = "div-table" id="welcome_alert">
            <div class = "div-row">
                    <div class="div-cell" id="welcome_icon">
                        <i class="far fa-user-circle"></i>
                    </div>
                    <div class="div-cell" id="welcome_message">
                        Bine ai revenit, <?php echo $_SESSION["username"] ?>!
                    </div>  
            </div>
        </div>
    <?php }?>
        <div class="center">
            <h2>Acasa</h2>
            <div class="horizontal-line"></div>
            <h3>Meniu rapid</h3>
            <div class="horizontal-line-2" id="closer-line"></div>

            <div class = "div-table" id="quick_action">
                <div class = "div-row">
                    <a class="div-cell" id="action" href="programari.php">
                        <i class="fas fa-clock"></i>
                    </a>
                    <a class="div-cell" id="action" href="persoane.php?action=add">
                        <i class="fas fa-user-plus"></i>
                    </a> 
                    <a class="div-cell" id="action" href="persoane.php?action=add2">
                        <i class="fas fa-baby"></i>
                    </a>

                    <a class="div-cell" id="action" href="proprietati.php?action=add">
                    <i class="fas fa-building"></i>
                    </a>  
                    <a class="div-cell" id="action" href="acte.php?new">
                        <i class="fas fa-id-card"></i>
                    </a>     
                </div>
                <div class = "div-row">
                    <div class="div-cell" id="text-action">
                        Programare noua
                    </div>
                    <div class="div-cell" id="text-action">
                        Adaugare persoana
                    </div> 
                    <div class="div-cell" id="text-action">
                        Inregistrare nastere
                    </div>
                    <div class="div-cell" id="text-action">
                        Adaugare proprietate
                    </div>  
                    <div class="div-cell" id="text-action">
                        Emitere act identitate
                    </div>     
                </div>
            </div>

            <div class="subsection">
                <h3>Statistici</h3>
                <div class="horizontal-line-2" id="closer-line"></div>
                <div class = "div-table" id="statistics">
                    <div class = "div-row">
                        <div class="div-cell" id="stats">
                            <div id="stats-header">Persoane inregistrate</div>
                            <div id="stats-data">
                                <?php echo $pers_count?>
                            </div>
                            <div id="stats-image"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="div-cell" id="stats">
                            <div id="stats-header">Proprietati declarate</div>
                            <div id="stats-data">
                                <?php echo $prop_count?>
                            </div>
                            <div id="stats-image"><i class="fas fa-city"></i></div>
                        </div>
                        <div class="div-cell" id="stats">
                            <div id="stats-header">Colectare impozite</div>
                            <div id="stats-data">
                                <?php echo $percentage?>%
                            </div>
                            <div id="stats-image"><i class="fas fa-money-bill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>