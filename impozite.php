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


// Daca special este setat, calculam rata de colectare a tuturor rau-platnicilor
if(isset($_GET["special"]))
    $query="
        SELECT P.PersoanaID, Nume, Prenume, Email, SUM(Achitat) / COUNT(*) AS ProcentAchitare FROM Impozite I JOIN Persoane P
        ON I.PersoanaID = P.PersoanaID
        group by P.PersoanaID
        HAVING SUM(Achitat) / COUNT(*) < (SELECT SUM(Achitat) / COUNT(*) AS ProcentMediuAchitare FROM Impozite)";

else
	// Suma datorata de fiecare persoana
    $query = "
        SELECT P.PersoanaID, P.Nume, P.Prenume, S.SumaDatorata
        FROM Persoane P LEFT JOIN
        (SELECT P.PersoanaID, P.Nume, P.Prenume, SUM(I.Valoare) + SUM(I.Penalitati) AS SumaDatorata
            FROM persoane P LEFT JOIN impozite I on P.PersoanaID = I.PersoanaID
            WHERE I.Achitat = 0
            GROUP BY P.PersoanaID, P.Nume, P.Prenume) S
            ON P.PersoanaID = S.PersoanaID";
$result = mysqli_query($link, $query);

$action = 0;

if(isset($_GET["action"])){
	// Vizualizare toate impozitele unei persoane
    if($_GET["action"] == "view"){
        $action = 1;
        $id = $_GET["id"];
        $query = "SELECT Nume, Prenume FROM persoane WHERE PersoanaID = $id";
        if($result = mysqli_query($link, $query)){
            $row = mysqli_fetch_array($result);
            $full_name = $row['Nume'] . " " . $row['Prenume'];
        }
        $query = "SELECT * FROM impozite I JOIN persoane P ON I.PersoanaID = P.PersoanaID WHERE P.PersoanaID = $id AND I.Achitat = 0";
        $result_unpaid = mysqli_query($link, $query);

        $query = "SELECT * FROM impozite I JOIN persoane P ON I.PersoanaID = P.PersoanaID WHERE P.PersoanaID = $id AND I.Achitat = 1";
        $result_paid = mysqli_query($link, $query);
    }
	// Adaugare impozit
    if($_GET["action"] == "add"){
        $action = 2;
        $id = $_GET["id"];
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $tip = $_POST["tip"];
            $proprietate_id = $_POST["proprietate_id"];
            $valoare = $_POST["valoare"];
            $data_emitere = $_POST["an_emitere"] . "-" . $_POST["luna_emitere"] . "-" . $_POST["zi_emitere"];
            $data_scadenta = $_POST["an_scadenta"] . "-" . $_POST["luna_scadenta"] . "-" . $_POST["zi_scadenta"];
            $query = "
                INSERT INTO impozite(Tip, ProprietateID, PersoanaID, Valoare, DataEmitere, DataScadenta, Achitat, Penalitati)
                VALUES('$tip', $proprietate_id, $id, $valoare, '$data_emitere', '$data_scadenta', '0', 0)";
            
            if(mysqli_query($link, $query)){
                mysqli_close($link);
                header("location: impozite.php?action=view&id=" . $id);
                exit;                
            }    
        }
    }
	// Marcare impozit ca platit/neplatit
    if($_GET["action"] == "mark"){
        $id = $_GET["id"];
        $query = "SELECT Achitat FROM impozite WHERE ImpozitID = $id";
        if($result = mysqli_query($link, $query)) {
            $row = mysqli_fetch_array($result);
            if($row["Achitat"] == '0')
                $query = "UPDATE impozite SET Achitat = '1' WHERE ImpozitID = $id";
            else if($row["Achitat"] == '1')
                $query = "UPDATE impozite SET Achitat = '0' WHERE ImpozitID = $id";
            if(mysqli_query($link, $query)){
                $query = "SELECT PersoanaID FROM impozite WHERE ImpozitID = $id";
                if($result = mysqli_query($link, $query)) $row = mysqli_fetch_array($result);
                header("location: impozite.php?action=view&id=" . $row["PersoanaID"]);
                exit;                
            }
        }           
    }
	// Adaugare penalitati	
    if($_GET["action"] == "penalty"){
        $id = $_GET["id"];
        $uid = "";
        $query = "SELECT PersoanaID FROM impozite WHERE ImpozitID = $id";
        if($result = mysqli_query($link, $query)){
            $row = mysqli_fetch_array($result);
            $uid = $row["PersoanaID"];
        }
        $action = 3;
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $valoare = $_POST["valoare"];
            $query = " UPDATE impozite SET Penalitati = Penalitati + $valoare WHERE ImpozitId = $id";
            if(mysqli_query($link, $query)){
                header("location: impozite.php?action=view&id=" . $uid);
                exit;                
            }
        }        
    }
	// Stergere impozit
    if($_GET["action"] == "delete"){
        $id = $_GET["id"];
        $query = "SELECT PersoanaID FROM impozite WHERE ImpozitID = $id";
        if($result = mysqli_query($link, $query)) $row = mysqli_fetch_array($result);
        $query = "
            DELETE FROM impozite
            WHERE ImpozitID = $id";
            if(mysqli_query($link, $query)){
                header("location: impozite.php?action=view&id=" . $row["PersoanaID"]);
                exit;                
            }           
    }
}

?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Inregistrare impozite</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <script>
            function checkValue(val){
                var select_loc = document.getElementById("select_loc");
                var select_teren = document.getElementById("select_teren");
                if(val == "cladire") {select_loc.style.display = "inline"; select_teren.style.display = "none";}
                else if(val == "teren") {select_teren.style.display = "inline"; select_loc.style.display = "none";}
                else {select_loc.style.display = "none"; select_teren.style.display = "none";}
            }
        </script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $("#istoric_button").click(function(){
                    $("#istoric").slideToggle(100);
                });
            });
        </script>
    </head>
    <body class="impozite"> 
        <?php include "header.php" ?>
        <div class="center">
            <?php if($action == 0) {?>
				<!--- Afisare persoane alaturi de suma datorata --->
                <?php if(!isset($_GET["special"])){?>
                <h2> Impozite</h2>
                <div class="horizontal-line"></div>
                <table>
                    <tr> 
                        <th> Nume complet </th>
                        <th> Suma datorata </th>
                        <th> Actiuni </th>
                    </tr>
                    <?php if ($result) { 
                        while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                        <td><?php 
                        if($row['SumaDatorata'] != "")
                            echo $row['SumaDatorata']; 
                        else echo "0";?></td>
                        <td> 
                        <a href = "impozite.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php }
                    }?>
                </table>
                <div class="center_footer">
                    <table>
                        <tr>
                            <th><a href = "impozite.php?special"><i class="fas fa-running"></i> Afisare rau-platnici</a></th>      
                        </tr>
                    </table>
                </div>
                <?php } else {?>
				<!--- Afisare rau-platnici --->
                <h2> Impozite</h2>
                <h3 id="red"> Rau-platnici</h3>
                <div class="horizontal-line"></div>
                <table>
                    <tr> 
                        <th> Nume complet </th>
                        <th> Procent achitare </th>
                        <th> Actiuni </th>
                    </tr>
                    <?php if ($result) { 
                        while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                        <td><?php echo $row['ProcentAchitare'] . "%"; ?></td>
                        <td> 
                        <a href = "mailto:<?php echo $row['Email'] ?>"><i class="fas fa-envelope"></i></i></a>
                        <a href = "impozite.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php }
                    }?>
                </table>
                <div class="center_footer">
                    <table>
                        <tr>
                            <th><a href = "impozite.php"><i class="fas fa-users"></i> Afisare totala</a></th>      
                        </tr>
                    </table>
                </div>
            <?php }
            }
            else if ($action == 1){ ?>
				<!--- Afisare toate impozitele unei persoane --->
                <h2>Impozite</h2>
                <h3><?php echo $full_name?></h3>
                <div class="horizontal-line"></div>
                <table>
                    <tr> 
                        <th> Tip </th>
                        <th> Data emitere </th>
                        <th> Data scadenta </th>
                        <th> Valoare </th>
                        <th> Penalitati </th>
                        <th> Actiuni </th> 
                    </tr>
                    <?php if ($result) { 
                    while($row = mysqli_fetch_array($result_unpaid)){ ?>
					<!--- Afisare impozitele neplatite ale unei persoane --->
                    <tr>
                        <td><?php echo $row['Tip'] ?> </td>
                        <td><?php echo $row['DataEmitere'] ?> </td>
                        <td><?php echo $row['DataScadenta'] ?> </td>
                        <td><?php echo $row['Valoare'] ?> </td>
                        <td><?php echo $row['Penalitati'] ?> </td>
                        <td>
                        <a href = "impozite.php?action=penalty&id=<?php echo $row["ImpozitID"];?>"><i class="fas fa-coins"></i></a>
                        <a href = "impozite.php?action=mark&id=<?php echo $row["ImpozitID"];?>"><i class="fas fa-check"></i></a>
                        <a href = "impozite.php?action=delete&id=<?php echo $row["ImpozitID"];?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php }
                }?>
                </table>
                <div id="istoric">
                    <p class="heading"> Impozite achitate </p>
                    <br>
                    <div class="horizontal-line-2"></div>
                    <table>
                    <tr> 
                        <th> Tip </th>
                        <th> Data emitere </th>
                        <th> Data scadenta </th>
                        <th> Valoare </th>
                        <th> Penalitati </th>
                        <th> Actiuni </th> 
                    </tr>
					<!--- Afisare impozitele platite ale unei persoane --->
                    <?php if ($result) { 
                    while($row = mysqli_fetch_array($result_paid)){ ?>
                    <tr>
                        <td><?php echo $row['Tip'] ?> </td>
                        <td><?php echo $row['DataEmitere'] ?> </td>
                        <td><?php echo $row['DataScadenta'] ?> </td>
                        <td><?php echo $row['Valoare'] ?> </td>
                        <td><?php echo $row['Penalitati'] ?> </td>
                        <td>
                        <a href = "impozite.php?action=mark&id=<?php echo $row["ImpozitID"];?>"><i class="fas fa-check"></i></a>
                        <a href = "impozite.php?action=delete&id=<?php echo $row["ImpozitID"];?>"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php }
                }?>
                </table>
                </div>
                <div class="center_footer">
                    <table>
                        <tr>
                            <th><a href ="javascript:window.history.back();"><i class="fas fa-arrow-left"></i> Inapoi</a></th>
                            <th><a href = "impozite.php?action=add&id=<?php echo $id;?>"><i class="fas fa-plus"></i> Adauga</a></th>
                            <th><a href = "#" id="istoric_button"><i class="fas fa-history"></i> Istoric</a></th>        
                        </tr>
                    </table>
                </div>
            <?php }
            else if ($action == 2){?>
			<!--- Formular adaugare impozit --->
                <h2><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Adaugare impozit </h2>
                <div class="horizontal-line"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add&id=" . $id); ?>" method="post">
                    <p class="form_heading">Date impozit</p>
                    <div class="horizontal-line-2"></div>
                    <div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
                                <label for="tip"><b>Tip</b></label><br>
                                <select name="tip" onchange='checkValue(this.value);' required>
                                    <option disabled selected value> Alegeti </option>
                                    <option value="cladire">Cladire</option>
                                    <option value="teren">Teren</option>
                                    <option value="auto">Auto</option>
                                </select>
                            </div>
                            <div class = "form_cell"> 
                            <label for="valoare"><b>Valoare</b></label><br>
                            <input type="text" placeholder="ex. 300" name="valoare" required>
                            </div>
                        </div>
                        <div class = "form_row">
                            <div class = "form_cell">
                                <label for="data_emitere"><b>Data emitere</b></label>
                                <br>
                                <table id="data_emitere">
                                    <tr>
                                        <td>
                                        <select name="zi_emitere" required>
                                            <option disabled selected value> Zi </option>
                                            <?php for ($i = 1; $i <= 31; $i++){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="luna_emitere" required>
                                        <option disabled selected value> Luna </option>
                                            <?php for ($i = 1; $i <= 12; $i++){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="an_emitere" required>
                                        <option disabled selected value> An </option>
                                        <?php for ($i = 2022; $i >= 1900; $i--){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>                                                                
                                    </tr>
                                </table>
                            </div>
                            <div class = "form_cell">
                                <label for="data_scadenta"><b>Data scadenta</b></label>
                                <br>
                                <table id="data_scadenta">
                                    <tr>
                                        <td>
                                        <select name="zi_scadenta" required>
                                            <option disabled selected value> Zi </option>
                                            <?php for ($i = 1; $i <= 31; $i++){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="luna_scadenta" required>
                                        <option disabled selected value> Luna </option>
                                            <?php for ($i = 1; $i <= 12; $i++){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="an_scadenta" required>
                                        <option disabled selected value> An </option>
                                        <?php for ($i = 2040; $i >= 1950; $i--){?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }?>
                                        </select>
                                        </td>                                                                
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br>
					<!--- Daca impozitul este atribuit unei proprietati, aceasta se va asocia --->
                    <input type="hidden" name="proprietate_id" value="null">
                    <div id="select_loc" style=" display: none">
                        <p class="form_heading">Proprietate</p>
                        <div class="horizontal-line-2"></div>
                        <?php
                            $query = "
                            SELECT * 
                            FROM persoaneproprietati ppr JOIN proprietati pr 
                            ON ppr.ProprietateID = pr.ProprietateID
                            WHERE PersoanaID = $id AND Categoria IN ('apartament', 'casa')";?>
                            <div class="form_table">
                                <div class = "form_row">
                                    <div class = "form_cell">
                                        <label for="proprietate_id"><b>Adresa proprietatii</b></label><br>
                                        <select name="proprietate_id">
                                            <option disabled selected value> Alegeti </option>
                                            <?php if($result = mysqli_query($link, $query)) {
                                                while($row = mysqli_fetch_array($result)) {
                                                    $str = "Str. " . $row['Strada'] . ", Nr. " . $row['Numar'];
                                                    if($row['Bloc'] != '') $str = $str . ", Bl. " . $row['Bloc'];
                                                    if($row['Apartament'] != '') $str = $str . ", Ap. " . $row['Apartament'];?>
                                            <option value=<?php echo $row['ProprietateID'];?>><?php echo $str?></option>     
                                            <?php }
                                            }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div id="select_teren" style=" display: none">
                        <p class="form_heading">Proprietate</p>
                        <div class="horizontal-line-2"></div>
                        <?php
                            $query = "
                            SELECT * 
                            FROM persoaneproprietati ppr JOIN proprietati pr 
                            ON ppr.ProprietateID = pr.ProprietateID
                            WHERE PersoanaID = $id AND Categoria = 'teren'";?>
                            <div class="form_table">
                                <div class = "form_row">
                                    <div class = "form_cell">
                                        <label for="proprietate_id"><b>Adresa proprietatii</b></label><br>
                                        <select name="proprietate_id">
                                            <option disabled selected value> Alegeti </option>
                                            <?php if($result = mysqli_query($link, $query)) {
                                                while($row = mysqli_fetch_array($result)) {
                                                    $str = "Str. " . $row['Strada'] . ", Nr. " . $row['Numar'];?>
                                            <option value=<?php echo $row['ProprietateID'];?>><?php echo $str?></option>     
                                            <?php }
                                            }?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <button type="submit">Adaugare</button>
                </form>  
                <?php }
            else if ($action == 3){?>
			<!--- Formular adaugare penalitati --->
                <h2><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Adaugare penalitati </h2>
                <div class="horizontal-line"></div>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=penalty&id=" . $id); ?>" method="post">
                    <div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">        
                                <label for="valoare"><b>Valoare penalitati</b></label>
                                <input type="text" placeholder="ex. 100" name="valoare" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit">Adaugare</button>
                </form>     
            <?php }?>
        </div>
    </body>
</html>