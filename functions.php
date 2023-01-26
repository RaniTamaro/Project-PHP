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

function check_if_user_logged($SESSION){
	if(!isset($SESSION["logged"]) || !$SESSION["logged"]) header("Location: log.php");
}

function generatedSelect($table, $selectName, $selectedOption = null){
    echo "<select name='$selectName' class='form-control' placeholder='Nazwa pokoju...>";
	echo "<option value='noSelection'>Wybierz z listy ...</option>";
        foreach ($table as $t => $tab)
            echo "<option value='$t'".($t==$selectedOption?' selected':'').">$tab</option>";
    echo '</select>';
}
?>