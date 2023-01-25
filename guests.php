<?php
$title = "Goście";
$page = "guests";
include_once('header.php');
include('functions.php');

session_start();
check_if_user_logged($_SESSION);

// reset modal form flag
$_SESSION["modalform"] = '';


// display list of all guests from database
function show_guest()
{
    global $connection;

    $request = "select * from goscie";
    $result = mysqli_query($connection, $request);
    if (!$result)
        return;

    $headers = array("Imię", "Nazwisko");
    ?>
    <form method='POST'>
        <h3>Goście</h3>
        <div style="text-align:right">
            <input type="submit" class="btn btn-outline-dark" name='button[-1]' value="Dodaj nowego gościa" />
        </div>

        <input type="text" class="form-control search-input" id="guestSearch" onkeyup="searchFuntion()" placeholder="Wyszukaj gościa"/>
        <table class='table table-striped table-color' id="guestsTable">
            <thead>
                <tr class="text-center">
                <?php
                foreach ($headers as $header)
                    echo "<th scope='col'>$header</th>";

                echo "<th scope='col' style='width:50%'></th></tr></thead><tbody>";
                while ($row = mysqli_fetch_row($result)) {
                    echo "<tr class='text-center'>";
                    foreach ($row as $c => $cell)
                        if ($c != 0 && $c < 3)
                            echo "<td>$cell</td>";
                    echo "<td><input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Zamelduj'>
                            <input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Edytuj'>
                            <input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Usuń'>
                        </td>";
                    print("</tr>");
                } ?>
                </tbody>
        </table>
    </form>

    <?= mysqli_free_result($result);
}

// display form to edit or add guest
function edit_guest($no = -1)
{
    global $connection;

    if($no != -1) {
		$command = "select imie, nazwisko, iloscdoroslych, iloscdzieci from goscie where Id=$no;";
		$row = mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
                
        $guest = mysqli_fetch_row($row);
        $_SESSION["name"] = $guest[0];
        $_SESSION["surname"] = $guest[1];
        $_SESSION["adultNumber"] = $guest[2];
        $_SESSION["kidsNumber"] = $guest[3];
	}
	else {
        $_SESSION["name"] = '';
        $_SESSION["surname"] = '';
        $_SESSION["adultNumber"] = 1;
        $_SESSION["kidsNumber"] = 0;
	}

    $_SESSION["modalform"] = 'editGuest';
}

// save guest from form to database
function save_guest($no)
{
    global $connection;
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $adultNumber = $_POST['adultNumber'];
    $kidsNumber = $_POST['kidsNumber'];
    // isset($_POST['adult']) ? $adult = 'T' : $adult = 'N';

    if($no != -1)
        $command = "update goscie set imie='$name', nazwisko='$surname', iloscdoroslych=$adultNumber, iloscdzieci=$kidsNumber where id=$no;";
    else $command = "insert into goscie values(null, '$name', '$surname', $adultNumber, $kidsNumber);";
    
    mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
    
    header("Location: guests.php");
}

// delete guest from database
function delete_guest($no)
{
    global $connection;
	
	$command = "delete from goscie where id=$no;";		
	mysqli_query($connection, $command) or exit("Błąd w zapytaniu: $command");
}

// display form for adding checkin and checkout
function checkin_guest($no)
{
    global $connection;

    $roomArray = [];
    $request = "select id, nazwa from pokoj;";
    $result = mysqli_query($connection, $request);
    while($row = mysqli_fetch_array($result)){
        $roomArray += [$row[0] => $row[1]];
    }

    $_SESSION["roomList"] = $roomArray;
	// $_SESSION["roomName"] = '';
    $_SESSION["checkInDate"] = '';
    $_SESSION["checkOutDate"] = '';
    $_SESSION["breakfast"] = 'N';
    $_SESSION["parking"] = 'N';
    $_SESSION["transport"] = 'N';

    $_SESSION["modalform"] = 'addCheckIn';
}

	
// save checkin and checkout in database
function save_checkin($no)
{
	global $connection;
	// get dates and roomId from form
	$roomId = $_POST['roomIdSelect'];
    $checkInDate = $_POST['checkInDate'];
    $checkOutDate = $_POST['checkOutDate'];
	
	// fill okres_wynajmu table
	$command = "insert into okres_wynajmu values(null, '$checkInDate', '$checkOutDate');";  
    mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
	
    isset($_POST['breakfast']) ? $breakfast = 'T' : $breakfast = 'N';
    isset($_POST['parking']) ? $parking = 'T' : $parking = 'N';
    isset($_POST['transport']) ? $transport = 'T' : $transport = 'N';

	// fill pokoj_goscie table
	$checkId = mysqli_insert_id($connection);
	$command = "insert into pokoj_goscie values(null, '$roomId', '$no', '$checkId', '$breakfast', '$parking', '$transport');";  
    mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
    
    header("Location: guests.php");

}


open_connection();

$option = '';
if(isset($_POST['button'])) {	
	$no = key($_POST['button']);
	$option = $_POST['button'][$no];
    $_SESSION["no"] = $no;
}

switch($option) {
    case 'Edytuj': edit_guest($no); break;
    case 'Dodaj nowego gościa': edit_guest(); break;
    case 'Zapisz': save_guest($no); break;
    case 'Usuń': delete_guest($no); break;
	case 'Zamelduj': checkin_guest($no); break;
    case 'Potwierdź': save_checkin($no); break;
}


show_guest();
close_connection();
?>

</div>

<!-- Edit Guest Modal -->
<div class="modal fade" id="editGuest" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Dane gościa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form method=POST action=''>
                    <div class="modal-body">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="nameInput" name="name" placeholder="Imię..." value="<?=$_SESSION["name"]?>">
                                <label for="nameInput">Imię</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="surnameInput" name="surname" placeholder="Nazwisko..." value="<?=$_SESSION["surname"]?>">
                                <label for="surnameInput">Nazwisko</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min=1 max=10 class="form-control" id="adultNumberInput" name="adultNumber" placeholder="Liczba dorosłych..." value="<?=$_SESSION["adultNumber"]?>">
                                <label for="adultNumberInput">Liczba dorosłych</label>
                            </div>
                            <div class="form-floating">
                                <input type="number" min=0 max=10 class="form-control" id="kidsNumberInput" name="kidsNumber" placeholder="Liczba dzieci..." value="<?=$_SESSION["kidsNumber"]?>">
                                <label for="kidsNumberInput">Liczba dzieci</label>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="Anuluj"/>
                        <input type="submit" name="button[<?=$no?>]" class="btn btn-primary" value="Zapisz"/>
                    </div>
                </form>
        </div>
    </div>
</div>


<!-- Checkin Guest Modal -->
<div class="modal fade" id="addCheckIn" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Zameldowanie</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form method=POST action=''>
                    <div class="modal-body">
                        <div class="form-floating mb-3">
                            <?php
                                generatedSelect($_SESSION["roomList"], "roomIdSelect");
                            ?>
                            <label for="roomIdSelect">Pokój</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input id="startDate" class="form-control" type="date" name="checkInDate"/>
                            <label for="startDate">Od</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input id="endDate" class="form-control" type="date" name="checkOutDate"/>
                            <label for="endDate">Do</label>
                        </div>
                        <div class="margin-5px mb-3">
                            <h5>Dodatkowe udogodnienia:</h5>
                        </div>
                        <div class="margin-5px mb-3">
                            <input type="checkbox" class="mb-3" id="breakfastInput" name="breakfast" <?php $_SESSION["breakfast"] == 'T' ? print('checked') : '' ?>/>
                            <label for="breakfastInput">Wliczone śniadanie</label>
                            <input type="checkbox" class="mb-3 margin-5px" id="parkingInput" name="parking" <?php $_SESSION["parking"] == 'T' ? print('checked') : '' ?>/>
                            <label for="parkingInput">Płatny parking</label>
                        </div>
                        <div class="margin-5px mb-3">
                            <input type="checkbox" class="mb-3" id="transportInput" name="transport" <?php $_SESSION["transport"] == 'T' ? print('checked') : '' ?>/>
                            <label for="transportInput">Transport z lotniska lub dworca</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="Anuluj"/>
                        <input type="submit" name="button[<?=$no?>]" class="btn btn-primary" value="Potwierdź"/>
                    </div>
                </form>
        </div>
    </div>
</div>


<script>

	// date validation
	
	var start = document.getElementById('startDate');
	var end = document.getElementById('endDate');

	start.addEventListener('change', function() {
		if (start.value)
			end.min = start.value;
	}, false);
	end.addEventLiseter('change', function() {
		if (end.value)
			start.max = end.value;
	}, false);
	

    //Choose modal to display
    $(document).ready(function () {
        //Function get value with information about modal name
        let value = '<?php echo $_SESSION['modalform']?>';
        if (value == 'editGuest') {
            $('#editGuest').modal('show');
        }
		if (value == 'addCheckIn') {
            $('#addCheckIn').modal('show');
        }
    });


    //Show serched guests and hide other with use style
    function searchFuntion(){
        let input, filter, table, tr, td, i, txtValue1, txtValue2;
        input = document.getElementById('guestSearch');
        filter = input.value;
        table = document.getElementById('guestsTable');
        tr = table.getElementsByTagName('tr');

        for (i = 0; i < tr.length; i++){
            td1 = tr[i].getElementsByTagName('td')[0];
            td2 = tr[i].getElementsByTagName('td')[1];
            if (td1 || td2){
                txtValue1 = td1.textContent || td1.innerHTML;
                txtValue2 = td2.textContent || td2.innerHTML;
                if (txtValue1.indexOf(filter) > -1 || txtValue2.indexOf(filter) > -1){
                    tr[i].style.display = "";
                }
                else{
                    tr[i].style.display = "none";
                }
            }
        }
    }
</script>

</div>
</body>

</html>