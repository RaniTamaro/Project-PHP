<?php
$title = "Goście";
$page = "guests";
include_once('header.php');
include('functions.php');
session_start();
session_destroy();

if (empty($_SESSION)) {
    $_SESSION["modalform"] = '';
    $_SESSION["name"] = '';
    $_SESSION["surname"] = '';
    $_SESSION["adult"] = 'T';
}

function show_guest()
{
    global $connection;

    $request = "select * from goscie";
    $result = mysqli_query($connection, $request);
    if (!$result)
        return;

    $headers = array("Imię", "Nazwisko", "Dorosły");
    ?>
    <form method='POST'>
        <h3>Goście</h3>
        <div style="text-align:right">
            <input type="submit" class="btn btn-outline-dark" name='button[-1]' value="Dodaj nowego gościa" />
        </div>

        <table class='table table-striped table-color'>
            <thead>
                <tr class="text-center">
                <?php
                foreach ($headers as $header)
                    echo "<th scope='col'>$header</th>";

                echo "<th scope='col'></th></tr></thead><tbody>";
                while ($row = mysqli_fetch_row($result)) {
                    echo "<tr class='text-center'>";
                    foreach ($row as $c => $cell)
                        if ($c != 0)
                            echo "<td>$cell</td>";
                    echo "<td><input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Edytuj'>
                            <input type='submit' class='btn btn-outline-dark' name='button[$row[0]]' value='Usuń'>
                        </td>";
                    print("</tr>");
                } ?>
                </tbody>
        </table>
    </form>

    <?= mysqli_free_result($result);
}


function edit_guest($no = -1)
{
    global $connection;

    if($no != -1) {
		$command = "select imie, nazwisko, dorosly from goscie where Id=$no;";
		$row = mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
                
        $guest = mysqli_fetch_row($row);
        $_SESSION["name"] = $guest[0];
        $_SESSION["surname"] = $guest[1];
        $_SESSION["adult"] = $guest[2];
	}
	else {
        $_SESSION["name"] = '';
        $_SESSION["surname"] = '';
        $_SESSION["adult"] = 'T';
	}

    $_SESSION["modalform"] = 'editGuest';
}

function save_guest($no)
{
    global $connection;
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    isset($_POST['adult']) ? $adult = 'T' : $adult = 'N';

    if($no != -1)
        $command = "update goscie set imie='$name', nazwisko='$surname', dorosly='$adult' where id=$no;";
    else $command = "insert into goscie values(null, '$name', '$surname', '$adult');";
    
    mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
    
    header("Location: guests.php");
}

function delete_guest($no)
{
    global $connection;
	
	$command = "delete from goscie where id=$no;";		
	mysqli_query($connection, $command) or exit("Błąd w zapytaniu: $command");
}

function checkin_guest($no)
{
    //TODO: Add logic or change place of this function.
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
    // case 'Zamelduj': zamelduj_goscia($nr); break;
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
                            <div class="margin-5px">
                                <input type="checkbox" class="mb-3" id="adultInput" name="adult" <?php $_SESSION["adult"] == 'T' ? print('checked') : '' ?>/>
                                <label for="adultInput">Osoba dorosła</label>
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
    $(document).ready(function () {
        let value = '<?php echo $_SESSION['modalform']?>';
        if (value == 'editGuest') {
            $('#editGuest').modal('show');
        }
    });
</script>

</body>

</html>