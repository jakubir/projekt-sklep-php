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
        <div class="menu">
          <ul>
            <li><img src="user.png" alt="Konto">
              <div class="menu-plus menu-u">
                <form action="logowanie.php"><input type="submit" value="Zaloguj się"></form>
                <hr>
                <form action="rejestracja.php"><input type="submit" value="Załóż konto"></form>
              </div>
            </li>
            <div class="kreska"></div>
            <li><img src="shopping-cart.png" alt="Koszyk">
              <div class="menu-plus menu-k">
				  Koszyk niedostępny
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      
      <div class="centered c3">
      <?php
        if(isset($_SESSION["zmiana_hasla"])){
          echo "<h2>Nie pamiętasz hasła?</h2>
          <div>Podaj kod z wiadomości email oraz nowe hasło.</div>
          <span></span>
          <form action='process.php' method='post'>
            <input type='number' max='999999' step='1' name='kod'"; 
            if(isset($_SESSION["kod_error"]) && $_SESSION["kod_error"] == 2){
              echo " class='error submit-oh' placeholder='Podaj kod odzyskiwania...'";
              unset($_SESSION["kod_error"]);
            }elseif(isset($_SESSION["kod_error"]) && $_SESSION["kod_error"] == 1){
              echo " class='error submit-oh' placeholder='Błedny kod odzyskiwania...'";
              unset($_SESSION["kod_error"]);
            }else{
              echo " class='submit-oh' placeholder='Podaj kod odzyskiwania...'";
            }
            echo " required>";

            echo "<input class='submit-oh' type='text' name='haslo'";
            if(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 2){
              unset($_SESSION["haslo_error"]);
              $haslo = str_shuffle(bin2hex(random_bytes(2)).random_int(0, 9).chr(random_int(33, 47)).chr(random_int(65, 90)).chr(random_int(97, 122)));
              echo " value='".$haslo."'";
            }elseif(isset($_SESSION["haslo"]) && isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 1){
              echo " value='".$_SESSION['haslo']."'";
              unset($_SESSION['haslo']);
            }else{
              $haslo = str_shuffle(bin2hex(random_bytes(2)).random_int(0, 9).chr(random_int(33, 47)).chr(random_int(65, 90)).chr(random_int(97, 122)));
              echo "value='".$haslo."'";
            }
            echo " required title='Hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne'><b style='width: 60%;'>";
            if(isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 1){
              echo "Twoje hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne.";
              unset($_SESSION["haslo_error"]);
            }
            echo "</b>
            <input type='submit' class='submit-oh' name='submitZOH' value='Zmień hasło'>
          </form>";
        }else{
          echo "<h2>Nie pamiętasz hasła?</h2>
          <div>Jeśli na ten e-mail jest założone konto,<br> to otrzymasz na niego wiadomość z kodem.</div>
          <span></span>
          <form action='process.php' method='post'>
            <input type='email' name='email'";
            if(isset($_SESSION["email_error"]) && $_SESSION["email_error"] == 1){
              echo " class='error submit-oh' placeholder='Podaj adres email...'";
              unset($_SESSION["email_error"]);
            }elseif(isset($_SESSION["email_error"]) && $_SESSION["email_error"] == 2){
              echo " class='error submit-oh' placeholder='Brak konta o podanym adresie email'";
              unset($_SESSION["email_error"]);
            }else{
              echo " class='submit-oh' placeholder='Podaj adres email...'";
            }
            echo " required>
            <input type='submit' class='submit-oh' name='submitOH' value='Odzyskaj hasło'>
          </form>";
        }
      ?>
      </div>
      
    </main>

  </body>
</html>