<?php
$title = "Raport dzienny";
$page = "dayreport";
include_once('header.php');
include('functions.php');

session_start();
check_if_user_logged($_SESSION);


$_SESSION["reportDate"] = Null;
if (isset($_POST["reportDate"]))
    $_SESSION["reportDate"] = $_POST['reportDate'];

//Display list of checkins from database
function show_checkins()
{
    global $connection;

    $reportDate = $_SESSION["reportDate"];

    $request = "select pg.Id, g.imie, g.nazwisko, g.iloscdoroslych, g.iloscdzieci, 
					pg.sniadanie, pg.parking, pg.transport, p.cenaosoba, p.cenapokoj,
					p.nazwa, o.datazameldowania, o.datawymeldowania, 
					datediff(o.datawymeldowania, o.datazameldowania)
                    from pokoj_goscie pg
                    join goscie g on pg.IdGoscia = g.Id
                    join pokoj p on pg.IdPokoju = p.Id
                    join okres_wynajmu o on pg.IdOkresuWynajmu = o.Id
					where o.datazameldowania <= '$reportDate'
					and o.datawymeldowania >= '$reportDate' ;";
    $result = mysqli_query($connection, $request);
    if (!$result)
        return;

    $headers = array("Imię i nazwisko gościa", "Dorośli / Dzieci", "Dodatki", "Nazwa pokoju");
    ?>
    <form method='POST'>
        <h3>Raport dzienny</h3>

        <table class='table table-striped table-color'>
            <tr>
                <td>
                    <label for="checkinStartDateInput">Data</label>
                    <input type="date" required class="form-control" id="checkinStartDateInput" name="reportDate"
                        value="<?= $_SESSION["reportDate"] ?>">
                </td>
                <td>
                    <p> </p>
                    <input type='submit' class='btn btn-outline-dark' id="checkinEndDateInput" name="generuj"
                        value="Generuj">
                </td>
            </tr>
        </table>

        <table class='table table-striped table-color' id="checkinTable">
            <thead>
                <tr class="text-center">
                    <?php
                    foreach ($headers as $header)
                        echo "<th scope='col'>$header</th>";

                    echo "<th scope='col'></th></tr></thead><tbody>";
                    while ($row = mysqli_fetch_row($result)) {
                        echo "<tr class='text-center'>";
                        echo "<td>$row[1]  $row[2]</td>";
                        echo "<td>$row[3] / $row[4]</td>";
                        echo "<td> $row[5] / $row[6] / $row[7]</td>";
                        echo "<td> $row[10] </td>";
                        print("</tr>");
                    } ?>
                    </tbody>
        </table>
    </form>


    <?= mysqli_free_result($result);
}


open_connection();

show_checkins();
close_connection();
?>

</body>

</html>