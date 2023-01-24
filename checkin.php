<?php
    $title = "Zameldowania gości";
    $page = "checkin";
    include_once('header.php');
    include('functions.php');

    session_start();
	check_if_user_logged($_SESSION);

	// reset modal form flag	
	$_SESSION["modalform"] = '';
	

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
            <h3>Zameldowania</h3>
            <!-- <div style="text-align:right">
                <input type="submit" class="btn btn-outline-dark" name='button[-1]' value="Dodaj nowe zameldowanie" />
            </div> -->

            <table class='table table-striped table-color'>
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

    function get_guests(){

    }

    $option = '';
    if(isset($_POST['button'])) {	
        $no = key($_POST['button']);
        $option = $_POST['button'][$no];
        $_SESSION["no"] = $no;
    }

    switch($option) {
        case 'Dodaj nowe zameldowanie': $_SESSION["modalform"] = 'numberGuestModal'; break;
        case 'Wybierz gości': $_SESSION["guestNumber"] = $_POST['guestNumber']; $_SESSION["modalform"] = 'addGuests'; break;
        case 'Zapisz': save_guest($no); break;
        case 'Usuń': delete_guest($no); break;
        // case 'Zamelduj': zamelduj_goscia($nr); break;
    }


    open_connection();
    show_checkins();
    close_connection();
?>


<!-- Guest Number Modal -->
<div class="modal fade" id="numberGuestModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Wpisz dane gościa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form method=POST action=''>
                    <div class="modal-body">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="guestNumber" name="guestNumber" placeholder="Ilość gości...">
                            <label for="guestNumber">Ilość gości</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="Anuluj"/>
                        <input type="submit" name="button[<?=$no?>]" class="btn btn-primary" value="Wybierz gości"/>
                    </div>
                </form>
        </div>
    </div>
</div>

<!-- Add Guest Data Modal -->
<div class="modal fade" id="addGuests" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Wpisz dane gościa</h1>
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

<!-- Add Date Modal -->
<div class="modal fade" id="editGuest" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Wpisz dane gościa</h1>
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

<!-- Choose Room Modal -->
<div class="modal fade" id="editGuest" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Wpisz dane gościa</h1>
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
        if (value == 'numberGuestModal') {
            $('#numberGuestModal').modal('show');
        }
        else if (value == 'addGuests'){
            $('#addGuests').modal('show');
        }
    });
</script>

    </div>
    </body>
</html>