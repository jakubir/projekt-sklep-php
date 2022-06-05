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
          <a href="index.php">POL & ROLLS</a>
        </div>
      </div>
    </nav>

    <main>
      <div class="formularz">
        <form action="process.php" method="post">
            <h2>Załóż konto</h2>
            <input type="text" name="imie" placeholder='Podaj imię...'<?php 
            if(isset($_SESSION["imie_error"])){
              if($_SESSION["imie_error"] == 2){
                echo "class='error'";
                unset($_SESSION["imie_error"]);
              }else
                echo "value='".$_SESSION['imie_error']."'";
            }elseif(isset($_SESSION["imie"])){
              echo "value='".$_SESSION['imie']."'";
              unset($_SESSION['imie']);
            }
            ?> required>
            <b> <?php if(isset($_SESSION["imie_error"]) && $_SESSION["imie_error"] != 2){ echo "Wprowadź poprawne imię!";unset($_SESSION["haslo_error"]);}?></b>

            <input type="text" name="nazwisko" placeholder='Podaj nazwisko...'<?php 
              if(isset($_SESSION["nazwisko_error"])){
                if($_SESSION["nazwisko_error"] == 2){
                  echo "class='error'";
                  unset($_SESSION["imie_error"]);
                }else
                  echo "value='".$_SESSION['nazwisko_error']."'";
              }elseif(isset($_SESSION["nazwisko"])){
                echo "value='".$_SESSION['nazwisko']."'";
                unset($_SESSION['nazwisko']);
              }
            ?> required>
            <b><?php if(isset($_SESSION["nazwisko_error"]) && $_SESSION["nazwisko_error"] != 2){ echo "Wprowadź poprawne nazwisko!";unset($_SESSION["haslo_error"]);}?></b>

            <input type="email" name="email" placeholder='Podaj adres email...'<?php 
              if(isset($_SESSION["email_error"])){
                if($_SESSION["email_error"] == 2){
                  echo "class='error'";
                  unset($_SESSION["email_error"]);
                }else
                  echo "value='".$_SESSION['email_error']."'";
              }elseif(isset($_SESSION["email"])){
                echo "value='".$_SESSION['email']."'";
                unset($_SESSION['email']);
              }
            ?> required>
            <b><?php if(isset($_SESSION["email_error"]) && $_SESSION["email_error"] != 2){ echo "Wprowadź poprawny adres email!";unset($_SESSION["haslo_error"]);}?></b>

            <input type="text" name="haslo" <?php 
              if(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 2){
                unset($_SESSION["haslo_error"]);
                $haslo = str_shuffle(bin2hex(random_bytes(2)).random_int(0, 9).chr(random_int(33, 47)).chr(random_int(65, 90)).chr(random_int(97, 122)));
                echo "value='".$haslo."'";
              }elseif(isset($_SESSION["haslo"])){
                echo "value='".$_SESSION['haslo']."'";
                unset($_SESSION['haslo']);
              }else{
                $haslo = str_shuffle(bin2hex(random_bytes(2)).random_int(0, 9).chr(random_int(33, 47)).chr(random_int(65, 90)).chr(random_int(97, 122)));
                echo "value='".$haslo."'";
              }
            ?> required title="Hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne">
            <b><?php if(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] != 2){ echo "Twoje hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne.";unset($_SESSION["haslo_error"]);}?></b>

            <label <?php 
              if(isset($_SESSION["regulamin_error"]) && $_SESSION["regulamin_error"] == 1){
                echo "class='error'";
                unset($_SESSION["regulamin_error"]);
              } 
            ?> >
              <input type="checkbox" name="regulamin" required>
              <span></span><b>*</b>Akceptuję regulamin sklepu
            </label>
            <b></b>

            <label>
              <input type="checkbox" name="newsletter">
              <span></span>Chcę otrzymywać powiadomienia o ofertach
            </label>
            <b></b>

            <input type="submit" name="submitR" value="Załóż konto">
        </form>
        <div class="propozycja">Masz już konto? <a href="logowanie.php">Zaloguj się!</a></div>
      </div>
    </main>

  </body>
</html>
