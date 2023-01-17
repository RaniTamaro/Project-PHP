<?php
function otworz_polaczenie(){
	global $polaczenie;
	$serwer = "127.0.0.1";
	$uzytkownik = "root";
	$haslo = "";
    $baza = "hotel";

	$polaczenie = mysqli_connect($serwer, $uzytkownik, $haslo) or exit("Nieudane połączenie z serwerem");
    mysqli_select_db($polaczenie, $baza);

	mysqli_set_charset($polaczenie, "utf8");
}

function zamknij_polaczenie(){
	global $polaczenie;
	mysqli_close($polaczenie);
}
?>