<?php
$title = "Wolne pokoje";
$page = "free";
include_once('header.php');
include('functions.php');

session_start();
check_if_user_logged($_SESSION);


$_SESSION["freeRoomStartDate"] = Null;
if (isset($_POST["freeRoomStartDate"]))
	$_SESSION["freeRoomStartDate"] = $_POST['freeRoomStartDate'];
$_SESSION["freeRoomEndDate"] = Null;
if (isset($_POST["freeRoomEndDate"]))
	$_SESSION["freeRoomEndDate"] = $_POST['freeRoomEndDate'];



//Display list of rooms from database
function show_free_rooms()
{
	global $connection;

	$freeRoomStartDate = $_SESSION["freeRoomStartDate"];
	$freeRoomEndDate = $_SESSION["freeRoomEndDate"];

	// get all reserved rooms 
	$request = "select pg.Id, p.id, p.nazwa, o.datazameldowania, o.datawymeldowania
                    from pokoj_goscie pg
                    join pokoj p on pg.IdPokoju = p.Id
                    join okres_wynajmu o on pg.IdOkresuWynajmu = o.Id
					where o.datazameldowania <= '$freeRoomEndDate'
					and o.datawymeldowania >= '$freeRoomStartDate' ;";
	$result = mysqli_query($connection, $request);

	// change result to array od ids
	$reservedRooms = [];
	while ($row = mysqli_fetch_array($result)) {
		$reservedRooms += [$row[1]];
	}

	mysqli_free_result($result);

	// get all rooms from database
	$request = "select p.id, p.nazwa, p.lozka, p.cenaosoba, p.cenapokoj
                    from pokoj p;";
	$allRooms = mysqli_query($connection, $request);
	if (!$allRooms)
		return;

	// filterout only free rooms
	$freeRooms = [];
	while ($row = mysqli_fetch_array($allRooms)) {
		$isfree = true;

		foreach ($reservedRooms as $resId) {
			if ($row[0] == $resId)
				$isfree = false;
		}

		if ($isfree)
			array_push($freeRooms, $row);
	}

	$headers = array("Nazwa pokoju", "Liczba łóżek", "Cena za osobę", "Cena za pokój");
	?>
	<form method='POST'>
		<h3>Wolne pokoje</h3>

		<table class='table table-striped table-color'>
			<tr>
				<td>
					<label for="checkinStartDateInput">Zalemdowanie od</label>
					<input type="date" required class="form-control" id="checkinStartDateInput" name="freeRoomStartDate"
						value="<?= $_SESSION["freeRoomStartDate"] ?>">
				</td>
				<td>
					<label for="checkinEndDateInput">Zalemdowanie do</label>
					<input type="date" required class="form-control" id="checkinEndDateInput" name="freeRoomEndDate"
						value="<?= $_SESSION["freeRoomEndDate"] ?>">
				</td>
				<td>
					<p> </p>
					<input type='submit' class='btn btn-outline-dark' id="checkinEndDateInput" name="generuj" value="Pokaż">
				</td>
			</tr>
		</table>

		<table class='table table-striped table-color' id="checkinTable">
			<thead>
				<tr class="text-center">
					<?php
					foreach ($headers as $header)
						echo "<th scope='col'>$header</th>";

					echo "<th scope='col'></th></tr></thead><tbody>"; foreach ($freeRooms as $row) {
						echo "<tr class='text-center'>";
						echo "<td>$row[1]</td>";
						echo "<td>$row[2]</td>";
						echo "<td>$row[3]</td>";
						echo "<td>$row[4]</td>";

					} ?>
					</tbody>
		</table>
	</form>

<?php
}


open_connection();

show_free_rooms();
close_connection();
?>

</body>

</html>