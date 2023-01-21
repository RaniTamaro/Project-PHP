<?php
function open_connection(){
	global $connection;
	$server = "127.0.0.1";
	$user = "root";
	$pass = "";
    $database = "hotel";

	$connection = mysqli_connect($server, $user, $pass) or exit("Nieudane połączenie z serwerem");
    mysqli_select_db($connection, $database);

	mysqli_set_charset($connection, "utf8");
}

function close_connection(){
	global $connection;
	mysqli_close($connection);
}
?>