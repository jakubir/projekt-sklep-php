<?php

ob_start();
session_start();

function sprawdz_haslo($haslo){
    $suma = 0;
    if(strlen($haslo) >= 8)
      	$suma+=1;
    for($i=0; $i<strlen($haslo); $i++){
      	if(preg_match('/^[0-9]+$/', $haslo[$i])){
        	$suma+=1;
        	break;
    	}
    }
    for($i=0; $i<strlen($haslo); $i++){
      	if(preg_match('/^[A-Z]+$/', $haslo[$i])){
        	$suma+=1;
        	break;
      	}
    }
    for($i=0; $i<strlen($haslo); $i++){
      	if(preg_match('/^[a-z]+$/', $haslo[$i])){
        	$suma+=1;
        	break;
      	}
    }
    for($i=0; $i<strlen($haslo); $i++){
      	if(preg_match('/^[!"#$%&\'()*+,-.\/]+$/', $haslo[$i])){
        	$suma+=1;
        	break;
      	}
    }
    if($suma == 5)
      	return true;
	else
    	return false;
}

$link = @mysqli_connect("localhost", "root", "", "sklep_projekt");

if(!$link){
    echo "Błąd połączenia";
    header("refresh: 2; url=index.php");
}else{
    require_once("czyszczenie.php");
    if(isset($_POST["submitR"])){
        $blad = false;
		$imie = $_POST["imie"];
		$nazwisko = $_POST["nazwisko"];
		$email = $_POST["email"];
		$haslo = $_POST["haslo"];
		if(!isset($_POST["regulamin"])){
			$blad = true;
			$_SESSION["regulamin_error"] = 1;
		}
		if(isset($_POST["newsletter"]))
			$newsletter = "tak";
		else
			$newsletter = NULL;

		if(empty($email)){
			$blad = true;
			$_SESSION["email_error"] = 2;
		}elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
          	$blad = true;
          	$_SESSION["email_error"] = $email;
        }else{
			$query = "SELECT `email` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
			$result = mysqli_query($link, $query);
			$_SESSION["email"] = $email;
			if(mysqli_num_rows($result) != 0){
				$_SESSION["email_error"] = 0;
				$blad = true;
			}
		}
        if(empty($haslo)){
			$blad = true;
			$_SESSION["haslo_error"] = 2;
		}elseif(!sprawdz_haslo($haslo)){
          	$blad = true;
          	$_SESSION["haslo_error"] = 1;
        }else{
			$_SESSION["haslo"] = $haslo;
		}
        if(empty($imie)){
			$blad = true;
			$_SESSION["imie_error"] = 2;
		}elseif(!preg_match('/^[A-ZŁŚ]{1}+[a-ząćęńóśźż]+$/', $imie)){
          	$blad = true;
          	$_SESSION["imie_error"] = $imie;
        }else{
			$_SESSION["imie"] = $imie;
		}
        if(empty($nazwisko)){
			$blad = true;
			$_SESSION["nazwisko_error"] = 2;
		}elseif(!preg_match('/^[A-ZŁŚ]{1}+[a-ząćęłńóśźż]+$/', $nazwisko)){
          	$blad = true;
          	$_SESSION["nazwisko_error"] = $nazwisko;
        }else{
			$_SESSION["nazwisko"] = $nazwisko;
		}

        if($blad){
			if(isset($_SESSION["email_error"]) && $_SESSION["email_error"] == 0){
				unset($_SESSION["imie_error"]);
				unset($_SESSION["nazwisko_error"]);
				unset($_SESSION["haslo_error"]);
				unset($_SESSION["imie"]);
				unset($_SESSION["nazwisko"]);
				unset($_SESSION["haslo"]);
				header("refresh: 0; url=logowanie.php");
			}else
				header("refresh: 0; url=rejestracja.php");
        }else{
			$haslo_hash = password_hash($haslo, PASSWORD_BCRYPT);
			$_SESSION["zalogowany"] = true;
			$_SESSION["admin"] = false;
			$_SESSION["imie_i_nazwisko"] = $imie.' '.$nazwisko;
			$_SESSION["autoryzacja"] = false;
			$query = "INSERT INTO `uzytkownicy` (`id`, `imie`, `nazwisko`, `email`, `haslo`, `rola`, `newsletter`) VALUES (NULL, '$imie', '$nazwisko', '$email', '$haslo_hash', 'user', '$newsletter');";
			$result = mysqli_query($link, $query);
			$query = "SELECT `id` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
			$result = mysqli_query($link, $query);
			$wynik = mysqli_fetch_row($result);
			$_SESSION["id_u"] = $wynik[0];
			header("refresh: 0; url=index.php");
		}

    }elseif(isset($_POST["submitL"])){
		$email = $_POST["email"];
		$haslo = $_POST["haslo"];
		$blad = false;
		$email_pop = false;
		$haslo_jest = false;

		if(empty($haslo)){
			$_SESSION["haslo_error"] = 2;
		}else{
			$haslo_jest = true;
		}

		if(empty($email)){
			$_SESSION["email_error"] = 2;
		}else{
			$_SESSION["email_error"] = $email;
			$query = "SELECT `email` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
			$result = mysqli_query($link, $query);
			if(mysqli_num_rows($result) > 0){
				unset($_SESSION["email_error"]);
				$_SESSION["email"] = $email;
				$email_pop = true;
			}
			
			if($email_pop == true && $haslo_jest == true){
				$_SESSION["haslo_error"] = 1;
				$query = "SELECT `haslo`, `rola`, `imie`, `nazwisko`, `data_ost_log` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
				$result = mysqli_query($link, $query);
				$wynik = mysqli_fetch_row($result);
				if(password_verify($haslo, $wynik[0])){
					$blad = true;
					unset($_SESSION["haslo_error"]);
					unset($_POST["email"]);
					$_SESSION["zalogowany"] = true;
					$_SESSION["admin"] = ($wynik[1] == "admin" || $wynik[1] == "owner") ? true : false;
					$_SESSION["owner"] = ($wynik[1] == "owner") ? true : false;
					$_SESSION["imie_i_nazwisko"] = $wynik[2].' '.$wynik[3];
					$_SESSION["data_ost_log"] = date("d-m-Y", strtotime($wynik[4]));
					$query = "SELECT `id` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
					$result = mysqli_query($link, $query);
					$wynik = mysqli_fetch_row($result);
					$id = $wynik[0];
					$_SESSION["id_u"] = $id;
					$data = date("Y-m-d");
					$query = "UPDATE `uzytkownicy` SET `data_ost_log` = '$data' WHERE `id` LIKE '$id'";
					$result = mysqli_query($link, $query);
				}
			}
		}	

		if($blad != true)
			header("refresh: 0; url=logowanie.php");
		else
			header("refresh: 0; url=index.php");
		
	}elseif(isset($_POST["submitW"])){
		session_unset();
		header("refresh: 0; url=logowanie.php");
	}elseif(isset($_POST["submitD"])){
		if(isset($_SESSION["admin"])){
			$nazwa = $_POST["nazwa"];
			$ilosc_dni = $_POST["ilosc_dni"];
			$data = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$ilosc_dni.' days'));
			$kategoria = $_POST["kategoria"];
			$cena = $_POST["cena"];
			$marza = $_POST["marza"];
			$ilosc = $_POST["ilosc"];
			$query = "INSERT INTO `produkty` (`id`, `nazwa`, `data_waznosci`, `kategoria_id`, `cena`, `marza`, `stan`, `ilosc`) VALUES (NULL, '$nazwa', '$data', '$kategoria', '$cena', '$marza', 'w_sprzedaży', '$ilosc')";
			mysqli_query($link, $query);
			$file = "./img/".basename($_FILES["image"]["name"]);
			if(!file_exists($_FILES["image"]["tmp_name"]))
				move_uploaded_file($_FILES["image"]["tmp_name"], $file);
			$nazwa = str_replace(' ', '', $nazwa);
			rename($file,"./img/$nazwa.png");
			header("refresh: 0; url=index.php");
		}else{
			header("refresh: 0; url=index.php");
		}
	}elseif(isset($_POST["submitE"])){
		if(isset($_SESSION["admin"])){
			$nazwa = $_POST["nazwa"];
			$nazwaS = $_POST["nazwaS"];
			$ilosc = $_POST["ilosc"];
			$ilosc_dni = $_POST["ilosc_dni"];
			$data = date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$ilosc_dni.' days'));
			$kategoria = $_POST["kategoria"];
			$cena = $_POST["cena"];
			$marza = $_POST["marza"];
			$id = $_POST["id"];
			$query = "UPDATE `produkty` SET `nazwa` = '$nazwa', `data_waznosci` = '$data', `kategoria_id` = $kategoria, `cena` = '$cena', `marza` = '$marza', `ilosc` = '$ilosc' WHERE `produkty`.`id` = $id";
			mysqli_query($link, $query);
			if(!file_exists($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])){
				$nazwa = str_replace(' ', '', $nazwa);
				$nazwaS = str_replace(' ', '', $nazwaS);
				rename("./img/$nazwaS.png","./img/$nazwa.png");
			}else{
				$file = "./img/".basename($_FILES["image"]["name"]);
				move_uploaded_file($_FILES["image"]["tmp_name"], $file);
				$nazwa = str_replace(' ', '', $nazwa);
				rename($file,"./img/$nazwa.png");
			}
			header("refresh: 0; url=index.php");
		}else{
			header("refresh: 0; url=index.php");
		}
	}elseif(isset($_POST["submitU"])){
		if(isset($_SESSION["admin"])){
			$id = $_POST["id"];
			$query = "UPDATE `produkty` SET `stan` = 'usunięty' WHERE `produkty`.`id` = '$id'";
			mysqli_query($link, $query);
			header("refresh: 0; url=index.php");
		}else{
			header("refresh: 0; url=index.php");
		}
	}elseif(isset($_POST["submitADK"])){
		$nazwa = $_POST["kategoria"];
		$query = "INSERT INTO `kategorie` (`nazwa`) VALUES ('$nazwa')";
		$result = mysqli_query($link, $query);
		$_SESSION["kategoria"] = $nazwa;
		$_SESSION["nazwa"] = $_POST["nazwa"];
		if(!isset($_POST["nazwaS"]))
			$_POST["nazwaS"] = " ";
		$_SESSION["nazwaS"] = $_POST["nazwaS"];
		$_SESSION["ilosc"] = $_POST["ilosc"];
		$_SESSION["ilosc_dni"] = $_POST["ilosc_dni"];
		$_SESSION["cena"] = $_POST["cena"];
		$_SESSION["marza"] = $_POST["marza"];
		if(!isset($_POST["id"]))
			$_POST["id"] = "a";
		$id = $_POST["id"];
		$url = "dodajProdukt.php?id=$id";
		if($_POST["se"] == "Edytuj")
			$url .= "&submitE=Edytuj";
		header("refresh: 0; url=$url");
	}elseif(isset($_POST["submitUK"])){
		$id = $_SESSION['id_u'];
		$data = date("Y-m-d");
		$query = "UPDATE `uzytkownicy` SET `prosba` = '$data' WHERE `id` = '$id'";
		mysqli_query($link, $query);
		header("refresh: 0; url=prosba.php");
	}elseif(isset($_POST["submitNUK"])){
		$id = $_SESSION['id_u'];
		$query = "UPDATE `uzytkownicy` SET `prosba` = NULL WHERE `id` = '$id'";
		mysqli_query($link, $query);
		header("refresh: 0; url=index.php");
	}elseif(isset($_POST["submitEK"])){
		$id = $_SESSION['id_u'];
		if(isset($_POST["newsletter"])){
			$newsletter = 'tak';
		}else{
			$newsletter = NULL;
		}
		$query = "UPDATE `uzytkownicy` SET `newsletter` = '$newsletter' WHERE `id` LIKE '$id'";
		$result = mysqli_query($link, $query);

		if(isset($_POST["haslo"]) && !empty($_POST["haslo"])){
			$haslo = $_POST["haslo"];
			$hasloStare = $_POST["hasloS"];
			$id_u = $_SESSION["id_u"];
			$query = "SELECT `haslo` FROM `uzytkownicy` WHERE `id` LIKE '$id_u'";
			$result = mysqli_query($link, $query);
			$wynik = mysqli_fetch_row($result);
			if(password_verify($hasloStare, $wynik[0])){
				if(!sprawdz_haslo($haslo)){
					$_SESSION["haslo_error"] = 1;
					header("refresh: 0; url=panelUzytkownika.php");
				}else{
					$haslo_hash = password_hash($haslo, PASSWORD_BCRYPT);
					$id = $_SESSION['id_u'];
					$query = "UPDATE `uzytkownicy` SET `haslo`= '$haslo_hash' WHERE `id` LIKE '$id'";
					$result = mysqli_query($link, $query);
					header("refresh: 0; url=panelUzytkownika.php");
				}
			}else{
				$_SESSION["haslo"] = $haslo;
				$_SESSION["haslo_error"] = 2;
				header("refresh: 0; url=panelUzytkownika.php");
			}
		}else{	
			header("refresh: 0; url=panelUzytkownika.php");
		}
	}elseif(isset($_POST["submitK"])){
		$id_p = $_POST['id'];
		$ilosc = $_POST['ilosc'];
		$query = "SELECT `ilosc` FROM produkty WHERE `id` = $id_p";
		$result = mysqli_query($link, $query);
		$wynik = mysqli_fetch_row($result);
		if($wynik[0] < $ilosc || $ilosc > 10){
			$_SESSION["ilosc_error"] = $id_p;
			header("refresh: 0; url=index.php");
		}else{
			$ilosc_pop = $wynik[0];
			$id = $_SESSION['id_u'];
			$czas = time()+60*15;
			$czas2 = date("Y-m-d H:i:s", $czas);
			if(!isset($_COOKIE["idz"])){
				$query = "INSERT INTO `zamowienia` (`id_uzytkownika`, `stan`, `data_przedawnienia`) VALUES ('$id', 'koszyk', '$czas2')";
				$result = mysqli_query($link, $query);
				$query = "SELECT `id` FROM `zamowienia` WHERE `id_uzytkownika` = '$id' AND `stan` = 'koszyk'";
				$result = mysqli_query($link, $query);
				$wynik = mysqli_fetch_row($result);
				setcookie("idz", $wynik[0], $czas, "/");
				$id_z = $wynik[0];
			}else{
				$query = "SELECT `id` FROM `zamowienia` WHERE `id_uzytkownika` = '$id' AND `stan` = 'koszyk'";
				$result = mysqli_query($link, $query);
				$wynik = mysqli_fetch_row($result);
				setcookie("idz", $wynik[0], $czas, "/");
				$id_z = $wynik[0];
				$query = "UPDATE `zamowienia` SET `data_przedawnienia` ='$czas2' WHERE `id` = '$id_z'";
				$result = mysqli_query($link, $query);
			}
			$query = "SELECT `ilosc` FROM `zamowione_produkty` WHERE `id_zamowienia` = '$id_z' AND `id_produktu` = '$id_p'";
			$result = mysqli_query($link, $query);
			if(mysqli_num_rows($result) > 0){
				$wynik = mysqli_fetch_row($result);
				$ilosc2 = $ilosc + $wynik[0];
				if($ilosc2 > 10)
					$ilosc2 = 10;
				$query = "UPDATE `zamowione_produkty` SET `ilosc` = '$ilosc2' WHERE `id_zamowienia` = '$id_z' AND `id_produktu` = '$id_p'";
				$result = mysqli_query($link, $query);
				$ilosc = $ilosc_pop + $wynik[0] - $ilosc2;
				$query = "UPDATE produkty SET `ilosc` = '$ilosc' WHERE `id` = '$id_p'";
				$result = mysqli_query($link, $query);
			}else{
				$query = "INSERT INTO `zamowione_produkty` (`id_produktu`, `ilosc`, `id_zamowienia`) VALUES ('$id_p', '$ilosc', '$id_z')";
				$result = mysqli_query($link, $query);
				$ilosc = $ilosc_pop - $ilosc;
				$query = "UPDATE produkty SET `ilosc` = '$ilosc' WHERE `id` = '$id_p'";
				$result = mysqli_query($link, $query);
			}
			header("refresh: 0; url=koszyk.php");
		}
	}elseif(isset($_POST["submitEPK"])){
		$id_z = $_COOKIE["idz"];
		$id_p = $_POST["id"];
		$ilosc = $_POST["ilosc"];
		$query = "SELECT `ilosc` FROM `zamowione_produkty` WHERE `id_zamowienia` = '$id_z' AND `id_produktu` = '$id_p'";
		$result = mysqli_query($link, $query);
		$wynik = mysqli_fetch_row($result);
		$ilosc_z_pop = $wynik[0];
		$query = "SELECT `ilosc` FROM produkty WHERE `id` = '$id_p'";
		$result = mysqli_query($link, $query);
		$wynik = mysqli_fetch_row($result);
		$ilosc_pop = $wynik[0];
		if($ilosc_pop + $ilosc_z_pop < $ilosc || $ilosc > 10){
			header("refresh: 0; url=koszyk.php");
		}else{
			$query = "UPDATE `zamowione_produkty` SET `ilosc` = '$ilosc' WHERE `id_zamowienia` = '$id_z' AND `id_produktu` = '$id_p'";
			$result = mysqli_query($link, $query);
			$ilosc_pop = $ilosc_pop + $ilosc_z_pop - $ilosc;
			$query = "UPDATE produkty SET `ilosc` = '$ilosc_pop' WHERE `id` = '$id_p'";
			$result = mysqli_query($link, $query);
			header("refresh: 0; url=koszyk.php");
		}
	}elseif(isset($_POST["submitUP"])){
		$id_z = $_COOKIE["idz"];
		$id_p = $_POST["id"];
		$query = "SELECT `ilosc` FROM `zamowione_produkty` WHERE `id_zamowienia` = '$id_z' AND `id_produktu` = '$id_p'";
		$result = mysqli_query($link, $query);
		$wynik = mysqli_fetch_row($result);
		$ilosc = $wynik[0];
		$query = "SELECT `ilosc` FROM produkty WHERE `id` = '$id_p'";
		$result = mysqli_query($link, $query);
		$wynik = mysqli_fetch_row($result);
		$ilosc_pop = $wynik[0];
		$ilosc = $ilosc + $ilosc_pop;
		$query = "UPDATE produkty SET `ilosc` = '$ilosc' WHERE `id` = '$id_p'";
		$result = mysqli_query($link, $query);
		$query = "DELETE FROM `zamowione_produkty` WHERE `id_produktu` = '$id_p' AND `id_zamowienia` = '$id_z'";
		$result = mysqli_query($link, $query);
		header("refresh: 0; url=koszyk.php");
	}elseif(isset($_POST["submitKUP"])){
		$id_z = $_COOKIE["idz"];
		$query = "UPDATE `zamowienia` SET `stan` = 'do realizacji', `data` = CURRENT_TIMESTAMP(), `data_przedawnienia` = NULL WHERE `id` = $id_z";
		$result = mysqli_query($link, $query);
		setcookie("idz", '', time() - 3600, '/');
		header("refresh: 0; url=panelUzytkownika.php");
	}elseif(isset($_POST["submitOH"])){
		$email = $_POST["email"];
		$_SESSION["email"] = $email;
		if(empty($email)){
			$_SESSION["email_error"] = 1;
			header("refresh: 0; url=odzyskanieHasla.php");
		}else{
			$query = "SELECT `email` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
			$result = mysqli_query($link, $query);
			if(mysqli_num_rows($result) > 0){
				$_SESSION["zmiana_hasla"] = 1;
				$kod = '';
				for($i=0; $i<6; $i++)
					$kod = $kod.strval(random_int(0, 9));
				$query = "UPDATE `uzytkownicy` SET `kod` = '$kod' WHERE `email` LIKE '$email'";
				$result = mysqli_query($link, $query);	
				header("refresh: 0; url=odzyskanieHasla.php");
			}else{
				$_SESSION["email_error"] = 2;
				header("refresh: 0; url=odzyskanieHasla.php");
			}
		}
	}elseif(isset($_POST["submitZOH"])){
		$blad = true;
		$kod = $_POST["kod"];
		$haslo = $_POST["haslo"];
		$email = $_SESSION["email"];
		if(empty($kod)){
			$_SESSION["kod_error"] = 2;
		}else{
			$_SESSION["kod_error"] = 1;
			$query = "SELECT `kod` FROM `uzytkownicy` WHERE `email` LIKE '$email'";
			$result = mysqli_query($link, $query);
			$wynik = mysqli_fetch_row($result);
			if($wynik[0] == $kod){
				unset($_SESSION["kod_error"]);
				$_SESSION["kod"] = $kod;
				if(empty($haslo)){
					$_SESSION["haslo_error"] = 2;
				}elseif(!sprawdz_haslo($haslo)){
					  $_SESSION["haslo"] = $haslo;
					  $_SESSION["haslo_error"] = 1;
				}else{
					$blad = false;
					$query = "UPDATE `uzytkownicy` SET `kod` = NULL WHERE `email` LIKE '$email'";
					$result = mysqli_query($link, $query);
					$haslo_hash = password_hash($haslo, PASSWORD_BCRYPT);
					$query = "UPDATE `uzytkownicy` SET `haslo`= '$haslo_hash' WHERE `email` LIKE '$email'";
					$result = mysqli_query($link, $query);
					unset($_SESSION["email"]);
					unset($_SESSION["zmiana_hasla"]);
				}
			}
		}	

		if($blad != true)
			header("refresh: 0; url=logowanie.php");
		else
			header("refresh: 0; url=odzyskanieHasla.php");
	}elseif(isset($_POST["submitOSC"])){
		$id = $_POST["id"];
		$stan = $_POST["stan"];
		$query="UPDATE `zamowienia` SET `stan` = '$stan' WHERE `id` = '$id';";
		$result = mysqli_query($link, $query);
		header("refresh: 0; url=index.php?dzial=zamowienia");
	}else{
		header("refresh: 0; url=index.php");
	}
}

?>