<?php
    $title = "Raporty";
    $page = "reports";
    include_once('header.php');
	include('functions.php');
	
	session_start();
	check_if_user_logged($_SESSION);
	
	
	$_SESSION["reportStartDate"] = Null; 
	if (isset($_POST["reportStartDate"]))
		$_SESSION["reportStartDate"] =  $_POST['reportStartDate'];
	$_SESSION["reportEndDate"] = Null;	
	if (isset($_POST["reportEndDate"]))
		$_SESSION["reportEndDate"] =  $_POST['reportEndDate'];
	
	$_SESSION["cenaSuma"] = 0;
	
	function calculate_price($perPerson, $perRoom, $adultNumber, $kidsNumber, $sniadanie, $parking, $transport, $numOfDays){
		$price = $perPerson * ($adultNumber + $kidsNumber);
		if ($price > $perRoom) $price = $perRoom;
		
		if ($sniadanie) $price += $adultNumber * 25 + $kidsNumber * 15;
		if ($parking) $price += 55;
		
		// times number of days in hotel
		$price = $price * $numOfDays;
		
		// transfort is only once per visit
		if ($transport) $price += 200;
		
		return $price;
	}

	
	//Display list of checkins from database
    function show_checkins(){
        global $connection;
		
		$reportStartDate = $_SESSION["reportStartDate"];
		$reportEndDate = $_SESSION["reportEndDate"];

        $request = "select pg.Id, g.imie, g.nazwisko, g.iloscdoroslych, g.iloscdzieci, 
					pg.sniadanie, pg.parking, pg.transport, p.cenaosoba, p.cenapokoj,
					p.nazwa, o.datazameldowania, o.datawymeldowania, 
					datediff(o.datawymeldowania, o.datazameldowania)
                    from pokoj_goscie pg
                    join goscie g on pg.IdGoscia = g.Id
                    join pokoj p on pg.IdPokoju = p.Id
                    join okres_wynajmu o on pg.IdOkresuWynajmu = o.Id
					where o.datazameldowania >= '$reportStartDate'
					and o.datawymeldowania <= '$reportEndDate' ;" ;
        $result = mysqli_query($connection, $request);
        if (!$result)
            return;

        $headers = array("Imię i nazwisko gościa", "Dorośli / Dzieci", "Dodatki", "Nazwa pokoju", "Data zameldowania", "Data wymeldowania", "Liczba dni", "Cena");
        ?>
        <form method='POST'>
            <h3>Raport</h3>

			<table class='table table-striped table-color' ><tr><td>
		    <label for="checkinStartDateInput">Zalemdowanie od</label>
			<input type="date" required class="form-control" id="checkinStartDateInput" name="reportStartDate" value="<?=$_SESSION["reportStartDate"]?>">
			</td><td>
		    <label for="checkinEndDateInput">Zalemdowanie do</label>
			<input type="date" required class="form-control" id="checkinEndDateInput" name="reportEndDate" value="<?=$_SESSION["reportEndDate"]?>">
			</td><td>
			<p> </p>
			<input  type='submit' class='btn btn-outline-dark' id="checkinEndDateInput" name="generuj" value="Generuj">
			</td></tr></table>
			
            <table class='table table-striped table-color' id="checkinTable">
                <thead>
                    <tr class="text-center">
                    <?php
                    foreach ($headers as $header)
                        echo "<th scope='col'>$header</th>";

                    echo "<th scope='col'></th></tr></thead><tbody>";
                    while ($row = mysqli_fetch_row($result)) {
						$row_price = calculate_price($row[8], $row[9], $row[3], $row[4], $row[5], $row[6], $row[7], $row[13]);
						$_SESSION["cenaSuma"] = $_SESSION["cenaSuma"] + $row_price;
                        echo "<tr class='text-center'>";
                        echo "<td>$row[1]  $row[2]</td>";
						echo "<td>$row[3] / $row[4]</td>";
						echo "<td> $row[5] / $row[6] / $row[7]</td>";
                        foreach ($row as $c => $cell)
                            if($c > 9)
                                echo "<td>$cell</td>";
						echo  "<td> $row_price zł </td>";
                        print("</tr>");
                    } ?>
                    </tbody>
            </table>
        </form>
		
		<p> </p><p> </p>
		<p><b>Suma: <?=$_SESSION["cenaSuma"]?> zł </b></p>

        <?= mysqli_free_result($result);
    }


	open_connection();

    show_checkins();
    close_connection();

?>

    </body>
</html>