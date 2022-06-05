<?php

$query = "SELECT `id`, `data_przedawnienia`, `stan` FROM `zamowienia`";
$result = mysqli_query($link, $query);
$czas = time();
while($wynik = mysqli_fetch_row($result)){
    if(strtotime($wynik[1]) < $czas && $wynik[2] == "koszyk"){
        $id = $wynik[0];
        $query2 = "SELECT `id`, `id_produktu`, `ilosc` FROM `zamowione_produkty` WHERE `id_zamowienia` LIKE '$id'";
        $result2 = mysqli_query($link, $query2);
        while($wynik2 = mysqli_fetch_row($result2)){
            $id_zp = $wynik2[0];
            $id_p = $wynik2[1];
            $ilosc = $wynik2[2];
            $query3 = "UPDATE `produkty` SET `ilosc` = `ilosc` + '$ilosc'  WHERE `id` LIKE '$id_p'";
            $result3 = mysqli_query($link, $query3);
            $query3 = "DELETE FROM `zamowione_produkty` WHERE `id` LIKE '$id_zp'";
            $result3 = mysqli_query($link, $query3);
        }
        $query2 = "DELETE FROM `zamowienia` WHERE `id` = '$id'";
        $result2 = mysqli_query($link, $query2);
    }
}

?>