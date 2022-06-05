<?php 
	session_start();
	if(!isset($_SESSION["zalogowany"]) || $_SESSION["zalogowany"] == false){
		$_SESSION["zalogowany"] = false;
		header("refresh: 0; url=logowanie.php");
  }else{
    $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
    if(!$link){
      echo "Błąd połączenia";
      header("refresh: 20; url=index.php");
    }
    $id = $_SESSION['id_u'];
    $query = "SELECT `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
    $result = mysqli_query($link, $query);
    $wynik = mysqli_fetch_row($result);
    if($wynik[0] != NULL){
      header("refresh: 0; url=prosba.php");
    }
    require_once("czyszczenie.php");
  }
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
                <?php
                  echo "
                    Witaj, ".$_SESSION["imie_i_nazwisko"].".<br>
                    Ostatnie logowanie:<br>".$_SESSION["data_ost_log"]."
                    <hr>
                    <form action='panelUzytkownika.php' method='post'><input type='submit' name='submitW' value='Twoje konto'></form>
                    <form action='process.php' method='post'><input type='submit' name='submitW' value='Wyloguj się'></form>";
                    $id = $_SESSION["id_u"];
                    $query = "SELECT `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
                    $result = mysqli_query($link, $query);
                    $wynik = mysqli_fetch_row($result);
                    if($wynik[0] == NULL){
						          echo "<form action='process.php' method='post'><input type='submit' name='submitUK'value='Usuń konto'></form>";
                    }else{
                      echo "<form action='process.php' method='post'><input class='usun-konto' type='submit' name='submitNUK'value='Anuluj usunięcie konta'></form>";
                    }
                ?>
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
      <div class="centered">Oczekiwanie na autoryzację...</div>
      <?php
        $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
        if(!$link){
          echo "Błąd połączenia";
          header("refresh: 2; url=autoryzacja.php");
        }else{
          $id = $_SESSION['id_u'];
          $query = "SELECT `autoryzacja` FROM `uzytkownicy` WHERE `id` LIKE $id";
          $result = mysqli_query($link, $query);
          $wynik = mysqli_fetch_row($result);
          if($wynik[0] == 'nie'){
              header("refresh: 60; url=autoryzacja.php");
          }else{
              header("refresh: 0; url=index.php");
          }
        }
      ?>
    </main>

  </body>
</html>
