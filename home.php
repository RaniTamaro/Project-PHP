<?php
    session_start();
	include('functions.php');
	check_if_user_logged($_SESSION);
	
    $title = "Strona główna";
    $page = "home";
    include_once('header.php');

?>
        <h3 class="display-5"> Witaj <?= $_SESSION["loggedUser"] ?>!</h3>
        <p>Wszystkie opcje dostępne dla pracownika znajdują się w menu.<br>
        Życzymy miłego dnia pracy!</p>
    </div>


    </body>
</html>