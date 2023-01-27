<?php
    $title = "Rezerwacje gości";
    $page = "checkin";
    include_once('header.php');
    include('functions.php');

    session_start();
	check_if_user_logged($_SESSION);

    // reset modal form flag
	$_SESSION["modalform"] = '';

    //Display list of checkins from database
    function show_checkins(){
        global $connection;

        $request = "select pg.Id, g.imie, g.nazwisko, p.nazwa, o.datazameldowania, o.datawymeldowania
                    from pokoj_goscie pg
                    join goscie g on pg.IdGoscia = g.Id
                    join pokoj p on pg.IdPokoju = p.Id
                    join okres_wynajmu o on pg.IdOkresuWynajmu = o.Id;";
        $result = mysqli_query($connection, $request);
        if (!$result)
            return;

        $headers = array("Imię i nazwisko gościa", "Nazwa pokoju", "Data zameldowania", "Data wymeldowania");
        ?>
        <form method='POST'>
            <h3>Rezerwacje</h3>

            <input type="text" class="form-control search-input" id="checkinSearch" onkeyup="searchFuntion()" placeholder="Wyszukaj rezerwacje"/>
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
                        foreach ($row as $c => $cell)
                            if($c > 2)
                                echo "<td>$cell</td>";
                        echo "<td><input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Edytuj dane'>
                                <input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Odwołaj wizytę'>
                            </td>";
                        print("</tr>");
                    } ?>
                    </tbody>
            </table>
        </form>

        <?= mysqli_free_result($result);
    }

    //Display form to edit checkin
    function edit_checkin($no){
        global $connection;

        $command = "select g.imie, g.nazwisko, p.id, o.datazameldowania, o.datawymeldowania, pg.sniadanie, pg.parking, pg.transport
                    from pokoj_goscie pg
                    join goscie g on pg.IdGoscia = g.Id
                    join pokoj p on pg.IdPokoju = p.Id
                    join okres_wynajmu o on pg.IdOkresuWynajmu = o.Id
                    where pg.Id = $no;";
        $row = mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);

        $room = mysqli_fetch_row($row);
        $_SESSION["checkinName"] = $room[0];
        $_SESSION["checkinSurname"] = $room[1];
        $_SESSION["checkinRoomId"] = $room[2];
        $_SESSION["checkinStartDate"] = $room[3];
        $_SESSION["checkinEndDate"] = $room[4];
        $_SESSION["editBreakfast"] =  $room[5];
        $_SESSION["editParking"] =  $room[6];
        $_SESSION["editTransport"] =  $room[7];

        //Get array of room to add it to select
        $roomArray = [];
        $request = "select id, nazwa from pokoj;";
        $result = mysqli_query($connection, $request);
        while($row = mysqli_fetch_array($result)){
            $roomArray += [$row[0] => $row[1]];
        }
    
        $_SESSION["roomList"] = $roomArray;

        $_SESSION["modalform"] = 'editCheckinData';
    }

    //Edit checkin in database
    function save_checkin($no){
        global $connection;

        $checkinRoomIdSelect = $_POST['checkinRoomIdSelect'];
        $checkinStartDate = $_POST['checkinStartDate'];
        $checkinEndDate = $_POST['checkinEndDate'];
        isset($_POST['editBreakfast']) ? $breakfast = 'T' : $breakfast = 'N';
        isset($_POST['editParking']) ? $parking = 'T' : $parking = 'N';
        isset($_POST['editTransport']) ? $transport = 'T' : $transport = 'N';

        $request = "insert into okres_wynajmu (datazameldowania, datawymeldowania) 
        SELECT '$checkinStartDate', '$checkinEndDate' 
        where not exists (select id from okres_wynajmu where datazameldowania = '$checkinStartDate' and datawymeldowania = '$checkinEndDate') limit 1;";
        mysqli_query($connection, $request);

        $command = "update pokoj_goscie set idpokoju='$checkinRoomIdSelect',
                    idokresuwynajmu=(select id from okres_wynajmu where datazameldowania = '$checkinStartDate' and datawymeldowania = '$checkinEndDate'),
                    sniadanie = '$breakfast',
                    parking = '$parking',
                    transport = '$transport'
                    where id=$no;";
        
        mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
        header("Location: checkin.php");
    }

    //Delete checkin from database
    function cancel_checkin($no){
        global $connection;
	
	    $command = "delete from pokoj_goscie where id=$no;";		
	    mysqli_query($connection, $command) or exit("Błąd w zapytaniu: $command");
    }

    open_connection();
    $option = '';
    if(isset($_POST['button'])) {	
        $no = key($_POST['button']);
        $option = $_POST['button'][$no];
        $_SESSION["no"] = $no;
    }

    switch($option) {
        case 'Edytuj dane': edit_checkin($no); break;
        case 'Odwołaj wizytę': cancel_checkin($no); break;
        case 'Zapisz': save_checkin($no); break;
    }

    show_checkins();
    close_connection();
?>


<!-- Edit Checkin Data Modal -->
<div class="modal fade" id="editCheckinData" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Wpisz dane gościa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form method=POST action=''>
                    <div class="modal-body">
                    <div class="form-floating mb-3">
                            <?php
                                generatedSelect($_SESSION["roomList"], "checkinRoomIdSelect", $_SESSION["checkinRoomId"]);
                            ?>
                            <label for="roomIdSelect">Pokój</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" required class="form-control" id="checkinStartDateInput" name="checkinStartDate" placeholder="Data od..." value="<?=$_SESSION["checkinStartDate"]?>">
                            <label for="checkinStartDateInput">Zameldowanie od</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="date" required class="form-control" id="checkinEndDateInput" name="checkinEndDate" placeholder="Data do..." value="<?=$_SESSION["checkinEndDate"]?>">
                            <label for="checkinEndDateInput">Zameldowanie do</label>
                        </div>
                        <div class="margin-5px mb-3">
                            <h5>Dodatkowe udogodnienia:</h5>
                        </div>
                        <div class="margin-5px mb-3">
                            <input type="checkbox" class="mb-3" id="breakfastInput" name="editBreakfast" <?php $_SESSION["editBreakfast"] == 'T' ? print('checked') : '' ?>/>
                            <label for="breakfastInput">Wliczone śniadanie</label>
                            <input type="checkbox" class="mb-3 margin-5px" id="parkingInput" name="editParking" <?php $_SESSION["editParking"] == 'T' ? print('checked') : '' ?>/>
                            <label for="parkingInput">Płatny parking</label>
                        </div>
                        <div class="margin-5px mb-3">
                            <input type="checkbox" class="mb-3" id="transportInput" name="editTransport" <?php $_SESSION["editTransport"] == 'T' ? print('checked') : '' ?>/>
                            <label for="transportInput">Transport z lotniska lub dworca</label>
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

<script>
    //Choose modal to display
    $(document).ready(function () {
        //Function get value with information about modal name
        let value = '<?php echo $_SESSION['modalform']?>';
        if (value == 'editCheckinData') {
            $('#editCheckinData').modal('show');
			
			// date validation
			var start = document.getElementById('checkinStartDateInput');
			var end = document.getElementById('checkinEndDateInput');
			console.log(start);
			console.log(end);
			
			start.addEventListener('change', function() {
				if (start.value)
					end.min = start.value;
			}, false);
			end.addEventListener('change', function() {
				if (end.value)
					start.max = end.value;
			}, false);
        }
    });

    //Show serched checkin and hide other with use style
    function searchFuntion(){
        let input, filter, table, tr, td, i, txtValue;
        input = document.getElementById('checkinSearch');
        filter = input.value;
        table = document.getElementById('checkinTable');
        tr = table.getElementsByTagName('tr');

        for (i = 0; i < tr.length; i++){
            td1 = tr[i].getElementsByTagName('td')[0];
            td2 = tr[i].getElementsByTagName('td')[1]
            if (td1 || td2){
                txtValue1 = td1.textContent.toLowerCase() || td1.innerHTML.toLowerCase();
                txtValue2 = td2.textContent.toLowerCase() || td2.innerHTML.toLowerCase();
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