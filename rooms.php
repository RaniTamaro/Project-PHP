<?php
    $title = "Pokoje";
    $page = "rooms";
    include_once('header.php');
    include('functions.php');
    session_start();
    session_destroy();

    if (empty($_SESSION)) {
        $_SESSION["modalform"] = '';
        $_SESSION["roomName"] = '';
        $_SESSION["bedNumber"] = '';
        $_SESSION["personPrice"] = '';
        $_SESSION["price"] = '';
    }

    function show_rooms()
    {
        global $connection;
    
        $request = "select * from pokoj";
        $result = mysqli_query($connection, $request);
        if (!$result)
            return;
    
        $headers = array("Nazwa", "Ilość łóżek", "Cena za osobę", "Cena za pokój");
        ?>
        <form method='POST'>
            <h3>Pokoje</h3>
            <div style="text-align:right">
                <input type="submit" class="btn btn-outline-dark" name='button[-1]' value="Dodaj nowy pokój" />
            </div>
        
            <input type="text" class="form-control search-input" id="roomSearch" onkeyup="searchFuntion()" placeholder="Wyszukaj pokój"/>
            <table class='table table-striped table-color' id="roomsTable">
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
    
    
    function edit_room($no = -1)
    {
        global $connection;
    
        if($no != -1) {
            $command = "select nazwa, lozka, cenaosoba, cenapokoj from pokoj where Id=$no;";
            $row = mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
                    
            $room = mysqli_fetch_row($row);
            $_SESSION["roomName"] = $room[0];
            $_SESSION["bedNumber"] = $room[1];
            $_SESSION["personPrice"] = $room[2];
            $_SESSION["price"] = $room[3];
        }
        else {
            $_SESSION["roomName"] = '';
            $_SESSION["bedNumber"] = '';
            $_SESSION["personPrice"] = '';
            $_SESSION["price"] = '';
        }
    
        $_SESSION["modalform"] = 'editRoom';
    }
    
    function save_room($no)
    {
        global $connection;
        $roomName = $_POST['roomName'];
        $bedNumber = $_POST['bedNumber'];
        $personPrice = $_POST['personPrice'];
        $price = $_POST['price'];
    
        if($no != -1)
            $command = "update pokoj set nazwa='$roomName', lozka='$bedNumber', cenaosoba='$personPrice', cenapokoj='$price' where id=$no;";
        else $command = "insert into pokoj values(null, '$roomName', '$bedNumber', '$personPrice' , '$price');";
        
        mysqli_query($connection, $command) or exit("Błąd w zapytaniu: ".$command);
        
        header("Location: rooms.php");
    }
    
    function delete_room($no)
    {
        global $connection;
        
        $command = "delete from room where id=$no;";		
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
        case 'Edytuj': edit_room($no); break;
        case 'Dodaj nowy pokój': edit_room(); break;
        case 'Zapisz': save_room($no); break;
        case 'Usuń': delete_room($no); break;
    }
    
    show_rooms();
    close_connection();
    ?>
    
    </div>
    
    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoom" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Dane gościa</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    <form method=POST action=''>
                        <div class="modal-body">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="nameRoomInput" name="roomName" placeholder="Nazwa..." value="<?=$_SESSION["roomName"]?>">
                                    <label for="nameRoomInput">Nazwa pokoju</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="number" min=1 max=10 class="form-control" id="bedNumberInput" name="bedNumber" placeholder="Ilość łóżek..." value="<?=$_SESSION["bedNumber"]?>">
                                    <label for="bedNumberInput">Ilość łóżek</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="number" min=1 class="form-control" id="personPriceInput" name="bedNumber" placeholder="Cena za osobę..." value="<?=$_SESSION["personPrice"]?>">
                                    <label for="personPriceInput">Cena za jedną osobę</label>
                                </div>
                                <div class="form-floating">
                                    <input type="number" min=1 class="form-control" id="priceInput" name="price" placeholder="Cena za pokój..." value="<?=$_SESSION["price"]?>">
                                    <label for="priceInput">Cena za pokój</label>
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
            if (value == 'editRoom') {
                $('#editRoom').modal('show');
            }
        });

        function searchFuntion(){
        let input, filter, table, tr, td, i, txtValue;
        input = document.getElementById('roomSearch');
        filter = input.value;
        table = document.getElementById('roomsTable');
        tr = table.getElementsByTagName('tr');

        for (i = 0; i < tr.length; i++){
            td = tr[i].getElementsByTagName('td')[0];
            if (td){
                txtValue = td.textContent || td.innerHTML;
                if (txtValue.indexOf(filter) > -1){
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