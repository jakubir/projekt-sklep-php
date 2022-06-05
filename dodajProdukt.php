<?php 
  session_start(); 
  if(!isset($_SESSION["zalogowany"])){
    $_SESSION["zalogowany"] = false;
  }
  $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");

  if(!$link){
      echo "Błąd połączenia";
      header("refresh: 2; url=index.php");
  }
  require_once("czyszczenie.php");
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>POL&ROLLS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="font-awesome.min.css">
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
                  if($_SESSION["zalogowany"] == false){
                    echo "
                      <form action='logowanie.php'><input type='submit' value='Zaloguj się'></form>
                      <hr>
                      <form action='rejestracja.php'><input type='submit' value='Załóż konto'></form>
                    ";
                  }else{
                    echo "
                      Witaj, ".$_SESSION["imie_i_nazwisko"]."
                      <hr>
                      <form action='process.php' method='post'><input type='submit' name='submitW'value='Wyloguj się'></form>
                    ";
                  }
                ?>
              </div>
            </li>
            <div class="kreska"></div>
            <li><img src="shopping-cart.png" alt="Koszyk">
              <div class="menu-plus menu-k">
                <?php
                  if(isset($_SESSION["admin"]) && $_SESSION["admin"] == true){
                    echo "<div>Nie można korzystać<br>z koszyka,<br>zalogowano jako administrator</div>";
                  }
                ?>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main>
      
    <div class="formularz">
        <div class="produkt">
            <form action="process.php" method="post" enctype="multipart/form-data" name="dodawanie">
                <label for="przeslanie-pliku" title="Plik" class="przeslanie-pliku">
                    <?php 
                    if(isset($_GET["submitE"])){
                      $id = $_GET["id"];
                      $query = "SELECT `nazwa`, `id`, `data_waznosci`, `kategoria_id`, `cena`, `marza`, `ilosc` FROM produkty WHERE `id` = '$id'";
                      $result = mysqli_query($link, $query);
                      $wynik = mysqli_fetch_row($result);
                      $kat_id = ltrim($wynik[3]);
                      $query2 = "SELECT * FROM `kategorie` WHERE `id` LIKE '$kat_id'";
                      $result2 = mysqli_query($link, $query2);
                      $wynik2 = mysqli_fetch_row($result2);
                      $wynik[3] = $wynik2[1];
                      echo "<img style='width: 60%; float: left;' src=img/".str_replace(" ", "", $wynik[0]).".png>";
                    }
                    ?>Wybierz zdjęcie
                </label>
                <input id="przeslanie-pliku" type="file" name="image">
                <input title="Nazwa" type="text" name="nazwa" <?php
                if(isset($_SESSION["nazwa"]) && $_SESSION["nazwa"] != "")
                  echo "value='".$_SESSION["nazwa"]."'";
                elseif(isset($_GET["submitE"]))
                  echo "value='".$wynik[0]."'";
                else
                  echo "placeholder='Podaj nazwę produktu'";
                ?>>
                <input title="Ilość dni przydatności" type="number" name="ilosc_dni" min="0" <?php
                if(isset($_SESSION["ilosc_dni"]) && $_SESSION["ilosc_dni"] != ""){
                  echo "value='".$_SESSION["ilosc_dni"]."'";
                }elseif(isset($_GET["submitE"])){
                  $ilosc_dni = strtotime($wynik[2]) - strtotime(date("Y-m-d", time()));
                  echo "value='".round($ilosc_dni / (60 * 60 * 24))."'";
                }else
                  echo "placeholder='Podaj ilość dni przydatności'";
                ?>>
                <input title="Ilość produktów" type="number" name="ilosc" min="0" <?php
                if(isset($_SESSION["ilosc"]) && $_SESSION["ilosc"] != "")
                  echo "value='".$_SESSION["ilosc"]."'";
                elseif(isset($_GET["submitE"])){
                  echo "value='".$wynik[6]."'";
                }else
                  echo "placeholder='Podaj ilość produktów'";
                ?>>
                <select name="kategoria" title="Kategoria" onchange="przejscie(event)">
                  <?php
                    $query2 = "SELECT * FROM `kategorie`";
			              $result2 = mysqli_query($link, $query2);
                    $tmp2 = (isset($wynik)) ? $wynik[3] : "shadiosad";
                    $tmp = (isset($_SESSION["kategoria"])) ? ltrim($_SESSION["kategoria"]) : $tmp2;
                    while($wynik2 = mysqli_fetch_row($result2)){
                      echo $tmp." - ".$wynik2[1];
                      if($wynik2[1] == $tmp)
                        echo "<option selected name='".$wynik2[1]."' value='".$wynik2[0]."'>".$wynik2[1]."</option>";
                      else
                        echo "<option name='".$wynik2[1]."' value='".$wynik2[0]."'>".$wynik2[1]."</option>";
                    }
                  ?>
                  <option value="nowa">Dodaj nową</option>
                </select>
                <input type="hidden" name="se" value="<?php if(isset($_GET["submitE"])) {echo $_GET["submitE"];} ?>">
                <div>
                    <input type='number' title="Cena" id='cena' onchange='cenka()' name='cena' min='0' step='0.01' 
                      <?php
                      if(isset($_SESSION["cena"]) && $_SESSION["cena"] != "")
                        echo "value='".$_SESSION["cena"]."'";
                      elseif(isset($_GET["submitE"]))
                        echo "value='".$wynik[4]."'";
                      else
                        echo "placeholder='Podaj cenę'";
                      ?>>
                    <?php
                    echo "<input type='range' title='Marza' id='suwak' onchange='suwaczek()' title='Podaj marżę' name='marza' min='0' max='100' step='0.1' ";
                    if(isset($_SESSION["marza"]) && $_SESSION["marza"] != "")
                      echo "value='".$_SESSION["marza"]."'>
                      <div id='suwak-wynik'>".$_SESSION["marza"]."%</div><input type='text' name='id' value='".$_GET["id"]."' style='display: none;'><input type='text' name='nazwaS' value='".$_SESSION["nazwaS"]."' style='display: none;'>";
                    elseif(isset($_GET["submitE"]))
                      echo "value='".$wynik[5]."'>
                      <div id='suwak-wynik'>".$wynik[5]."%</div><input type='text' name='id' value='".$wynik[1]."' style='display: none;'><input type='text' name='nazwaS' value='".$wynik[0]."' style='display: none;'>";
                    else
                      echo "value='5'>
                      <div id='suwak-wynik'>5%</div>";
                    unset($_SESSION["kategoria"]);
                    unset($_SESSION["nazwa"]);
                    unset($_SESSION["nazwaS"]);
                    unset($_SESSION["ilosc"]);
                    unset($_SESSION["ilosc_dni"]);
                    unset($_SESSION["cena"]);
                    unset($_SESSION["marza"]);
                    ?>
                </div>
                <div id="cena-kontener">
                  0 zł
                </div>
                <input type="submit" <?php
                  if(isset($_GET["submitE"]))
                    echo "name='submitE' value='Zapisz zmiany'";
                  else
                    echo "name='submitD' value='Dodaj produkt'";
                ?>>
            </form>
      </div>
    </div>
      
    </main>

    <script>

        function suwaczek(){
            let suwak = document.getElementById("suwak").value;
            let suwak_wynik = document.getElementById("suwak-wynik");
            suwak_wynik.innerHTML = suwak + "%";
            let cena = document.getElementById("cena").value;
            cena = cena.replace(/,/g, ".");
            let cena_kontener = document.getElementById("cena-kontener");
            let tmp = ((parseFloat(suwak) / 100) * parseFloat(cena.replace(',',''))) + parseFloat(cena.replace(',',''));
            if(isNaN(tmp) == false)
              cena_kontener.innerHTML = (Math.round(tmp * 100) / 100) + " zł";
            else
              cena_kontener.innerHTML = "0 zł";
        }
        function cenka(){
            let cena = document.getElementById("cena").value;
            cena = cena.replace(/,/g, ".");
            let suwak = document.getElementById("suwak").value;
            let cena_kontener = document.getElementById("cena-kontener");
            let tmp = ((parseFloat(suwak) / 100) * parseFloat(cena)) + parseFloat(cena);
            if(isNaN(tmp) == false)
              cena_kontener.innerHTML = (Math.round(tmp * 100) / 100) + " zł";
            else
              cena_kontener.innerHTML = "0 zł";
        }
        function przejscie(event) {
          if (event.target.value === "nowa") {
            document.forms["dodawanie"].action.value='dodajKategorie.php';
		        document.forms["dodawanie"].action='dodajKategorie.php';
            document.forms["dodawanie"].submit();
          }
        }
    </script>

  </body>
</html>