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
    $query = "SELECT `autoryzacja`, `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
    $result = mysqli_query($link, $query);
    $wynik = mysqli_fetch_row($result);
    if($wynik[0] == 'nie'){
      header("refresh: 0; url=autoryzacja.php");
    }elseif($wynik[1] != NULL){
      header("refresh: 0; url=prosba.php");
    }
    if(isset($_COOKIE["idz"])){
      $id_z = $_COOKIE["idz"];
      $czas = time()+60*15;
      setcookie("idz", $id_z, $czas, "/");
      $czas2 = date("Y-m-d H:i:s", $czas);
      $query = "UPDATE `zamowienia` SET `data_przedawnienia` ='$czas2' WHERE `id` = '$id_z'";
      $result = mysqli_query($link, $query);
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
                    Witaj, ".$_SESSION["imie_i_nazwisko"]."<br>
                    Ostatnie logowanie:<br>".$_SESSION["data_ost_log"]."
                    <hr>";
                    if(!isset($_SESSION["admin"]) OR $_SESSION["admin"] == false){
                      echo "<form action='panelUzytkownika.php' method='post'><input type='submit' name='submit' value='Twoje konto'></form>";
                    }
                    echo "<form action='process.php' method='post'><input type='submit' name='submitW' value='Wyloguj się'></form>";
                    $id = $_SESSION["id_u"];
                    $query = "SELECT `prosba` FROM `uzytkownicy` WHERE `id` LIKE '$id'";
                    $result = mysqli_query($link, $query);
                    $wynik = mysqli_fetch_row($result);
                    if(!isset($_SESSION["admin"]) OR $_SESSION["admin"] == false){
                      if($wynik[0] == NULL){
                        echo "<form action='process.php' method='post'><input type='submit' name='submitUK'value='Usuń konto'></form>";
                      }else{
                        echo "<form action='process.php' method='post'><input class='usun-konto' type='submit' name='submitNUK'value='Anuluj usunięcie konta'></form>";
                      }
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
                  }else{
                    echo "Do zapłaty:<br><strong>";
                    if(isset($_COOKIE["idz"])){
                      $query = "SELECT SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) 'Suma' FROM zamowienia z JOIN zamowione_produkty zp ON z.`id` = zp.`id_zamowienia` JOIN produkty p ON zp.`id_produktu` = p.`id` JOIN uzytkownicy u ON z.`id_uzytkownika` = u.`id` WHERE u.`id` = '$id' AND z.`id` = '$id_z' AND z.`stan` = 'koszyk' GROUP BY z.`id`";
                      $result = mysqli_query($link, $query);
                      if(mysqli_num_rows($result) > 0){
                        $wynik = mysqli_fetch_row($result);
                        echo $wynik[0]." zł";
                      }else 
                        echo "0 zł";
                    }else 
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
      <div class="filtry-container">
        <div class="filtry">
          <form action="index.php" method="get" name="filtry">
						<?php if(isset($_SESSION["owner"]) && $_SESSION["owner"] == false) { ?>
							<h3>Filtry</h3>
            <?php } else { ?>
              <h3>Menu</h3>
                <h4>Menu</h4>
                <label style="margin-bottom: 10px;">
                  <select name="dzial" onchange="document.forms['filtry'].submit()">
										<option value="inwentaryzacja" <?php if(isset($_GET["dzial"]) && $_GET["dzial"] == "inwentaryzacja") echo "selected"; ?>>Inwentaryzacja</option>
										<option value="zamowienia" <?php if(isset($_GET["dzial"]) && $_GET["dzial"] == "zamowienia") echo "selected"; ?>>Zamówienia</option>
										<option value="klienci" <?php if(isset($_GET["dzial"]) && $_GET["dzial"] == "klienci") echo "selected"; ?>>Klienci</option>
										<option value="podsumowanie" <?php if(isset($_GET["dzial"]) && $_GET["dzial"] == "podsumowanie") echo "selected"; ?>>Podsumowanie zysków</option>
									</select>
								</label>
								
							<?php } if(isset($_SESSION["owner"]) && $_SESSION["owner"] == false || isset($_SESSION["owner"]) && $_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "inwentaryzacja") { ?>
								<h4>Kategorie</h4>
								<?php
								$query = "SELECT * FROM `kategorie`";
								$result = mysqli_query($link, $query);
								while($wynik = mysqli_fetch_row($result)){
									echo "<label><input type='checkbox' name='kategorie[]'";
									if(isset($_GET["kategorie"])){
										foreach ($_GET["kategorie"] as $kategoria) {
											if($kategoria == $wynik[0]){
											echo "checked ";
											}
										}
									}
									echo "value='$wynik[0]'> $wynik[1]</label>";
								}
								?>
								<h4>Cena</h4>
								<label><input type="number" placeholder="od" <?php if(!empty($_GET["cena-od"])) echo "value='".$_GET["cena-od"]."'"; ?> name="cena-od"> zł  —  
								<input type="number" placeholder="do" <?php if(!empty($_GET["cena-do"])) echo "value='".$_GET["cena-do"]."'"; ?> name="cena-do"> zł</label>
                <h4>Data</h4>
								<label><input type="date" placeholder="od" <?php if(!empty($_GET["data-od"])) echo "value='".$_GET["data-od"]."'"; ?> name="data-od">  —  
								<input type="date" placeholder="do" <?php if(!empty($_GET["data-do"])) echo "value='".$_GET["data-do"]."'"; ?> name="data-do"></label>
								<h4>Sortowanie</h4>
								<label>
                  <select name="sort">
										<option value="cenaros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenaros") echo "selected"; ?>>Cena rosnąco</option>
										<option value="cenamal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenamal") echo "selected"; ?>>Cena malejąco</option>
										<option value="terminros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminros") echo "selected"; ?>>Termin przyd. rosnąco</option>
										<option value="terminmal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminmal") echo "selected"; ?>>Termin przyd. malejąco</option>
									</select>
								</label>

							<?php } elseif($_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "zamowienia") { ?>
								<h4>Stan</h4>
								<?php
									$query = "SELECT DISTINCT stan FROM `zamowienia`";
									$result = mysqli_query($link, $query);
									while($wynik = mysqli_fetch_row($result)){ ?>
										<label><input type='checkbox' name='zamowienia[]'
										<?php
											if(isset($_GET["zamowienia"])){
												foreach ($_GET["zamowienia"] as $kategoria) {
													if($kategoria == $wynik[0]){
													echo "checked ";
													}
												}
											}
											echo "value='$wynik[0]'"; ?>
										><?php $tmp = ($wynik[0] == "dostarczono") ? "odebrane" : $wynik[0]; echo " ".ucfirst($tmp); ?></label>
									<?php	}?>
								<h4>Data</h4>
								<label><input type="date" placeholder="od" <?php if(!empty($_GET["data-od"])) echo "value='".$_GET["data-od"]."'"; ?> name="data-od">  —  
								<input type="date" placeholder="do" <?php if(!empty($_GET["data-do"])) echo "value='".$_GET["data-do"]."'"; ?> name="data-do"></label>
								<h4>Sortowanie</h4>
								<label>
                  <select name="sort">
										<option value="numer" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "numer") echo "selected"; ?>>Numer zamówienia</option>
										<option value="cenaros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenaros") echo "selected"; ?>>Wartość rosnąco</option>
										<option value="cenamal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenamal") echo "selected"; ?>>Wartość malejąco</option>
										<option value="terminros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminros") echo "selected"; ?>>Data rosnąco</option>
										<option value="terminmal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminmal") echo "selected"; ?>>Data malejąco</option>
										<option value="klient" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "klient") echo "selected"; ?>>Dane klienta</option>
									</select>
								</label>
							<?php } elseif($_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "klienci") { ?>
								<h4>Data</h4>
								<label><input type="date" placeholder="od" <?php if(!empty($_GET["data-od"])) echo "value='".$_GET["data-od"]."'"; ?> name="data-od">  —  
								<input type="date" placeholder="do" <?php if(!empty($_GET["data-do"])) echo "value='".$_GET["data-do"]."'"; ?> name="data-do"></label>
								<h4>Wartość zamówień</h4>
								<label><input type="number" placeholder="od" <?php if(!empty($_GET["cena-od"])) echo "value='".$_GET["cena-od"]."'"; ?> name="cena-od"> zł  —  
								<input type="number" placeholder="do" <?php if(!empty($_GET["cena-do"])) echo "value='".$_GET["cena-do"]."'"; ?> name="cena-do"> zł</label>
								<h4>Sortowanie</h4>
								<label>
                  <select name="sort" style="font-size: 0.89em;">
										<option value="klient" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "klient") echo "selected"; ?>>Dane klienta</option>
										<option value="terminros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminros") echo "selected"; ?>>Data logowania rosnąco</option>
										<option value="terminmal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "terminmal") echo "selected"; ?>>Data logowania malejąco</option>
										<option value="iloscros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "iloscros") echo "selected"; ?>>Ilosć zamówień rosnąco</option>
										<option value="iloscmal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "iloscmal") echo "selected"; ?>>Ilosć zamówień malejąco</option>
										<option value="cenaros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenaros") echo "selected"; ?>>Wartość zamówień rosnąco</option>
										<option value="cenamal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "cenamal") echo "selected"; ?>>Wartość zamówień malejąco</option>
										<option value="srcenaros" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "srcenaros") echo "selected"; ?>>Śr. wart. zamówień rosnąco</option>
										<option value="srcenamal" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "srcenamal") echo "selected"; ?>>Śr. wart. zamówień malejąco</option>
									</select>
								</label>
              <?php } elseif($_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "podsumowanie") { ?>
                <h4>Data</h4>
								<label><input type="date" placeholder="od" <?php if(!empty($_GET["data-od"])) echo "value='".$_GET["data-od"]."'"; ?> name="data-od">  —  
								<input type="date" placeholder="do" <?php if(!empty($_GET["data-do"])) echo "value='".$_GET["data-do"]."'"; ?> name="data-do"></label>
              <?php } ?>
              <input type="submit" value="Filtruj">
          </form>
        </div>
      </div>

      <?php
        if(isset($_SESSION["owner"]) && $_SESSION["owner"] == true && !isset($_GET["dzial"]))
          header("refresh: 0; url=index.php?dzial=inwentaryzacja");
        if(isset($_SESSION["owner"]) && $_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "inwentaryzacja" || isset($_SESSION["owner"]) && $_SESSION["owner"] == false){
          if(isset($_SESSION["admin"]) && $_SESSION["admin"] == true && isset($_SESSION["owner"]) && $_SESSION["owner"] == false){
            echo "
            <div class='produkt'>
              <a href='dodajProdukt.php' title='Dodaj produkt'><img src='plus.png' alt='plus'></a>
              <div class='title'>Dodaj produkt</div>
              <div class='data'></div>
              <div class='price'></div>
              <form style='visibility: hidden;'><input type='submit' value='' name='submit'></form>
            </div>
            ";
          }
          $link = @mysqli_connect("localhost", "root", "", "sklep_projekt");
          if(!$link){
            echo "Błąd połączenia";
            header("refresh: 20; url=index.php");
          }else{
            $warunek = "";
            if(isset($_GET["cena-od"]))
              $_GET["cena-od"] = filter_var($_GET["cena-od"], FILTER_SANITIZE_NUMBER_FLOAT);
            if(isset($_GET["cena-do"]))
              $_GET["cena-do"] = filter_var($_GET["cena-do"], FILTER_SANITIZE_NUMBER_FLOAT);
            
            if(!empty($_GET["cena-od"]) && !empty($_GET["cena-do"]))
              $warunek .= " AND p.`cena` BETWEEN ".strval($_GET["cena-od"])." AND ".strval($_GET["cena-do"]);
            elseif(!empty($_GET["cena-od"]) && empty($_GET["cena-do"]))
              $warunek .= " AND p.`cena` > ".strval($_GET["cena-od"]);
            elseif(empty($_GET["cena-od"]) && !empty($_GET["cena-do"]))
              $warunek .= " AND p.`cena` < ".strval($_GET["cena-do"]);

            if(!empty($_GET["data-od"]) && !empty($_GET["data-do"]))
              $warunek .= " AND p.`data_waznosci` BETWEEN \"".date("Y-m-d", strtotime($_GET["data-od"]))."\" AND \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";
            elseif(!empty($_GET["data-od"]) && empty($_GET["data-do"]))
              $warunek .= " AND p.`data_waznosci` > \"".strval(date("Y-m-d", strtotime($_GET["data-od"])))."\"";
            elseif(empty($_GET["data-od"]) && !empty($_GET["data-do"]))
              $warunek .= " AND p.`data_waznosci` < \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";

            if(isset($_GET["kategorie"])){
              $warunek .= " AND p.`kategoria_id` IN (";
              $i = 0;
              foreach ($_GET["kategorie"] as $kategoria){
                if($i == 0){
                  $warunek .= "$kategoria";
                  $i++;
                }else
                $warunek .= ", $kategoria";
              }
              $warunek .= ")";
            }

						if(isset($_GET["sort"]) && empty($_GET["sort"]) == false) {
							$warunek .= " ORDER BY ";
							switch ($_GET["sort"]) {
								case 'cenaros':
									$warunek .= "p.`cena` ASC";
									break;
								case 'cenamal':
									$warunek .= "p.`cena` DESC";										
									break;
								case 'terminros':
									$warunek .= "p.`data_waznosci` ASC";										
									break;
								case 'terminmal':
									$warunek .= "p.`data_waznosci` DESC";										
									break;
								default:
									$warunek .= "p.`id`";
							}
						}
            
            $query = "SELECT p.`nazwa`, k.`nazwa`, p.`data_waznosci`, p.`cena`, p.`marza`, p.`id`, p.`ilosc` FROM `produkty` p JOIN `kategorie` k ON p.`kategoria_id` = k.`id` WHERE p.`stan` = 'w_sprzedaży'".$warunek;
            $result = mysqli_query($link, $query);
						$licznik = 0;
            if(mysqli_num_rows($result) > 0){
              while($wynik = mysqli_fetch_row($result)){
                if(!isset($_SESSION["admin"]) || $_SESSION["admin"] == false && strtotime($wynik[2]) + 60*60*12 < time())
                  continue;
								$licznik ++;
                echo "<div class='produkt'>
                  <img src='img/".str_replace(" ", "", $wynik[0]).".png'>
                  <div class='title'>".$wynik[0]." -> <i>".$wynik[1]."</i></div>
                  <div class='data' title='Data przydatności'>".$wynik[2]."</div>";
                if(isset($_SESSION["owner"]) && $_SESSION["owner"] == true)
                  echo " <div class='price'>Koszt: ".str_replace('.', ',', $wynik[3])." zł + Marża: ". $wynik[4]."%</div>";
                echo " <div class='price'>Cena: ".str_replace('.', ',', round((($wynik[3] * ($wynik[4] / 100)) + $wynik[3]), 2))." zł</div>";
                  if(isset($_SESSION["admin"]) && $_SESSION["admin"] == true && isset($_SESSION["owner"]) && $_SESSION["owner"] == false){
                    echo "<div class='przycisk-produkt'>
                      <form action='dodajProdukt.php' method='GET'><input type='text' name='id' value='".$wynik[5]."' style='display: none;'><input type='submit' value='Edytuj' name='submitE'></form>
                      <form action='process.php' method='post'><input type='text' name='id' value='".$wynik[5]."' style='display: none;'><input type='submit' value='Usuń' name='submitU'></form>
                    </div>";
                  }elseif(isset($_SESSION["owner"]) && $_SESSION["owner"] == true){
                    $id_p = $wynik[5];
                    $query = "SELECT SUM(`ilosc`) FROM `zamowione_produkty` WHERE `id_zamowienia` IN (SELECT `id` FROM `zamowienia` WHERE `stan` IN ('koszyk', 'do realizacji', 'oczekuje')) AND `id_produktu` LIKE '$id_p' GROUP BY `id_produktu`;";
                    $result2 = mysqli_query($link, $query);
										$suma = $wynik[6];
										if(mysqli_num_rows($result2) > 0)
											$suma += mysqli_fetch_row($result2)[0];
                    echo "<div class='ilosc'>Dostępne: <strong>".$suma."</strong></div>";
                  }else{
                    echo "<div class='przycisk-produkt'>";
                    if($wynik[6] == 0){
                        echo "<div class='ilosc'><b>Niedostępny</b></div>";
                    }else{
                      echo "<div class='ilosc'>Dostępne: <strong>".$wynik[6]."</strong></div>
                      <form action='process.php' method='post'>
                        <select name='ilosc' title='Wybierz ilość sztuk'>";
                          if($wynik[6] > 10)
                            $k = 10;
                          else 
                            $k = $wynik[6];
                          for ($i=1; $i <= $k; $i++) { 
                            echo "<option value='$i'>$i</option>";
                          }
                        echo "</select>
                        <input type='text' name='id' value='".$wynik[5]."' style='display: none;'>
                        <input type='submit' value='Dodaj do koszyka' name='submitK'>
                      </form>";
                    }
                    echo "<b";
                    if(isset($_SESSION["ilosc_error"]) AND $wynik[5] == $_SESSION["ilosc_error"]){ 
                      echo " class='ilosc-error'>Brak wystarczającej ilości produktów";
                      unset($_SESSION["ilosc_error"]);
                      header("refresh: 5; url=index.php");
                    }else{
                      echo ">";
                    }
                    echo "</b>";
                    echo "</div>";
                  }
                echo "</div>";
              }
            }
						if($licznik == 0){
              echo "<div class='centered c2'><h3>Brak produktów dla podanych filtrów</h3></div>";
            }
          }
        } elseif(isset($_SESSION["owner"]) && $_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "zamowienia") {
      ?>
				<div class="table-container">
          <h2 style="width: 100%; text-align: center">Zamówienia</h2>
					<table>
						<thead>
							<tr>
								<th>Numer zamówienia</th>
								<th>Imię i nazwisko klienta</th>
								<th>Data zamówienia</th>
								<th>Stan zamówienia</th>
								<th>Wartość</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$warunek = "";
								
								if(!empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND z.`data` BETWEEN \"".date("Y-m-d", strtotime($_GET["data-od"]))."\" AND \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";
								elseif(!empty($_GET["data-od"]) && empty($_GET["data-do"]))
									$warunek = " AND z.`data` > \"".strval(date("Y-m-d", strtotime($_GET["data-od"])))."\"";
								elseif(empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND z.`data` < \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";

								if(isset($_GET["zamowienia"])){
									$warunek .= " AND z.`stan` IN (";
									$i = 0;
									foreach ($_GET["zamowienia"] as $zamowienia){
										if($i == 0){
											$warunek .= "\"$zamowienia\"";
											$i++;
										}else
										$warunek .= ", \"$zamowienia\"";
									}
									$warunek .= ")";
								}

								$sort = "";
								if(isset($_GET["sort"])) {
									$sort .= " ORDER BY ";
									switch ($_GET["sort"]) {
										case 'cenaros':
											$sort .= "wartosc ASC";
											break;
										case 'cenamal':
											$sort .= "wartosc DESC";										
											break;
										case 'terminros':
											$sort .= "z.`data` ASC";										
											break;
										case 'terminmal':
											$sort .= "z.`data` DESC";										
											break;
										case 'klient':
											$sort .= "u.`email`";
											break;
										case 'numer':
											$sort .= "z.`id`";										
											break;
										default:
											$warunek .= "z.`id`";
									}
								}

								$query = "SELECT z.`id`, CONCAT(u.`imie`, ' ', u.`nazwisko`), u.`email`, z.`data`, z.`stan`, SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) wartosc FROM `zamowienia` z JOIN `uzytkownicy` u ON z.`id_uzytkownika` = u.`id` JOIN `zamowione_produkty` zp ON zp.`id_zamowienia` = z.`id` JOIN `produkty` p ON p.`id` = zp.`id_produktu` WHERE z.`stan` NOT LIKE 'koszyk'".$warunek." GROUP BY z.`id`".$sort.";";
								$result = mysqli_query($link, $query);
								while($wynik = mysqli_fetch_row($result)) {
							?>
							<tr>
								<td><?php echo "Nr ".sprintf("%05d", $wynik[0]); ?></td>
								<td><?php echo $wynik[1]; ?></td>
								<td><?php echo $wynik[3]; ?></td>
								<td>
                  <form action="process.php" method="post" name="stany">
                    <select name="stan">
                      <?php $tmp = ($wynik[4] == "dostarczono") ? "odebrane" : $wynik[4]; echo $tmp;?>
                      <option value="do realizacji" <?php if(trim($tmp) == "do realizacji") {echo "selected";}?>>Do realizacji</option>
                      <option value="oczekuje" title="oczekuje na odbiór" <?php if(trim($tmp) == "oczekuje") {echo "selected";}?>>Oczekuje</option>
                      <option value="dostarczono" <?php if(trim($tmp) == "odebrane") {echo "selected";}?>>Odebrano</option>
                    </select>
                    <input style="display: none;" type="number" name="id" value="<?php echo $wynik[0]; ?>">
                    <input type="submit" name="submitOSC" value="✔" title="Kliknij, aby zatwierdzić zmiany">
                  </form>
                </td>
								<td><?php echo $wynik[5]." zł"; ?></td>
							</tr>
							<?php
								$id_z = $wynik[0];
								$query = "SELECT p.`nazwa`, zp.`ilosc` FROM `zamowione_produkty` zp JOIN `produkty` p ON p.`id` = zp.`id_produktu` WHERE zp.`id_zamowienia` = '$id_z'";
								$result2 = mysqli_query($link, $query);
								$ilosc_wierszy = mysqli_num_rows($result2);
								$i = 1;
								while($wynik2 = mysqli_fetch_row($result2)) {
							?>
							<tr <?php if($i == $ilosc_wierszy) {echo "style='border-bottom: 4px solid #1e3030;'";} ?>>
								<?php if($i == 1) { ?>
									<td style="font-weight: 600;" colspan="2" rowspan="<?php echo $ilosc_wierszy; ?>">Zamówione produkty:</td>
								<?php } ?>
								<td colspan="2"><?php echo $wynik2[0]; ?></td>
								<td colspan="2"><?php echo $wynik2[1]; $i++; ?></td>
							</tr>
							<?php }} ?>
						</tbody>
					</table>
				</div>
      <?php } elseif(isset($_SESSION["owner"]) && $_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "klienci") { ?>
				<div class="table-container">
          <h2 style="width: 100%; text-align: center">Klienci</h2>
					<table>
						<thead>
							<tr>
								<th>Imię i nazwisko</th>
								<th>Adres email</th>
								<th>Data ostatniego logowania</th>
								<th>Data ostatniego zakupu</th>
								<th>Ilość zamówień</th>
								<th>Łączna wartość zamówień</th>
								<th>Średnia wartość zamówień</th>
								<th>Częstotliwość zamówień</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$warunek = "";
                $warunek2 = "";
								
								if(!empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND u.`data_ost_log` BETWEEN \"".strval($_GET["data-od"])."\" AND \"".strval($_GET["data-do"])."\"";
								elseif(!empty($_GET["data-od"]) && empty($_GET["data-do"]))
									$warunek = " AND u.`data_ost_log` > \"".strval($_GET["data-od"])."\"";
								elseif(empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND u.`data_ost_log` < \"".strval($_GET["data-do"])."\"";
                  
                if(!empty($_GET["cena-od"]) && !empty($_GET["cena-do"]))
                  $warunek2 = "HAVING wartosc BETWEEN ".strval($_GET["cena-od"])." AND ".strval($_GET["cena-do"]);
                elseif(!empty($_GET["cena-od"]) && empty($_GET["cena-do"]))
                  $warunek2 = "HAVING wartosc > ".strval($_GET["cena-od"]);
                elseif(empty($_GET["cena-od"]) && !empty($_GET["cena-do"]))
                  $warunek2 = "HAVING wartosc < ".strval($_GET["cena-do"]);

								$sort = "";
								if(isset($_GET["sort"])) {
									$sort .= " ORDER BY ";
									switch ($_GET["sort"]) {
										case 'cenaros':
											$sort .= "wartosc ASC";
											break;
										case 'cenamal':
											$sort .= "wartosc DESC";										
											break;
                    case 'srcenaros':
                      $sort .= "srednia ASC";
                      break;
                    case 'srcenamal':
                      $sort .= "srednia DESC";										
                      break;
										case 'terminros':
											$sort .= "u.`data_ost_log` ASC";										
											break;
										case 'terminmal':
											$sort .= "u.`data_ost_log` DESC";										
											break;
										case 'klient':
											$sort .= "u.`email`";
											break;
										case 'iloscros':
											$sort .= "ilosc ASC";										
											break;
										case 'iloscmal':
											$sort .= "ilosc DESC";										
											break;
										default:
											$sort .= "u.`id`";
									}
								}

								$query = "SELECT CONCAT(u.`imie`, ' ', u.`nazwisko`), u.`email`, u.`data_ost_log`, (SELECT COUNT(*) FROM zamowienia z WHERE z.`id_uzytkownika` = u.`id`) ilosc, SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) wartosc, u.`id`, ROUND(SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) / (SELECT COUNT(*) FROM zamowienia z WHERE z.`id_uzytkownika` = u.`id`), 2) srednia FROM `uzytkownicy` u JOIN `zamowienia` z ON z.`id_uzytkownika` = u.`id` JOIN `zamowione_produkty` zp ON zp.`id_zamowienia` = z.`id` JOIN `produkty` p ON p.`id` = zp.`id_produktu` WHERE u.`rola` LIKE 'user' ".$warunek." GROUP BY u.`id` ".$warunek2." ".$sort.";";
								$result = mysqli_query($link, $query);
								while($wynik = mysqli_fetch_row($result)) {
									$daty = [];
									$id_u = $wynik[5];
									$query = "SELECT `data` FROM `zamowienia` WHERE `id_uzytkownika` LIKE '$id_u' ORDER BY `data` DESC";
									$result2 = mysqli_query($link, $query);
									$l = 0;
									while($wynik2 = mysqli_fetch_row($result2)) {
                    if($l == 0)
                      $data_ost_z = $wynik2[0];
										$daty[$l] = $wynik2[0]; 
										$l++;
									}
									$dni = [];
									for($i = 0; $i < count($daty) - 1; $i++) {
										$dni[$i] = (strtotime($daty[$i]) - strtotime($daty[$i+1]))/86400;
									}
									$suma = 0;
									foreach($dni as $value)
										$suma += $value;
									if(count($dni) != 0)
										$czest = round($suma /= count($dni));
									else
										$czest = 0;
							?>
							<tr>
								<td><?php echo $wynik[0]; ?></td>
								<td><?php echo $wynik[1]; ?></td>
								<td><?php echo $wynik[2]; ?></td>
								<td><?php echo date("Y-m-d", strtotime($data_ost_z)); ?></td>
								<td><?php echo $wynik[3]; ?></td>
								<td><?php echo $wynik[4]." zł"; ?></td>
								<td><?php echo $wynik[6]." zł"; ?></td>
								<td><?php $czest = ($czest == 0) ? "-" : $czest." dni"; echo $czest; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
      <?php } elseif(isset($_SESSION["owner"]) && $_SESSION["owner"] == true && isset($_GET["dzial"]) && $_GET["dzial"] == "podsumowanie") { ?>
        <div class="table-container">
          <h2 style="width: 100%; text-align: center">Podsumowanie sprzedaży</h2>
					<table>
						<thead>
							<tr>
								<th style="width: calc(100% / 4); padding: 17px">Wydatki na produkty</th>
								<th style="width: calc(100% / 4)">Ilość sprzedanych produktów</th>
								<th style="width: calc(100% / 4)">Wartość sprzedaży</th>
								<th>Zyski</th>
							</tr>
						</thead>
						<tbody>
              <?php
								$warunek = "";
								
								if(!empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND z.`data` BETWEEN \"".date("Y-m-d", strtotime($_GET["data-od"]))."\" AND \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";
								elseif(!empty($_GET["data-od"]) && empty($_GET["data-do"]))
									$warunek = " AND z.`data` > \"".strval(date("Y-m-d", strtotime($_GET["data-od"])))."\"";
								elseif(empty($_GET["data-od"]) && !empty($_GET["data-do"]))
									$warunek = " AND z.`data` < \"".date("Y-m-d H:i", strtotime($_GET["data-do"]) + 23*60*60 + 59*60)."\"";

                $query = "SELECT SUM(ROUND(zp.`ilosc` * p.`cena`, 2)) wydatki, SUM(zp.`ilosc`) ilosc, SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100) + 1)), 2)) przychody, SUM(ROUND(zp.`ilosc` * (p.`cena` * ((p.`marza` / 100))), 2)) zysk FROM `uzytkownicy` u JOIN `zamowienia` z ON z.`id_uzytkownika` = u.`id` JOIN `zamowione_produkty` zp ON zp.`id_zamowienia` = z.`id` JOIN `produkty` p ON p.`id` = zp.`id_produktu` WHERE u.`rola` LIKE 'user'".$warunek.";";
                $result = mysqli_query($link, $query);
                $wynik = mysqli_fetch_row($result);
              ?>
              <td style="padding: 17px"><?php echo $wynik[0]." zł"; ?></td>
              <td><?php echo $wynik[1]; ?></td>
              <td><?php echo $wynik[2]." zł"; ?></td>
              <td style="text-decoration: underline"><?php echo $wynik[3]." zł"; ?></td>
						</tbody>
					</table>
				</div>
      <?php } ?>
    </main>

  </body>
</html>