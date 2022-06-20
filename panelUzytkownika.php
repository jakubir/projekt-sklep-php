<?php
session_start();
if (!isset($_SESSION["zalogowany"]) || $_SESSION["zalogowany"] == false) {
  $_SESSION["zalogowany"] = false;
  header("refresh: 0; url=logowanie.php");
} else {
  $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
  if (!$link) {
    echo "Błąd połączenia";
    header("refresh: 20; url=index.php");
  }
  $id = $_SESSION['id_u'];
  $query = "SELECT `autoryzacja`, `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
  $result = mysqli_query($link, $query);
  $wynik = mysqli_fetch_row($result);
  if ($wynik[0] == 'nie') {
    header("refresh: 0; url=autoryzacja.php");
  } elseif ($wynik[1] != NULL) {
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
                    Witaj, " . $_SESSION["imie_i_nazwisko"] . ".<br>";
                    if(isset($_SESSION["data_ost_log"]))
                      echo "Ostatnie logowanie:<br>".$_SESSION["data_ost_log"];
                    echo "<hr>
                    <form action='process.php' method='post'><input type='submit' name='submitW' value='Wyloguj się'></form>";
              $id = $_SESSION["id_u"];
              $query = "SELECT `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
              $result = mysqli_query($link, $query);
              $wynik = mysqli_fetch_row($result);
              if ($wynik[0] == NULL) {
                echo "<form action='process.php' method='post'><input type='submit' name='submitUK'value='Usuń konto'></form>";
              } else {
                echo "<form action='process.php' method='post'><input class='usun-konto' type='submit' name='submitNUK'value='Anuluj usunięcie konta'></form>";
              }
              ?>
            </div>
          </li>
          <div class="kreska"></div>
          <li><img src="shopping-cart.png" alt="Koszyk">
            <div class="menu-plus menu-k">
              <?php
              if (isset($_SESSION["admin"]) && $_SESSION["admin"] == true) {
                echo "<div>Nie można korzystać<br>z koszyka,<br>zalogowano jako administrator</div>";
              } else {
                echo "Do zapłaty:<br><strong>";
                if (isset($_COOKIE["idz"])) {
                  $id_z = $_COOKIE["idz"];
                  $query = "SELECT SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) 'Suma' FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` = '$id' AND z.`id` = '$id_z' GROUP BY z.`id`";
                  $result = mysqli_query($link, $query);
                  if (mysqli_num_rows($result) > 0) {
                    $wynik = mysqli_fetch_row($result);
                    echo $wynik[0] . " zł";
                  } else
                    echo "0 zł";
                } else
                  echo "0 zł";
                echo "</strong><hr>
                    <form action='koszyk.php' method='post'><input type='submit' name='submit' value='Przejdź do koszyka'></form>";
              }
              ?>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main>

    <div class="formularz" style="grid-row: 1/1;">
      <form action="process.php" method="post">
        <h2>Zmiana ustawień konta</h2>
        <input style="margin-left: 15px;" type="text" name="haslo" placeholder="Podaj nowe hasło..." title="Hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne"
        <?php
        if(isset($_SESSION["haslo"])){
          echo "value='".$_SESSION["haslo"]."'";
          unset($_SESSION["haslo"]);
        }
        ?>>
        <input style="margin-left: 15px;" type="password" name="hasloS" placeholder="Podaj stare hasło..." title="Hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne">
        <b><?php
            if (isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] == 2) {
              echo "Podane stare hasło jest niepoprawne.";
              unset($_SESSION["haslo_error"]);
            } elseif (isset($_SESSION["haslo_error"]) && $_SESSION["haslo_error"] != 2) {
              echo "Twoje hasło powinno zawierać conajmniej 8 znaków, w tym małe i wielkie litery, cyfry i znaki specjalne.";
              unset($_SESSION["haslo_error"]);
            }
            ?></b>
        <label style="margin-bottom: 20px;">
          <input type="checkbox" name="newsletter" <?php
            $query = "SELECT `newsletter` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
            $result = mysqli_query($link, $query);
            $wynik = mysqli_fetch_row($result);
            if ($wynik[0] == 'tak') {
              echo "checked";
            }
            ?>>
          <span></span>Chcę otrzymywać powiadomienia o ofertach
        </label>
        <b></b>
        <input type="submit" name="submitEK" value="Zaakceptuj zmiany">
      </form>
    </div>

    <div class="centered" style="margin: 0;">
      <h2>Historia zakupów</h2>
      <?php
      $query = "SELECT SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)), SUM(zp.`ilosc`), z.`data`, z.`stan`, z.`id` FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` LIKE '$id' AND z.`stan` NOT LIKE 'koszyk' GROUP BY z.`id` ORDER BY z.`id` DESC";
      $result = mysqli_query($link, $query);
      while ($wynik = mysqli_fetch_row($result)) {
        echo "<div>
              <div>Zamówienie z dnia <strong>" . substr($wynik[2], 0, 10) . "</strong> o numerze <strong>" . sprintf("%05d", $wynik[4]) . "</strong> ";
        if ($wynik[3] == "oczekuje")
          echo "oczekuje na odbiór.</div><div>Składa";
        elseif($wynik[3] == "do realizacji")
          echo "jest w trakcie kompletowania, <br> prosimy o cierpliwość.</div><div>Składa";
        else
          echo "zostało odebrane.</div><div>Składało";
        echo " się z " . $wynik[1] . " produktów, o łącznej wartości " . $wynik[0] . "zł.</div>
            </div><hr>";
      }
      ?>
    </div>
  </main>

</body>

</html>