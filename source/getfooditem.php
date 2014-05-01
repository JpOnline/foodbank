<?php
    //get the cat parameter from URL
    $catid = $_GET["cat"];
    //get the loc parameter from URL
    $locid = $_GET["loc"];

    require('config.php');
    $dbh = connect();
    $found = false;
    
    if($locid == '') {
	    $query = $dbh->prepare("SELECT s.quantity, fi.Name, cw.centralWarehouseName FROM Store s, FoodItem fi, Warehouse cw WHERE s.idFoodItem = fi.id AND s.idWarehouse = cw.id AND fi.id = :fiid");
        $query->bindValue(":fiid", $catid);
    } else if($catid == '') {
	    $query = $dbh->prepare("SELECT s.quantity, fi.Name, cw.centralWarehouseName FROM Store s, FoodItem fi, Warehouse cw WHERE s.idFoodItem = fi.id AND s.idWarehouse = cw.id AND cw.id = :cwid");
        $query->bindValue(":cwid", $locid);
    } else {
	    $query = $dbh->prepare("SELECT s.quantity, fi.Name, cw.centralWarehouseName FROM Store s, FoodItem fi, Warehouse cw WHERE s.idFoodItem = fi.id AND s.idWarehouse = cw.id AND fi.id = :fiid AND cw.id = :cwid");
        $query->bindValue(":fiid", $catid);
        $query->bindValue(":cwid", $locid);
    }
    
    if($query->execute()) {
        if($query->rowCount() > 0) {
            $found = true;
            $rows = $query->fetchAll();
            
            $hint = '<table style=\'width: 100%; text-align:center;\'>';
            $hint .= '<thead>';
            $hint .= '<td><h3>Name</h3></td>';
            $hint .= '<td><h3>Location</h3></td>';
            $hint .= '<td><h3>Quantity</h3></td>';
            $hint .= '</thead>';
            
            $total = 0;
            
            foreach($rows as $row)
            {
                $hint1 = '<tr>';
                $hint1 .= '<td>'.$row['Name'].'</td>';
                $hint1 .= '<td>'.$row['centralWarehouseName'].'</td>';
                $hint1 .= '<td>'.$row['quantity'].'</td>';
                $hint1 .= '</tr>';
                $hint .= $hint1;
                $total += $row['quantity'];
            }
            $hint .= '<tr><td colspan=\'3\'>&nbsp;</td></tr>';
            $hint .= '<tr><td colspan=\'2\' style=\'text-align:right;\'><h3>Total</h3></td><td><h3>'.$total.'</h3></td></tr>';
        }
    } else {
        die('Unable to get food item information from database.');
    }
    
    // Set output to "no suggestion" if no hint were found
    // or to the correct values
    if (!$found)
    {
        $response = "No item found.";
    }
    else
    {
        $hint .= '</table>';
        $response=$hint;
    }
    
    //output the response
    echo $response;
?>
