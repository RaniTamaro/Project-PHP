<html>
<?php 
	$title = "Logowanie";
	$page = "log";
	include_once('header.php');
?>
<br><div style='text-align: center'>

<?php
session_start();

// do not let user login twice
if(isset($_SESSION["logged"]) && $_SESSION["logged"]) $_SESSION["logged"] = False;

// jeśli dane zostały przesłane
if(isset($_POST['login'])) {

	// przechwytuje do zmiennych wartości podane w formularzu
	$login = $_POST['login'];
	$haslo = $_POST['haslo'];

	// otwieram plik z danymi użytkowników
	$plik = @fopen("dostep.txt", "r") 
			or exit("Brak pliku z danymi użytkowników");

	// w pętli szukam uzytkownika o podanym przy logowaniu danych (imię i hasło)
	$znaleziony = false; // flaga logiczna informująca czy w pliku są dane użytkownika czy nie
	while(!feof($plik)) {
		// $user (jako wynik funkcji fgetcsv()) jest tablicą elementów, które w stringu wejściowym (linia w pliku) 
		// były rozdzielone średnikami, za każdym wywołaniem fgetcsv() bierze kolejną linię z pliku
		$user = fgetcsv($plik, 0, ';'); // drugi parametr 0, tutaj jest konieczny 
										//(inaczej nie mógłbym ustawić trzeciego parametru)
		// sposób 2, użycie funkcji fgets() i explode() - dzieli tekst i wrzuca po kawałku do tablicy
		//$user = fgets($plik);
		//$user = explode(';',$user);
		
		if($user[0] == $login) { // użytkownik znaleziony				
			$znaleziony = true;
			break;
		}
	}
	fclose($plik);

	if($znaleziony) 
		if($user[1] == hash("sha256",$haslo)){ // hasło prawidłowe
			$_SESSION["logged"] = True;
			$_SESSION["loggedUser"] = $login;
			header("Location: home.php");
		}
		else{
			echo "Nieprawidłowe hasło - logowanie nieudane"; 
			$_SESSION["logged"] = False;
		}
	else{
		$_SESSION["logged"] = False;
		echo "Nie znaleziono użytkownika - logowanie nieudane";
	}
}
else {
	// formularz generuje tylko gdy dane jeszcze nie były wysyłane
	?>
	<h2>Logowanie do systemu.</h2>
	<form method=POST action=''>
	<table border=0 style='display: inline'>
	<tr><td>Login</td><td colspan=2>
	<input type=text name='login' size=15></td>
	</tr>
	<tr><td>Hasło</td><td colspan=2>
	<input type=password name='haslo' size=15 style='text-align:left'></td>
	</tr><tr>
	<td colspan=3>
	<input type=submit class='btn btn-light' value='Zaloguj się' style='width:100%'></td></tr>
	</table>
	</form>
<?php } // koniec else ?>
</div>
</body>
</html>
