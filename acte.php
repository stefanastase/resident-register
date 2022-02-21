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

// In cazul in care new este setat se vor afisa doar utilizatorii ce nu au acte de identitate
if(isset($_GET["new"]))
    $query = "SELECT PersoanaID, Nume, Prenume, ActID FROM persoane WHERE ActID IS null";
else
    $query = "SELECT PersoanaID, Nume, Prenume, ActID FROM persoane";

$result = mysqli_query($link, $query);

$action = 0;
$act_id = "";

if(isset($_GET["action"])){
	// Vizualizare acte de identitate ale unei persoane (act curent si istoric)
    if($_GET["action"] == "view"){
        $action = 1;
        $id = $_GET["id"];
		// Obtinere nume complet al persoanei
        $query = "SELECT Nume, Prenume FROM persoane WHERE PersoanaID = $id";
        if($result = mysqli_query($link, $query)){
            $row = mysqli_fetch_array($result);
            $full_name = $row['Nume'] . " " . $row['Prenume'];
        }
        $query = "SELECT A.* FROM acteidentitate A JOIN persoane P ON P.ActID = A.ActID WHERE P.PersoanaID = $id";
        $result = mysqli_query($link, $query);
    }
	// Adaugare act de identitate nou
    if($_GET["action"] == "add"){
        $action = 2;
        $id = $_GET["id"];
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $serie = $_POST["serie"];
            $numar = $_POST["numar"];
            $tip = $_POST["tip"];
            $data_emitere = $_POST["an_emitere"] . "-". $_POST["luna_emitere"] . "-" . $_POST["zi_emitere"];
            $data_expirare = $_POST["an_expirare"] . "-". $_POST["luna_expirare"] . "-" . $_POST["zi_expirare"];
            $autoritate_emitenta = $_POST["autoritate_emitenta"];
            $curent = $_POST["curent"];
			// Adaugare act de identitate nou
            $query = "
                INSERT INTO acteidentitate(Serie, Numar, Tip,  DataEmitere, DataExpirare, AutoritateEmitenta, PersoanaID)
                VALUES('$serie', '$numar', '$tip', '$data_emitere', '$data_expirare', '$autoritate_emitenta', $id)";
            if(mysqli_query($link, $query)){
                if($curent == "Yes"){
					// Adaugare act la persoana
                    $query = "SELECT ActID FROM acteidentitate WHERE PersoanaID = $id ORDER BY ActID DESC";
                    if($result = mysqli_query($link, $query)){
                        $row = mysqli_fetch_array($result);
                        $a_id = $row["ActID"];
                        $query = "UPDATE persoane SET ActID = $a_id WHERE PersoanaID = $id";
                        if(mysqli_query($link, $query)){
                            header("location: acte.php?action=view&id=" . $id);
                            exit;    
                        }
                    }
                }
                header("location: acte.php?action=view&id=" . $id);
                exit;                
            }    
        }
    }
	// Stergere act identitate
    if($_GET["action"] == "delete"){
        $id = $_GET["id"];
        $query = "SELECT PersoanaID FROM acteidentitate WHERE ActID = $id";
        if($result = mysqli_query($link, $query)) {
            $row = mysqli_fetch_array($result);
            $persoana_id = $row['PersoanaID'];
            if(isset($_GET["current"]) && $_GET["current"] == "1"){
                $query = "UPDATE persoane SET ActId = null WHERE PersoanaID = $persoana_id";
                mysqli_query($link, $query);
                }
            $query = "
                DELETE FROM acteidentitate
                WHERE ActID = $id";
            if(mysqli_query($link, $query)){
                header("location: acte.php?action=view&id=" . $persoana_id);
                exit;                
            }
        }           
    }
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Inregistrare acte de identitate</title>
        <link rel="stylesheet" type="text/css" href="style.css">
		<link rel="icon" href="img/flag.png">
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $("#istoric_button").click(function(){
                    $("#istoric").slideToggle(100);
                });
            });
        </script>
    </head>
    <body class="acte"> 
        <?php include "header.php" ?>

        <div class="center">
		<!--- Afisare persoane si status-ul actului de identitate --->
        <?php if($action == 0) {?>
		<h2> Acte identitate</h2>
        <div class="horizontal-line"></div>
		<table>
            <tr> 
                <th> Nume complet </th>
                <th> Status </th>
                <th> Actiuni </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){
                    if($row['ActID'] == null) {
                        $status = "inexistent";
                        $color = "red";
                    }
                    else {
                        $status = "emis";
                        $color = "green";
                    }?>
                <tr>
                    <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                    <td style="<?php echo "color: " . $color;?>"><?php echo $status ?></td>
                    <td>
                    <?php
                    if(isset($_GET["new"])) {?>
                    <a href = "acte.php?action=add&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-plus"></i></a>
                    <?php } 
                    else {?>
                    <a href = "acte.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                    <?php }?>
                    </td>
                </tr>
            <?php }
            }?>
        </table>
        <?php if(isset($_GET["new"])) {?>
            <div class="center_footer">
                <table>
                    <tr>
                        <th><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i> Inapoi</a></th>      
                    </tr>
                </table>
            </div>
        <?php }
        }
        else if ($action == 1){ ?>
		<!--- Afisare acte de identitate pentru o anumita persoana --->
        <h2>Acte identitate</h2>
        <h3><?php echo $full_name?></h3>
        <div class="horizontal-line"></div>
        <table>
            <tr> 
                <th> Serie </th>
                <th> Numar </th>
                <th> Tip </th>
                <th> Data emitere </th>
                <th> Data expirare </th>
                <th> Autoritate Emitenta </th>
                <th> Actiuni </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){ ?>
                <?php $act_id = $row['ActID']; ?>
                <tr>
                    <td><?php echo $row['Serie'] ?> </td>
                    <td><?php echo $row['Numar']?> </td>
                    <td><?php echo $row['Tip'] ?> </td>
                    <td><?php echo $row['DataEmitere'] ?> </td>
                    <td><?php echo $row['DataExpirare'] ?> </td>
                    <td><?php echo $row['AutoritateEmitenta'] ?> </td>
                    <td><a href = "acte.php?action=delete&current=1&id=<?php echo $row["ActID"];?>"><i class="fas fa-trash"></i></a></td>
                </tr>
            <?php }
            }?>
        </table>
        <div id="istoric">
            <p class="heading"> Istoric </p>
            <br>
            <div class="horizontal-line-2"></div>
            <table>
                <tr> 
                    <th> Serie </th>
                    <th> Numar </th>
                    <th> Tip </th>
                    <th> Data emitere </th>
                    <th> Data expirare </th>
                    <th> Autoritate Emitenta </th>
                    <th> Actiuni </th>
                </tr>
                <?php
                if ($act_id != ""){
                    $query = "SELECT * FROM acteidentitate WHERE PersoanaID = $id AND ActID != $act_id";
                }
                else {
                    $query = "SELECT * FROM acteidentitate WHERE PersoanaID = $id";
                }
                $result = mysqli_query($link, $query); ?>
                <?php if ($result) { 
                    while($row = mysqli_fetch_array($result)){ ?>
                    <tr>
                        <td><?php echo $row['Serie'] ?> </td>
                        <td><?php echo $row['Numar']?> </td>
                        <td><?php echo $row['Tip'] ?> </td>
                        <td><?php echo $row['DataEmitere'] ?> </td>
                        <td><?php echo $row['DataExpirare'] ?> </td>
                        <td><?php echo $row['AutoritateEmitenta'] ?> </td>
                        <td><a href = "acte.php?action=delete&id=<?php echo $row["ActID"];?>"><i class="fas fa-trash"></i></a></td>
                    </tr>
                <?php }
                }?>
            </table>
        </div>
        <div class="center_footer">
            <table>
                <tr>
                    <th><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i> Inapoi</a></th>
                    <th><a href = "acte.php?action=add&id=<?php echo $id;?>"><i class="fas fa-plus"></i> Adauga</a></th> 
                    <th><a href = "#" id="istoric_button"><i class="fas fa-history"></i> Istoric</a></th>        
                </tr>
            </table>
        </div>
        <?php }
        else if ($action == 2){?>
		<!--- Afisare formular introducere act de identitate--->
        <h2><a href="javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Adaugare act identitate </h2>
        <div class="horizontal-line"></div>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add&id=" . $id); ?>" method="post">
            <p class="form_heading">Date document</p>
            <div class="horizontal-line-2"></div>
            <div class="form_table">
                <div class = "form_row">
                    <div class = "form_cell">
                        <label for="serie"><b>Serie</b></label>
                        <br>
                        <input type="text" placeholder="ex. PX" name="serie" required>
                    </div>
                    <div class = "form_cell">
                        <label for="numar"><b>Numar</b></label>
                        <br>
                        <input type="text" placeholder="ex. 123456" name="numar" required>
                    </div>
                    <div class = "form_cell">
                        <label for="autoritate_emitenta"><b>Autoritate Emitenta</b></label>
                        <br>
                        <input type="text" placeholder="ex. SPCLEP Sectorul 6" name="autoritate_emitenta" required>
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
                        <label for="data_expirare"><b>Data expirare</b></label>
                        <br>
                        <table id="data_expirare">
                            <tr>
                                <td>
                                <select name="zi_expirare" required>
                                    <option disabled selected value> Zi </option>
                                    <?php for ($i = 1; $i <= 31; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="luna_expirare" required>
                                <option disabled selected value> Luna </option>
                                    <?php for ($i = 1; $i <= 12; $i++){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>
                                <td>
                                <select name="an_expirare" required>
                                <option disabled selected value> An </option>
                                <?php for ($i = 2040; $i >= 1950; $i--){?>
                                    <option value=<?php echo $i?>><?php echo $i?></option>
                                    <?php }?>
                                </select>
                                </td>                                                                
                            </tr>
                        </table>
                    </div>
                    <div class = "form_cell">
                        <label for="tip"><b>Tip</b></label>
                        <br>
                        <select name="tip" required>
                            <option disabled selected value> Selectati </option>
                            <option value="C">Clasic</option>
                            <option value="B">Biometric</option>
                        </select>
                    </div>
                </div>
            </div>
            <br>
            <div class="horizontal-line-2"></div> 
            <div class="form_table" id="checkbox">
                <div class="form_row">
                    <div class="form_cell">
                        <label for="curent"><b>Actul este cel utilizat in prezent</b></label>
                    </div>

                    <div class="form_cell" >
                        <input type="checkbox" name="curent" value = "Yes">
                    </div>
                </div>
            </div>
            <button type="submit">Adaugare</button>
            </form>     
            <?php }?>
    </body>
</html>