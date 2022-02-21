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
// Selectare utilizatori
$query = "SELECT * FROM persoane";
$result = mysqli_query($link, $query);

$action = 0;

if(isset($_GET["action"])){
	// Stergere utilizator
    if($_GET["action"] == "delete"){
        $id = $_GET["id"];
        $query = "DELETE FROM persoane WHERE PersoanaID = $id";
        mysqli_query($link, $query);
        header("location: persoane.php");
        exit;
    }
	// Adaugare utilizator
    else if($_GET["action"] == "add"){
        $action = 1;
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $nume = $_POST["nume"];
            $prenume = $_POST["prenume"];
            $cnp = $_POST["cnp"];
            $nr_tel = $_POST["nr_tel"];
            $strada = $_POST["strada"];
            $numar = $_POST["numar"];
            $email = $_POST["email"];
            $apartament = $_POST["apartament"];
            $bloc = $_POST["bloc"];
            $sex = $_POST["sex"];
            $data_nastere = $_POST["an"] . "-" . $_POST["luna"] . "-" . $_POST["zi"];
            $stare_civila = $_POST["stare_civila"];
 
            $query = "INSERT INTO persoane(Nume, Prenume, CNP, NumarTelefon, Email, Strada, Numar, Apartament, Bloc, Sex, DataNasterii, StareCivila, ActID) VALUES('$nume', '$prenume', '$cnp', '$nr_tel', '$email', '$strada', '$numar', '$apartament', '$bloc', '$sex', '$data_nastere', '$stare_civila', NULL)";
            
            if($result = mysqli_query($link, $query)){
                mysqli_close($link);
                header("location: persoane.php");
                exit;
            }    
        }
    }
	// Modificare utilizator
    else if($_GET["action"] == "modify") {
        $action = 2;
        $id = $_GET["id"];
        $select_query = "SELECT * FROM persoane WHERE PersoanaID = $id";
        $values = mysqli_fetch_array(mysqli_query($link, $select_query));
        $an = substr($values["DataNasterii"], 0, 4);
        $luna = substr($values["DataNasterii"], 5, 2);
        $zi = substr($values["DataNasterii"], 8, 2);

        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $nume = $_POST["nume"];
            $prenume = $_POST["prenume"];
            $cnp = $_POST["cnp"];
            $nr_tel = $_POST["nr_tel"];
            $email = $_POST["email"];
            $strada = $_POST["strada"];
            $numar = $_POST["numar"];
            $apartament = $_POST["apartament"];
            $bloc = $_POST["bloc"];
            $sex = $_POST["sex"];
            $data_nastere = $_POST["an"] . "-" . $_POST["luna"] . "-" . $_POST["zi"];
            $stare_civila = $_POST["stare_civila"];
            $query = "
            UPDATE persoane
            SET Nume = '$nume', Prenume = '$prenume', CNP = '$cnp', NumarTelefon = '$nr_tel', Email = '$email', Strada = '$strada', Numar = '$numar', Apartament = '$apartament', Bloc = '$bloc', Sex = '$sex', DataNasterii = '$data_nastere', StareCivila = '$stare_civila'
            WHERE PersoanaID = $id ";
            
            $result = mysqli_query($link, $query);
    
            if($result){
                mysqli_close($link);
                header("location: persoane.php");
                exit;
            }    
        }   
    }
	// Vizualizare completa utilizator, impreuna cu act de identitate
    else if($_GET["action"] == "view"){
        $action = 3;
        $id = $_GET["id"];

        $query = "SELECT *, p.Numar AS NumarAdresa FROM persoane p left join acteidentitate a on p.actid = a.actid where p.persoanaid = $id";
        
        $result = mysqli_query($link, $query);
        if($result){
            $row = mysqli_fetch_array($result);
        }
    }
	// Adaugare nou-nascut
    else if($_GET["action"] == "add2"){
        $action = 4;
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $nume = $_POST["nume"];
            $prenume = $_POST["prenume"];
            $cnp = $_POST["cnp"];
            $strada = $_POST["strada"];
            $numar = $_POST["numar"];
            $apartament = $_POST["apartament"];
            $bloc = $_POST["bloc"];
            $sex = $_POST["sex"];
            $data_nastere = $_POST["an"] . "-" . $_POST["luna"] . "-" . $_POST["zi"];
 
            $query = "INSERT INTO persoane(Nume, Prenume, CNP, Strada, Numar, Apartament, Bloc, Sex, DataNasterii, StareCivila) VALUES('$nume', '$prenume', '$cnp', '$strada', '$numar', '$apartament', '$bloc', '$sex', '$data_nastere', 'necasatorit')";
            if($result = mysqli_query($link, $query)){
                mysqli_close($link);
                header("location: persoane.php");
                exit;
            }    
        }
    }
}

mysqli_close($link);
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Inregistrare persoane</title>
        <link rel="stylesheet" type="text/css" href="style.css">
		<link rel="icon" href="img/flag.png">
    </head>
    <body class="persoane"> 
        <?php include "header.php" ?>
        <div class="center">
        <?php if($action == 0) {?>
		<!--- Afisare persoane --->
		<h2> Persoane inregistrate</h2>
		<div class="horizontal-line"></div>
		<table>
            <tr> 
                <th> Nume complet </th>
                <th> CNP </th>
                <th> Actiuni </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){ ?>
                <tr>
                    <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                    <td><?php echo $row['CNP']?> </td>
                    <td>
                    <a href = "persoane.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                    <a href = "persoane.php?action=modify&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-edit"></i></a>
                    <a href = "persoane.php?action=delete&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php }
            }?>
        </table>
        <div class="center_footer">
            <table>
                <tr>
                    <th><a href = "persoane.php?action=add"><i class="fas fa-plus"></i> Adauga persoana</a></th>  
                </tr>
            </table>
        </div>
        
        <?php }
        else if ($action == 1){ ?>
		<!--- Formular adaugare persoana --->
        <h2><a href="javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Inregistrare persoana </h2>
        <div class="horizontal-line"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add"); ?>" method="post">
            <p class="form_heading">Date personale</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="nume"><b>Nume</b></label>
                        <br>
                        <input type="text" placeholder="ex. Popescu" name="nume" required>
                    </div>
                    <div class = "form_cell">
                        <label for="prenume"><b>Prenume</b></label>
                        <br>
                        <input type="text" placeholder="ex. Andrei" name="prenume" required>
                    </div>
                    <div class = "form_cell">
                        <label for="cnp"><b>CNP</b></label>
                        <br>
                        <input type="text" placeholder="ex. 1111111111111" name="cnp" required>
                    </div>
                </div>
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="data_nastere"><b>Data nasterii</b></label>
                        <br>
                        <table id="data_nastere">
                            <tr>
                                <td>
                                <select name="zi" required>
                                    <option disabled selected value> Zi </option>
                                    <?php for ($i = 1; $i <= 31; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="luna" required>
                                <option disabled selected value> Luna </option>
                                    <?php for ($i = 1; $i <= 12; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="an" required>
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
                        <label for="sex"><b>Sex</b></label>
                        <br>
                        <select name="sex" required>
                            <option disabled selected value> Selectati </option>
                            <option value="M">Masculin</option>
                            <option value="F">Feminin</option>
                        </select>
                    </div>
                    <div class = "form_cell">
                        <label for="stare_civila"><b>Stare civila</b></label>
                        <br>
                        <select name="stare_civila" required>
                            <option disabled selected value> Selectati </option>
                            <option value="casatorit">Casatorit(a)</option>
                            <option value="necasatorit">Necasatorit(a)</option>
                            <option value="divortat">Divortat(a)</option>
                            <option value="vaduv">Vaduv(a)</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>
            <p class="form_heading">Adresa</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="strada"><b>Strada</b></label>
                        <br>
                        <input type="text" placeholder="ex. Unirii" name="strada" required>
                    </div>
                    <div class = "form_cell">
                        <label for="numar"><b>Numar</b></label>
                        <br>
                        <input type="text" placeholder="ex. 1" name="numar" required>
                    </div>
                    <div class = "form_cell">
                        <label for="apartament"><b>Apartament</b></label>
                        <br>
                        <input type="text" placeholder="ex. 22" name="apartament">
                    </div>
                    <div class = "form_cell">
                    <label for="bloc"><b>Bloc</b></label>
                    <br>
                    <input type="text" placeholder="ex. 1A" name="bloc">
                    </div>
                </div>
            </div>
            <br>
            <p class="form_heading">Date de contact</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                    <label for="nr_tel"><b>Numar de telefon</b></label>
                        <br>
                        <input type="text" placeholder="ex. 0720000000" name="nr_tel">
                    </div>
                    <div class = "form_cell">
                        <label for="email"><b>Adresa de email</b></label>
                        <br>
                        <input type="text" placeholder="ex. nume@companie.ro" name="email">
                    </div>
                </div>
            </div>
            <button type="submit">Inregistrare</button>
        </form> 
        <?php }
        else if ($action == 2){?>
		<!--- Formular actualizare persoana --->
        <h2><a href="javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Actualizare persoana </h2>
		<div class="horizontal-line"></div>
            <?php if ($values) {?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=modify&id=$id"); ?>" method="post">
                    <p class="form_heading">Date personale</p>
                    <div class="horizontal-line-2"></div>
                    <div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
                                <label for="nume"><b>Nume</b></label>
                                <br>
                                <input type="text" placeholder="ex. Popescu" name="nume" value = <?php echo $values["Nume"] ?> required>
                            </div>
                            <div class = "form_cell">
                                <label for="prenume"><b>Prenume</b></label>
                                <br>
                                <input type="text" placeholder="ex. Andrei" name="prenume" value = <?php echo $values["Prenume"] ?> required>
                            </div>
                            <div class = "form_cell">
                                <label for="cnp"><b>CNP</b></label>
                                <br>
                                <input type="text" placeholder="ex. 1111111111111" name="cnp" value = <?php echo $values["CNP"] ?> required>
                            </div>
                        </div>
                        <div class = "form_row">
                            <div class = "form_cell">
                                <label for="data_nastere"><b>Data nasterii</b></label>
                                <br>
                                <table id="data_nastere">
                                    <tr>
                                        <td>
                                        <select name="zi" required>
                                        <?php for ($i = 1; $i <= 31; $i++){
                                            if ($i == $zi){?>
                                            <option selected value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }
                                            else {?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                        <?php }
                                        }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="luna" required>
                                        <?php for ($i = 1; $i <= 12; $i++){
                                            if ($i == $luna){?>
                                            <option selected value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }
                                            else {?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                        <?php }
                                        }?>
                                        </select>
                                        </td>
                                        <td>
                                        <select name="an" required>
                                        <?php for ($i = 2022; $i >= 1900; $i--){
                                            if ($i == $an){?>
                                            <option selected value=<?php echo $i?>><?php echo $i?></option>
                                            <?php }
                                            else {?>
                                            <option value=<?php echo $i?>><?php echo $i?></option>
                                        <?php }
                                        }?>
                                        </select>
                                        </td>                                                                
                                    </tr>
                                </table>
                            </div>
                            <div class = "form_cell">
                                <label for="sex"><b>Sex</b></label>
                                <br>
                                <select name="sex" required>
                                    <?php if($values["Sex"] == 'M'){?>
                                    <option value="M" selected>Masculin</option>
                                    <option value="F">Feminin</option>
                                    <?php } else {?>
                                    <option value="M">Masculin</option>
                                    <option value="F" selected>Feminin</option>
                                    <?php }?>    
                                </select>
                            </div>
                            <div class = "form_cell">
                                <label for="stare_civila"><b>Stare civila</b></label>
                                <br>
                                <select name="stare_civila" required>
                                <?php if($values["StareCivila"] == "casatorit"){?>
                                    <option value="casatorit" selected>Casatorit(a)</option>
                                    <option value="necasatorit">Necasatorit(a)</option>
                                    <option value="divortat">Divortat(a)</option>
                                    <option value="vaduv">Vaduv(a)</option>
                                <?php } else if($values["StareCivila"] == "necasatorit"){?>
                                    <option value="casatorit">Casatorit(a)</option>
                                    <option value="necasatorit" selected>Necasatorit(a)</option>
                                    <option value="divortat">Divortat(a)</option>
                                    <option value="vaduv">Vaduv(a)</option>
                                <?php } else if($values["StareCivila"] == "divortat"){?>
                                    <option value="casatorit">Casatorit(a)</option>
                                    <option value="necasatorit" >Necasatorit(a)</option>
                                    <option value="divortat" selected>Divortat(a)</option>
                                    <option value="vaduv">Vaduv(a)</option>
                                <?php } else if($values["StareCivila"] == "vaduv"){?>
                                    <option value="casatorit">Casatorit(a)</option>
                                    <option value="necasatorit">Necasatorit(a)</option>
                                    <option value="divortat">Divortat(a)</option>
                                    <option value="vaduv" selected>Vaduv(a)</option>
                                <?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <br>
                    <p class="form_heading">Adresa</p>
                    <div class="horizontal-line-2"></div>
                    <div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
                                <label for="strada"><b>Strada</b></label>
                                <br>
                                <input type="text" placeholder="ex. Unirii" name="strada" value = <?php echo $values["Strada"]?> required>
                            </div>
                            <div class = "form_cell">
                                <label for="numar"><b>Numar</b></label>
                                <br>
                                <input type="text" placeholder="ex. 1" name="numar" value = <?php echo $values["Numar"]?> required>
                            </div>
                            <div class = "form_cell">
                                <label for="bloc"><b>Bloc</b></label>
                                <br>
                                <input type="text" placeholder="ex. 1A" name="bloc" value = <?php echo $values["Bloc"]?>>
                            </div>
                            <div class = "form_cell">
                                <label for="apartament"><b>Apartament</b></label>
                                <br>
                                <input type="text" placeholder="ex. 22" name="apartament" value = <?php echo $values["Apartament"]?>>
                            </div>
                        </div>
                    </div>
                    <br>
                    <p class="form_heading">Date de contact</p>
                    <div class="horizontal-line-2"></div>
                    <div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
                            <label for="nr_tel"><b>Numar de telefon</b></label>
                                <br>
                                <input type="text" placeholder="ex. 0720000000" name="nr_tel" value = <?php echo $values["NumarTelefon"] ?>>
                            </div>
                            <div class = "form_cell">
                                <label for="email"><b>Adresa de email</b></label>
                                <br>
                                <input type="text" placeholder="ex. nume@companie.ro" name="email" value = <?php echo $values["Email"] ?>>
                            </div>
                        </div>
                    </div>                                        
                    <button type="submit">Actualizare</button>
                    </form> 
            <?php }
                }
        else if ($action == 3){ ?>
		<!--- Vizualizare completa persoana --->
		<h2><a href="javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Vizualizare persoana</h2>
    
        <h3><?php echo $row["Nume"] . " " . $row["Prenume"]?></h3>
		<div class="horizontal-line"></div>
                <div class = "div-table" id="info">
                    <div class = "div-row" id="info-row">
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">CNP</div>
                            <div id="info-data">
                                <?php echo $row["CNP"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Data nasterii</div>
                            <div id="info-data">
                                <?php echo $row["DataNasterii"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Sex</div>
                            <div id="info-data">
                                <?php echo $row["Sex"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Stare civila</div>
                            <div id="info-data">
                                <?php echo $row["StareCivila"]?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "div-table" id="info">
                    <div class = "div-row">
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Adresa domiciliu</div>
                            <div id="info-data">
                            <?php
                            $adr = $row["Strada"] . ", " . $row["NumarAdresa"];
                            echo $adr?>
                            </div>
                            <?php if($row["Apartament"] != "" && $row["Bloc"] != "") {?>
                                <div id="info-header"><?php echo "Bloc " . $row["Bloc"] . ", Ap. " . $row["Apartament"]?></div>
                                
                                <?php }?>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Numar telefon</div>
                            <div id="info-data">
                                <?php echo $row["NumarTelefon"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Adresa email</div>
                            <div id="info-data">
                                <?php echo $row["Email"]?>
                            </div>
                        </div>
                    </div>
                </div>
            <br>    
            <h3>Act identitate</h3>
            <div class="horizontal-line-2" id="closer-line"></div>
            <div id="act-div">
                <?php if (!is_null($row["ActID"])) {?> 
                <div class = "div-table" id="info">
                    <div class = "div-row">
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Serie</div>
                            <div id="info-data">
                                <?php echo $row["Serie"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Numar</div>
                            <div id="info-data">
                                <?php echo $row["Numar"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Tip</div>
                            <div id="info-data">
                                <?php echo $row["Tip"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Data emitere</div>
                            <div id="info-data">
                                <?php echo $row["DataEmitere"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Data expirare</div>
                            <div id="info-data">
                                <?php echo $row["DataExpirare"]?>
                            </div>
                        </div>
                        <div class="div-cell" id="info-cell">
                            <div id="info-header">Autoritate emitenta</div>
                            <div id="info-data">
                                <?php echo $row["AutoritateEmitenta"]?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }
                else {?>
                <div class = "div-table" id="warning_alert">
                    <div class = "div-row">
                        <div class="div-cell" id="warning_icon">
                        <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="div-cell" id="warning_message">
                           Persoana nu are act de identitate emis.
                        </div>  
                    </div>
                </div>
                <?php }?>
            </div>
        <div class="center_footer">
            <table>
                <tr>
                    <th><a href = <?php echo "programari.php?action=view&id=" . $id?>><i class="fas fa-calendar-check"></i> Programari</a></th>
                    <th><a href = <?php echo "impozite.php?action=view&id=" . $id?>><i class="fas fa-receipt"></i> Impozite</a></th> 
                    <th><a href = <?php echo "proprietati.php?action=view&id=" . $id?>><i class="fas fa-building"></i> Proprietati</a></th>        
                </tr>
            </table>
        </div>
        <?php }
        else if ($action == 4){ ?>
		<!--- Formular adaugare nou-nascut --->
        <h2><a href="javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Inregistrare nastere </h2>
        <div class="horizontal-line"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add2"); ?>" method="post">
            <p class="form_heading">Date personale</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="nume"><b>Nume</b></label>
                        <br>
                        <input type="text" placeholder="ex. Popescu" name="nume" required>
                    </div>
                    <div class = "form_cell">
                        <label for="prenume"><b>Prenume</b></label>
                        <br>
                        <input type="text" placeholder="ex. Andrei" name="prenume" required>
                    </div>
                    <div class = "form_cell">
                        <label for="cnp"><b>CNP</b></label>
                        <br>
                        <input type="text" placeholder="ex. 1111111111111" name="cnp" required>
                    </div>
                </div>
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="data_nastere"><b>Data nasterii</b></label>
                        <br>
                        <table id="data_nastere">
                            <tr>
                                <td>
                                <select name="zi" required>
                                    <option disabled selected value> Zi </option>
                                    <?php for ($i = 1; $i <= 31; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="luna" required>
                                <option disabled selected value> Luna </option>
                                    <?php for ($i = 1; $i <= 12; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="an" required>
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
                        <label for="sex"><b>Sex</b></label>
                        <br>
                        <select name="sex" required>
                            <option disabled selected value> Selectati </option>
                            <option value="M">Masculin</option>
                            <option value="F">Feminin</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>
            <p class="form_heading">Adresa</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="strada"><b>Strada</b></label>
                        <br>
                        <input type="text" placeholder="ex. Unirii" name="strada" required>
                    </div>
                    <div class = "form_cell">
                        <label for="numar"><b>Numar</b></label>
                        <br>
                        <input type="text" placeholder="ex. 1" name="numar" required>
                    </div>
                    <div class = "form_cell">
                        <label for="apartament"><b>Apartament</b></label>
                        <br>
                        <input type="text" placeholder="ex. 22" name="apartament">
                    </div>
                    <div class = "form_cell">
                    <label for="bloc"><b>Bloc</b></label>
                    <br>
                    <input type="text" placeholder="ex. 1A" name="bloc">
                    </div>
                </div>
            </div>
            <button type="submit">Inregistrare</button>
        </form>
        <?php } ?> 
        </div>  
    </body>
</html>