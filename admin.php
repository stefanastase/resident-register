<?php
// Initializare sesiune
session_start();

// Verificam daca utilizatorul este conectat, daca acesta nu este logat va fi redirectionat catre pagina de login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Verificam daca utilizatorul este administratorul, utilizatorii ceilalti nu pot vedea pagina admin.php
if($_SESSION["username"] !== "admin"){
    header("location: welcome.php");
    exit;
}
// Realizam conectarea la baza de date in cazul in care nu este facuta deja
require_once "config.php";

// Selectam utilizatorii
$query = "SELECT id, username FROM utilizatori WHERE isAdmin = 0";
$result = mysqli_query($link, $query);

$action = 0;

// Realizam query-urile pentru fiecare actiune 
if(isset($_GET["action"])){
	// Actiunea este adaugare
	if($_GET["action"] == "add"){
		$action = 1;
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			$username = $_POST["username"];
			$password = $_POST["password"];
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			$query = "INSERT INTO utilizatori(username, pass) VALUES('$username', '$hashed_password')";
			if($result = mysqli_query($link, $query)){
				header("location: admin.php");
				exit;
			}
		}
	}
	// Actiunea este modificare
	if($_GET["action"] == "modify"){
		$action = 2;
		$id = $_GET["id"];
		$query = "SELECT username FROM utilizatori WHERE id = $id";
		if($result = mysqli_query($link, $query)){
			$row = mysqli_fetch_array($result);
			if($_SERVER["REQUEST_METHOD"] == "POST"){
				$password = $_POST["password"];
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
				$query = "UPDATE utilizatori SET pass = '$hashed_password' WHERE id = $id";
				if($result = mysqli_query($link, $query)){
					header("location: admin.php");
					exit;
				}
			}
		}
	}
	// Actiunea este stergere
	if($_GET["action"] == "delete"){
		$id = $_GET["id"];
		$query = "DELETE FROM utilizatori WHERE id = $id";
		if($result = mysqli_query($link, $query)){
			header("location: admin.php");
			exit;
		}
	}
}
mysqli_close($link);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Administrare utilizatori</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body class="admin"> 
		<?php include "header.php" ?>
		<div class = "center">
		<!--- Actiunea este vizualizare, afisam utilizatorii --->
			<?php if($action == 0) {?>
				<h2> Utilizatori </h2>
				<div class="horizontal-line"></div>
				<table>
					<tr>
						<th>Nume utilizator</th>
						<th>Actiuni
					</tr>
					<?php if ($result){
						while($row = mysqli_fetch_array($result)){ ?>
						<tr>
							<td><?php echo $row['username']; ?></td>
							<td>
								<a href = <?php echo "admin.php?action=modify&id=" . $row['id'] ?>><i class="fas fa-edit"></i></a>
								<a href = <?php echo "admin.php?action=delete&id=" . $row['id'] ?>><i class="fas fa-trash"></i></a>
							</td>
						</tr>
						<?php }
						}?>
				</table>
				<div class="center_footer">
				<table>
					<tr>
						<th><a href="admin.php?action=add"><i class="fas fa-plus"></i> Adauga</a></th> 
					</tr>
				</table>
					</div>				
			<?php }
			else if($action == 1){?>
			<!--- Actiunea este adugare, afisam formularul de inserare --->
				<h2><a href = "admin.php?action=view"><i class="fas fa-arrow-left"></i></a> Adaugare utilizator</h2>
				<div class="horizontal-line"></div>
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=add"); ?>" method="post">
					<div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
								<label for="username"><b>Username</b></label>
								<br>
								<input type="text" placeholder="" name="username" required>
							</div>
                            <div class = "form_cell">
								<label for="password"><b>Parola</b></label>
								<br>
								<input type="password" placeholder="" name="password" required>
							</div>
						</div>
					</div>
					<button type="submit">Adaugare</button>
            	</form>   
			<?php }
			else if($action == 2){?>
			<!--- Actiunea este modificare, afisam formularul pentru modificare parola --->
				<h2><a href = "admin.php?action=view"><i class="fas fa-arrow-left"></i></a> Modificare parola</h2>
				<h3><?php echo $row["username"]; ?></h3>
				<div class="horizontal-line"></div>
				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?action=modify&id=" . $id); ?>" method="post">
					<div class="form_table">
                        <div class = "form_row">
                            <div class = "form_cell">
								<label for="password"><b>Parola</b></label>
								<br>
								<input type="password" placeholder="" name="password" required>
							</div>
						</div>
					</div>
					<button type="submit">Schimbare parola</button>
            	</form>
			<?php } ?>  
		</div>
    </body>
</html>