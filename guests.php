<?php
    $title = "Goście";
    $page = "guests";
    include_once('header.php');
    include('functions.php');

    function show_guest(){
        global $connection;
    
        $request = "select * from goscie";	
        $result = mysqli_query($connection, $request);
        if(!$result) return;
    
        $headers = array("Imię", "Nazwisko", "Dorosły");
        echo "<form method='POST'>";
        echo "<h3>Goście</h3>";
        echo "<table class='table table-striped table-color'>
                <thead>
                    <tr align='center'>";
        foreach($headers as $header) echo "<th scope='col'>$header</th>";

        echo "<th scope='col'></th></tr></thead><tbody>";

        while($row = mysqli_fetch_row($result)){		
                echo "<tr align='center'>";
                foreach($row as $c=>$cell)
                    if($c != 0) echo "<td>$cell</td>";
                echo "<td><input type='submit' class='btn btn-outline-dark' name='przycisk[$row[0]]' value='Edytuj'>
                                        <input type='submit' class='btn btn-outline-dark' name='przycisk[$row[0]]' value='Usuń'></td>";	
                print("</tr>");		
        }
        echo "</tbody></table>";
        echo "</form>";
        mysqli_free_result($result);
    }


    function edit_guest($nr = -1){

    }

    function save_guest($nr){
        
    }

    function delete_guest($nr){

    }

    function checkin_guest($nr){

    }
?>


<?php
    open_connection();

    // switch($polecenie) {
    //     case 'Edytuj': edytuj_goscia($nr); break;
    //     case 'Dodaj nowego': edytuj_goscia(); break;
    //     case 'Zapisz': zapisz_goscia($nr); break;
    //     case 'Usuń': usun_goscia($nr); break;
    //     case 'Zamelduj': zamelduj_goscia($nr); break;
    // }

    show_guest();
    close_connection();
?>

    </div>
    </body>
</html>