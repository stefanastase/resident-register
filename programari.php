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

// In cazul in care current este setat se vor afisa doar viitoarele programari
if(!isset($_GET["current"]))
    $query = "SELECT P.PersoanaID, Nume, Prenume, CNP, COUNT(pr.programareid) AS NrProgramari FROM persoane p left join programari pr on p.persoanaid = pr.persoanaid GROUP BY p.persoanaid, nume, prenume";
else
    $query = "SELECT p.persoanaid, Nume, Prenume, Motiv, Ora, Data FROM persoane p join programari pr on p.persoanaid = pr.persoanaid WHERE pr.data > curdate()";

$result = mysqli_query($link, $query);

$action = 0;

if(isset($_GET["action"])){
	// Vizualizare toate programarile unei persoane
    if($_GET["action"] == "view"){
        $action = 1;
        $id = $_GET["id"];
        $query = "SELECT Nume, Prenume FROM persoane WHERE PersoanaID = $id";
        if($result = mysqli_query($link, $query)){
            $row = mysqli_fetch_array($result);
            $full_name = $row['Nume'] . " " . $row['Prenume'];
        }
        $query = "SELECT * FROM programari pr JOIN persoane p ON pr.PersoanaID = p.PersoanaID WHERE p.PersoanaID = $id";
        $result = mysqli_query($link, $query);
    }
	// Adaugare programare unei persoane
    if($_GET["action"] == "add"){
        $action = 2;
        if(isset($_GET["id"])){
            $id = $_GET["id"];
            $query = "SELECT Nume, Prenume FROM persoane WHERE PersoanaID = $id";
            if($result = mysqli_query($link, $query)){
                $row = mysqli_fetch_array($result);
                $full_name = $row['Nume'] . " " . $row['Prenume'];
            }
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                $motiv = $_POST["motiv"];
                $data = $_POST["an_programare"] . "-" . $_POST["luna_programare"] . "-" . $_POST["zi_programare"];
                $ora = $_POST["ora_programare"] . ":" . $_POST["minut_programare"];
                $query = "
                    INSERT INTO programari(Motiv, Data, Ora, PersoanaID)
                    VALUES('$motiv', '$data', '$ora', $id)";
                if(mysqli_query($link, $query)){
                    mysqli_close($link);
                    header("location: programari.php?action=view&id=" . $id);
                    exit;                
                }    
            }
        }
    }
	// Stergere programare existenta
    if($_GET["action"] == "delete"){
        $id = $_GET["id"];
        $query = "SELECT PersoanaID FROM programari WHERE ProgramareID = $id";
        if($result = mysqli_query($link, $query)) $row = mysqli_fetch_array($result);
        $query = "
            DELETE FROM programari
            WHERE ProgramareID = $id";
            if(mysqli_query($link, $query)){
                header("location: programari.php?action=view&id=" . $row["PersoanaID"]);
                exit;                
            }           
    }
	// Afisare programari din luna si anul precizate de catre utilizator
    if($_GET["action"] == "history"){
        $action = 3;
        if(isset($_GET["m"]) && isset($_GET["y"])){
            $mth = $_GET["m"];
            $yr = $_GET["y"];
            $query = "
                SELECT * FROM persoane p JOIN
                (SELECT * from programari
                WHERE month(Data) = $mth and year(Data) = $yr) pr ON p.PersoanaID = pr.PersoanaID";
            $result = mysqli_query($link, $query);

        }
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $luna = $_POST["luna_programare"];
            $an = $_POST["an_programare"];
            header("location: programari.php?action=history&m=" . $luna . "&y=" . $an);
            exit;                
            }    
        }
}

mysqli_close($link);
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Programari</title>
        <link rel="stylesheet" type="text/css" href="style.css">
		<link rel="icon" href="img/flag.png">
    </head>
    <body class="programari"> 
        <?php include "header.php" ?>
        <div class="center">
		
        <?php if($action == 0) {?>
		<!--- Afisare persoane si numarul de programari --->
            <?php if(!isset($_GET["current"])){?>
            <h2> Programari</h2>
            <div class="horizontal-line"></div>
            <table>
                <tr> 
                    <th> Nume complet </th>
                    <th> CNP </th>
                    <th> Numar programari </th>
                    <th> Actiuni </th>
                </tr>
                <?php if ($result) { 
                    while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                        <td><?php echo $row['CNP']; ?></td>
                        <td><?php echo $row['NrProgramari']; ?></td>
                        <td> 
                            <a href = "programari.php?action=add&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-plus"></i></a>
                            <a href = "programari.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                <?php }
                }?>
            </table>
            <div class="center_footer">
                        <table>
                            <tr>
                                <th><a href = "programari.php?current"><i class="fas fa-calendar-day"></i> Programari viitoare</a></th>   
                                <th><a href = "programari.php?action=history"><i class="fas fa-calendar-day"></i> Istoric programari</a></th>     
                            </tr>
                        </table>
            </div>
        <?php }
        else {?>
		<!--- Afisare programari viitoare --->
            <h2> Programari viitoare</h2>
            <div class="horizontal-line"></div>
            <table>
                <tr> 
                    <th> Nume complet </th>
                    <th> Motiv programare </th>
                    <th> Data </th>
                    <th> Ora </th>
                </tr>
                <?php if ($result) { 
                    while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                        <td><?php echo $row['Motiv']; ?></td>
                        <td><?php echo $row['Data']; ?></td>
                        <td><?php echo $row['Ora']; ?></td>
                    </tr>
                <?php }
                }?>
            </table>
            <div class="center_footer">
                <table>
                    <tr>
                        <th><a href = "programari.php"><i class="fas fa-calendar-day"></i> Toate programarile</a></th>     
                    </tr>
                </table>
            </div>
        <?php }
        }
        else if ($action == 1){ ?>
		<!--- Afisare programari ale unei persoane selectate --->
        <h2>Programari</h2>
        <h3><?php echo $full_name?></h3>
        <div class="horizontal-line"></div>
        <table>
            <tr> 
                <th> Motiv </th>
                <th> Data </th>
                <th> Ora </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){ ?>
                <tr>
                    <td><?php echo $row['Motiv'] ?> </td>
                    <td><?php echo $row['Data'] ?> </td>
                    <td><?php echo $row['Ora'] ?> </td>
                    <td><a href = "programari.php?action=delete&id=<?php echo $row['ProgramareID'] ?>"><i class="fas fa-trash"></i></a></td>
                </tr>
            <?php }
            }?>
        </table>
        <div class="center_footer">
            <table>
                <tr>
                    <th><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i> Inapoi</a></th>
                    <th><a href = "programari.php?action=add&id=<?php echo $id;?>"><i class="fas fa-plus"></i> Adauga</a></th>       
                </tr>
            </table>
        </div>       
        <?php }
        else if ($action == 2){?>
		<!--- Afisare formular programare noua --->
        <h2><a href = javascript:window.history.back();><i class="fas fa-arrow-left"></i></a> Adaugare programare</h2>
        <h3><?php echo $full_name?></h3>
        <div class="horizontal-line"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add&id=" . $id); ?>" method="post">
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="motiv"><b>Motiv</b></label><br>
                        <input type="text" placeholder="ex. eliberare act identitate" name="motiv" required> 
                    </div>
                    <div class = "form_cell">
                        <label for="data_programare"><b>Data programare</b></label>
                        <br>
                        <table id="data_programare">
                            <tr>
                                <td>
                                <select name="zi_programare" required>
                                    <option disabled selected value> Zi </option>
                                    <?php for ($i = 1; $i <= 31; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="luna_programare" required>
                                <option disabled selected value> Luna </option>
                                    <?php for ($i = 1; $i <= 12; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="an_programare" required>
                                <option disabled selected value> An </option>
                                <?php for ($i = 2022; $i <= 2023; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>                                                                
                            </tr>
                        </table>
                    </div>
                    <div class = "form_cell">
                        <label for="timp_programare"><b>Ora programare</b></label>
                        <br>
                        <table id="timp_programare">
                            <tr>
                                <td>
                                <select name="ora_programare" required>
                                    <option disabled selected value> Ora </option>
                                    <?php for ($i = 8; $i <= 16; $i++){
                                        if($i < 10) {?>
                                    <option value=<?php echo $i?>><?php echo "0" . $i?></option>
                                    <?php } else {?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }
                                }?>
                                </select>
                                </td>
                                <td>
                                <select name="minut_programare" required>
                                <option disabled selected value> Minute </option>
                                    <?php for ($i = 0; $i <= 55; $i = $i + 5){
                                        if($i < 10) {?>
                                    <option value=<?php echo $i?>><?php echo "0" . $i?></option>
                                    <?php } else {?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }
                                }?>
                                </select>
                                </td>                                                               
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <button type="submit">Adaugare</button>
        </form>     
        <?php }
        else if ($action == 3){
        if(isset($_GET["m"]) && isset($_GET["y"])){?>
		<!--- Afisare programari din data selectata --->
        <h2><a href = javascript:window.history.back();><i class="fas fa-arrow-left"></i></a> Istoric programari</h2>
        <h3><?php
        if($_GET["m"] == 1) $month = "Ianuarie";
        if($_GET["m"] == 2) $month = "Februarie";
        if($_GET["m"] == 3) $month = "Martie";
        if($_GET["m"] == 4) $month = "Aprilie";
        if($_GET["m"] == 5) $month = "Mai";
        if($_GET["m"] == 6) $month = "Iunie";
        if($_GET["m"] == 7) $month = "Iulie";
        if($_GET["m"] == 8) $month = "August";
        if($_GET["m"] == 9) $month = "Septembrie";
        if($_GET["m"] == 10) $month = "Octombrie";
        if($_GET["m"] == 11) $month = "Noiembire";
        if($_GET["m"] == 12) $month = "Decembrie";

        echo $month . " " . $_GET["y"]?></h3>
        <div class="horizontal-line"></div>
            <table>
                <tr> 
                    <th> Nume complet </th>
                    <th> Motiv programare </th>
                    <th> Data </th>
                    <th> Ora </th>
                </tr>
                <?php if ($result) { 
                    while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                        <td><?php echo $row['Motiv']; ?></td>
                        <td><?php echo $row['Data']; ?></td>
                        <td><?php echo $row['Ora']; ?></td>
                    </tr>
                <?php }
                }?>
            </table>
        <?php }
        else {?>
		<!--- Afisare formular selectare data --->
        <h2><a href = javascript:window.history.back();><i class="fas fa-arrow-left"></i></a> Istoric programari</h2>
        <h3>Selectati luna si anul</h3>
        <div class="horizontal-line"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=history"); ?>" method="post">
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="data_programare"><b>Data programare</b></label>
                        <br>
                        <table id="data_programare">
                            <tr>
                                <td>
                                <select name="luna_programare" required>
                                <option disabled selected value> Luna </option>
                                    <?php for ($i = 1; $i <= 12; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="an_programare" required>
                                <option disabled selected value> An </option>
                                <?php for ($i = 2021; $i <= 2023; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>                                                                
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <button type="submit">Cautati</button>
        </form>     
        <?php }
        }?>
    </div>
    </body>
</html>