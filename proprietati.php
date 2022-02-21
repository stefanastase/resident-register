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

$action = 0;
$type = 0;
// In cazul in care avem de cautat vreun tip de proprietate setam $type
if(isset($_GET["type"]))
    $type = $_GET["type"];

if ($type == 0)
    // Numar proprietati 
    $query = "
        select p.PersoanaID, p.Nume, p.Prenume, count(ppr.ProprietateID) AS NumarProprietati
        from persoaneproprietati ppr right join persoane p on ppr.PersoanaID = p.PersoanaID
        group by p.PersoanaID, p.Nume, p.Prenume";

else if ($type == 1)
	// Numar terenuri 
    $query = "
    select p.PersoanaID, p.Nume, p.Prenume, count(ppr.ProprietateID) AS NumarProprietati
    from persoaneproprietati ppr right join persoane p on ppr.PersoanaID = p.PersoanaID
    where ppr.ProprietateID IN (SELECT ProprietateID FROM proprietati WHERE Categoria='teren')
    group by p.PersoanaID, p.Nume, p.Prenume";

else if ($type == 2)
	// Numar case 
    $query = "
    select p.PersoanaID, p.Nume, p.Prenume, count(ppr.ProprietateID) AS NumarProprietati
    from persoaneproprietati ppr right join persoane p on ppr.PersoanaID = p.PersoanaID
    where ppr.ProprietateID IN (SELECT ProprietateID FROM proprietati WHERE Categoria='casa')
    group by p.PersoanaID, p.Nume, p.Prenume";

else if ($type == 3)
	// Numar apartamente 
    $query = "
    select p.PersoanaID, p.Nume, p.Prenume, count(ppr.ProprietateID) AS NumarProprietati
    from persoaneproprietati ppr right join persoane p on ppr.PersoanaID = p.PersoanaID
    where ppr.ProprietateID IN (SELECT ProprietateID FROM proprietati WHERE Categoria='apartament')
    group by p.PersoanaID, p.Nume, p.Prenume";
$result = mysqli_query($link, $query);



$full_name = "";
$id = "";

if(isset($_GET["action"])){
	// Vizualizare toate proprietatile unei persoane
    if($_GET["action"] == "view"){
        $action = 1;
        if(isset($_GET["id"])){
            $id = $_GET["id"];
            $query = "SELECT Nume, Prenume FROM persoane WHERE PersoanaID = $id";
            if($result = mysqli_query($link, $query)){
                $row = mysqli_fetch_array($result);
                $full_name = $row['Nume'] . " " . $row['Prenume'];
            }
            $query = "
                SELECT pr.* 
                FROM persoaneproprietati ppr JOIN proprietati pr
                ON ppr.ProprietateID = pr.ProprietateID
                WHERE ppr.PersoanaID = $id";
        }
        else {
            $query = "SELECT * FROM Proprietati";
        }
        $result = mysqli_query($link, $query);
    }

    if($_GET["action"] == "add"){
		// Adugare proprietate
        $action = 2;
        if(isset($_GET["id"]))
            $id = $_GET["id"];
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            $categorie = $_POST["categorie"];
            $strada = $_POST["strada"];
            $numar = $_POST["numar"];
            $bloc = $_POST["bloc"];
            $apartament = $_POST["apartament"];
            $suprafata = $_POST["suprafata"];
            $count = 0;
            if(!empty($_POST['id_list'])) {
                foreach($_POST['id_list'] as $persoanaid){
                    $count = $count + 1;
                }
            }
            $query = "
            INSERT INTO proprietati(Categoria, Strada, Numar, Bloc, Apartament, Suprafata, NumarProprietari)
            VALUES('$categorie', '$strada', '$numar', '$bloc', '$apartament', '$suprafata', $count)";
            
            $data_dobandire = $_POST["an_dobandire"] . "-" . $_POST["luna_dobandire"] . "-" . $_POST["zi_dobandire"];
            if(mysqli_query($link, $query)){
                // Query pentru obtinerea id-ului noii proprietati adaugate
                $query = "SELECT ProprietateID FROM proprietati ORDER BY ProprietateID DESC";
                $proprietate_id = "";
                if($result = mysqli_query($link, $query)){
                    $row = mysqli_fetch_array($result);
                    $proprietate_id = $row["ProprietateID"];
                }
				// Realizare legatura cu tabela persoaneproprietati
                if(!empty($_POST['id_list'])) {
                    foreach($_POST['id_list'] as $persoana_id){
                        $query = "INSERT INTO persoaneproprietati VALUES($persoana_id, $proprietate_id, '$data_dobandire')";
                        mysqli_query($link, $query);
                    }
                }
                if ($id != "")
                    header("location: proprietati.php?action=view&id=" . $id);
                else
                    header("location: proprietati.php?action=view");
                exit;                
            }    
        }
    }
	// Stergere proprietate
    if($_GET["action"] == "delete"){
        $id = $_GET["id"];
        $pid = "";
        if (isset($_GET["pid"])) $pid = $_GET["pid"];
        $query = "
            DELETE FROM persoaneproprietati
            WHERE ProprietateID = $id";
            if(mysqli_query($link, $query)){
                $query = "DELETE FROM proprietati WHERE ProprietateID = $id";
                if(mysqli_query($link, $query)){
                    if($pid != "") header("location: proprietati.php?action=view&id=$pid");
                    else header("location: proprietati.php?action=view");
                    exit;
                }                
            }           
    }
}

?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Proprietati</title>
        <link rel="stylesheet" type="text/css" href="style.css">
		<link rel="icon" href="img/flag.png">
    </head>
    <body class="proprietati"> 
        <?php include "header.php" ?>
		<!--- Pagina difera in functie de filtrul de proprietate selectat --->
        <div class="center">
        <?php if($action == 0) {?>
        <?php if ($type == 0) {?>
        <h2> Proprietati</h2>
        <?php } else if ($type == 1) {?>
        <h2> Terenuri</h2>
        <?php } else if ($type == 2) {?>
        <h2> Case</h2>
        <?php } else if ($type == 3) {?>
        <h2> Apartamente</h2>
        <?php }?>
        <h2 id = "loc-icons">
            <?php if($type == 1) {?>
            <a id = "selected-icon" href="proprietati.php"><i class="fas fa-map"></i></a> 
            <a id = "unselected-icon" href="proprietati.php?type=2"><i class="fas fa-home"></i></a> 
            <a id = "unselected-icon" href="proprietati.php?type=3"><i class="fas fa-building"></i></a>
            <?php } else if($type == 2) {?>
            <a id = "unselected-icon" href="proprietati.php?type=1"><i class="fas fa-map"></i></a> 
            <a id = "selected-icon" href="proprietati.php"><i class="fas fa-home"></i></a> 
            <a id = "unselected-icon" href="proprietati.php?type=3"><i class="fas fa-building"></i></a>   
            <?php } else if($type == 3) {?>
            <a id = "unselected-icon" href="proprietati.php?type=1"><i class="fas fa-map"></i></a> 
            <a id = "unselected-icon" href="proprietati.php?type=2"><i class="fas fa-home"></i></a> 
            <a id = "selected-icon" href="proprietati.php"><i class="fas fa-building"></i></a>    
            <?php } else {?>
            <a id = "selected-icon" href="proprietati.php?type=1"><i class="fas fa-map"></i></a> 
            <a id = "selected-icon" href="proprietati.php?type=2"><i class="fas fa-home"></i></a> 
            <a id = "selected-icon" href="proprietati.php?type=3"><i class="fas fa-building"></i></a>  
            <?php } ?>         
        </h2>
        
        <div class="horizontal-line"></div>
		<table>
            <tr> 
                <th> Nume proprietar </th>
                <th> Numar proprietati </th>
                <th> Actiuni </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){ ?>
                <tr>
                    <td><?php echo $row['Nume'] . " " . $row['Prenume']; ?> </td>
                    <td><?php echo $row['NumarProprietati']; ?></td>
                    <td> 
                    <a href = "proprietati.php?action=view&id=<?php echo $row['PersoanaID'] ?>"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
            <?php }
            }?>
			<!--- Calcul total proprietati din tipul selectat --->
            <?php
            if ($type == 0)
                $query = "SELECT COUNT(*) AS Total FROM Proprietati";
            else if ($type == 1)
                $query = "SELECT COUNT(*) AS Total FROM Proprietati WHERE Categoria ='teren'";
            else if ($type == 2)
                $query = "SELECT COUNT(*) AS Total FROM Proprietati WHERE Categoria ='casa'";
            else if ($type == 3)
                $query = "SELECT COUNT(*) AS Total FROM Proprietati WHERE Categoria ='apartament'";
            if($result = mysqli_query($link, $query)){
                $row = mysqli_fetch_array($result);?>
                
                <tr>
                        <th>Total</th>
                        <th><?php echo $row['Total']; ?></th>
                        <th> 
                        <a href = "proprietati.php?action=view"><i class="fas fa-eye"></i></a>
                        </th>
                    </tr>
                <?php } ?>
        </table>
        <?php }
        else if ($action == 1){ ?>
		<!--- Afisare toate proprietatile unei persoane --->
        <h2>Proprietati</h2>
        <h3><?php echo $full_name?></h3>
        <div class="horizontal-line"></div>
        <table>
            <tr> 
                <th> Categoria </th>
                <th> Adresa </th>
                <th> Suprafata </th>
                <th> Actiuni </th>
            </tr>
            <?php if ($result) { 
                while($row = mysqli_fetch_array($result)){ ?>
                <tr>
                    <td><?php echo $row['Categoria'] ?> </td>
                    <?php 
                    $adr = "Str. " . $row["Strada"] . ", Nr. " . $row["Numar"];
                    if ($row["Apartament"] != "" && $row["Bloc"] != "") $adr = $adr . ", Bl. " . $row["Bloc"] . ", Ap. " . $row["Apartament"]?>
                    <td><?php echo $adr ?> </td>
                    <td><?php echo $row['Suprafata'] ?> mp</td>
                    <?php if ($id != "") {?>
                    <td><a href = "proprietati.php?action=delete&pid=<?php echo $id; ?>&id=<?php echo $row["ProprietateID"];?>"><i class="fas fa-trash"></i></a></td>
                    <?php } else {?>   
                    <td><a href = "proprietati.php?action=delete&id=<?php echo $row["ProprietateID"];?>"><i class="fas fa-trash"></i></a></td>   
                    <?php } ?>
                </tr>
            <?php }
            }?>
        </table>
        <div class="center_footer">
            <table>
                <tr>
                    <th><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i> Inapoi </a></th>
                    <th>
                    <?php if ($id != "") {?>
                    <a href = "proprietati.php?action=add&id=<?php echo $id;?>"><i class="fas fa-plus"></i> Adauga</a></a>
                    <?php } else {?>   
                    <a href = "proprietati.php?action=add"><i class="fas fa-plus"></i> Adauga</a></a>
                    <?php } ?>
                    </th>      
                </tr>
            </table>
        </div>        
        <?php }
        else if ($action == 2){?>
		<!--- Formular introducere proprietate --->
        <h2><a href = "javascript:window.history.back();"><i class="fas fa-arrow-left"></i></a> Adaugare proprietate noua</h2>
        <div class="horizontal-line"></div> 
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add&id=" . $id); ?>" method="post">
        <p class="form_heading">Detalii proprietate</p>
        <div class="horizontal-line-2"></div>
        <div class="form_table"> 
            <div class="form_row"> 
                <div class="form_cell">
                    <label for="categorie"><b>Tip</b></label>
                    <br>
                    <select name="categorie" required>
                        <option disabled selected value> Alegeti </option>
                        <option value="casa">Casa</option>
                        <option value="apartament">Apartament</option>
                        <option value="teren">Teren</option>
                    </select> 
                </div> 
                <div class="form_cell">
                    <label for="suprafata"><b>Suprafata</b></label>
                    <br>
                    <input type="text" placeholder="ex. 200" name="suprafata" required> 
                </div>      
                <div class = "form_cell">
                    <label for="data_dobandire"><b>Data dobandirii</b></label>
                    <br>
                    <table id="data_dobandire">
                        <tr>
                            <td>
                            <select name="zi_dobandire" required>
                                <option disabled selected value> Zi </option>
                                <?php for ($i = 1; $i <= 31; $i++){?>
                                <option value=<?php echo $i?>><?php echo $i?></option>
                                <?php }?>
                            </select>
                            </td>
                            <td>
                            <select name="luna_dobandire" required>
                            <option disabled selected value> Luna </option>
                                <?php for ($i = 1; $i <= 12; $i++){?>
                                <option value=<?php echo $i?>><?php echo $i?></option>
                                <?php }?>
                            </select>
                            </td>
                            <td>
                            <select name="an_dobandire" required>
                            <option disabled selected value> An </option>
                            <?php for ($i = 2022; $i >= 1950; $i--){?>
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
        <p class="form_heading">Proprietari</p>
        <div class="horizontal-line-2"></div>
        <div class="overflow">
        <?php $query = "SELECT PersoanaID, Nume, Prenume FROM persoane";?>
        <?php if($result = mysqli_query($link, $query)) {?>
        <div class="form_table" id="checkbox">
            <?php while($row = mysqli_fetch_array($result)) { ?>
            <div class="form_row">
                <div class="form_cell">
                    <label for="curent"><?php echo $row["Nume"] . " " . $row["Prenume"]; ?></label>
                </div>
                <div class="form_cell" >
                <input type="checkbox" name="id_list[]" value = <?php echo $row["PersoanaID"]; if ($id == $row["PersoanaID"]) echo " checked"?>>
                </div>
            </div>
            <?php }?>
        </div>
        </div>
        <?php }?>
        <button type="submit">Adaugare</button>
        </form>     
        <?php } ?>
    </div>
    </body>
</html>