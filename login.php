
<?php
// Initializam sesiunea
session_start();

// Verificam daca utilizatorul este deja logat, in caz afirmativ il redirectionam la pagina de start
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: home.php");
    exit;
}

// Realizam conectarea in cazul in care nu este conectata
require_once "config.php";

// Initializare variabile
$username = $password = "";
$login_err = "";

// Datele va fi procesata dupa ce formularul este trimis
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Pregatim un SELECT
    $sql = "SELECT id, username, pass FROM utilizatori WHERE username = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        // Adaugam variabilele la stmt
        mysqli_stmt_bind_param($stmt, "s", $param_username);

        // Setam parametrul
        $param_username = $username;

        // Incercam sa executam query-ul
        if(mysqli_stmt_execute($stmt)){
            //Stocam rezultatul
            mysqli_stmt_store_result($stmt);

            // Daca exista user-ul, verificam parola
            if(mysqli_stmt_num_rows($stmt) == 1){                    
                // Adugam parametrii returnati la stmt
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                if(mysqli_stmt_fetch($stmt)){
                    if(password_verify($password, $hashed_password)){
                        // Parola este corecta, incepm o noua sesiune
                        session_start();
                        
                        // Salvam datele in variabilele sesiunii
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;                            
                        
                        // Redirectionare la pagina de start
                        header("location: home.php?welcome");
                    } else{
                        // Parola nu este valida, afisam un mesaj de eroare
                        $login_err = "Datele de autentificare introduse sunt incorecte";
                    }
                }
            } else{
                // Numele de utilizator nu exista, afisam un mesaj de eroare
                $login_err = "Datele de autentificare introduse sunt incorecte.";
            }
        }

        // Inchidem statement-ul
        mysqli_stmt_close($stmt);
    }
    
    // Inchidem conexiunea
    mysqli_close($link);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Autentificare</title>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body class="login">
        <?php include "header.php" ?>
        <div class="center" id = "login_center">
            <h2>Autentificare utilizator</h2>
            <div class="horizontal-line"></div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form_table">
                    <div class = "form_row">
                        <div class = "form_cell">
                            <label for="user"><b>Nume de utilizator</b></label>
                            <br>
                            <input type="text" placeholder="" name="username" required>
                        </div>
                    </div>
                    <div class = "form_row">
                        <div class = "form_cell">
                            <label for="password"><b>Parola</b></label>
                            <br>
                            <input type="password" placeholder="" name="password" required>
                        </div>
                    </div>
                </div>
                <?php if ($login_err != "") {?>
                <div class = "div-table" id="error">
                    <div class = "div-row">
                        <div class="div-cell" id="icon">
                            <i class="fas fa-exclamation-triangle"></i> 
                        </div>
                        <div class="div-cell" id="message">
                        Datele de autentificare introduse sunt incorecte. <br>In cazul in care v-ati uitat parola, va rugam sa va adresati administratorului.
                        </div>               
                    </div>
                </div>
                <?php }?>
                <button type="submit">Login</button>
            </form> 
        </div>
    </body>
</html>