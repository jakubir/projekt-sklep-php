<?php
	session_start();
  $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
  if(!$link){
    echo "Błąd połączenia";
    header("refresh: 20; url=index.php");
  }
  require_once("czyszczenie.php");
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>POL&ROLLS</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>

    <nav>
      <div class="container">
        <div class="logo">
          <a>POL & ROLLS</a>
        </div>
      </div>
    </nav>

    <main>
      <div class="formularz">
        <form action="process.php" method="post">
            <h2>Zaloguj się</h2>
            <b></b>

            <input type="email" name="email" placeholder='Podaj adres email...' <?php 
            if(isset($_SESSION["email_error"]) && $_SESSION["email_error"] == 2){
              echo "class='error'";
              unset($_SESSION["email_error"]);
            }elseif(isset($_SESSION["email_error"]) && $_SESSION["email_error"] == 1){
              echo "value='".$_SESSION["email_error"]."'";
              unset($_SESSION["email_error"]);
            }elseif(isset($_SESSION["email"])){
              echo "value='".$_SESSION["email"]."'";
              unset($_SESSION["email"]);
            }
            ?> required>
            <b></b>

            <input type="password" name="haslo" placeholder="Podaj haslo..." <?php 
            if(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 2){
              echo "class='error'";
              unset($_SESSION["haslo_error"]);
            }
            ?> required>
            <div class="propozycja" style="margin: 0; font-size: 1em" ><a href="odzyskanieHasla.php">Nie pamiętasz hasła?</a></div>
            <b><?php
              if(isset($_SESSION["email_error"]) && $_SESSION["email_error"] === 0){
                echo "Konto dla tego adresu email już istnieje, zaloguj się na nie powyżej";
                unset($_SESSION["email_error"]);
              }elseif(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 1 || isset($_SESSION["email_error"]) && $_SESSION["email_error"] != 2){
                echo "Adres email lub hasło NIE są poprawne";
                unset($_SESSION["haslo_error"]);
                unset($_SESSION["email_error"]);
                unset($_SESSION["email"]);
              }
            ?></b>

            <input type="submit" name="submitL" value="Zaloguj się">
        </form>
        <div class="propozycja">Nie masz konta? <a href="rejestracja.php">Zarejestruj się!</a></div>
      </div>
    </main>

  </body>
</html>
