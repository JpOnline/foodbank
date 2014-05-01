<?php
    //get the q parameter from URL
    $fptid = $_GET["id"];
    
    require('config.php');
    $dbh = connect();
    $response = '';
    
    $ret = '<table style=\'width: 100%;\'>';
    $ret .= '<thead>';
    $ret .= '<td><h1>Items</h1></td><td><h1>Quantity</h1></td>';
    $ret .= '</thead>';
    
    $query = $dbh->prepare("SELECT startingLetter FROM FoodParcelType WHERE id = " . $fptid);
    
    if($query->execute()) {
        $row = $query->fetch();
        $startingLetter = $row['startingLetter'];
    } else {
        $response = 'Unable to get food parcel type information from database.';
    }
    
    $query = $dbh->prepare("SELECT FI.Name, FPT.tagColour, FPT.startingLetter, FPTC.quantity FROM FoodItem FI, FoodParcelType FPT, FPType_Contains FPTC WHERE FPTC.idFoodParcelType = " . $fptid . " AND FPTC.idFoodParcelType = FPT.id AND FPTC.idFoodItem = FI.id");
    
    if($query->execute()) {
        if($query->rowCount() > 0) {
	        $found = true;
    	    $rows = $query->fetchAll();
            
            $query = $dbh->prepare("SELECT referenceNumber FROM FoodParcel WHERE referenceNumber LIKE '" . $startingLetter . "%' ORDER BY referenceNumber DESC LIMIT 0,1");
            if($query->execute()) {
                if($query->rowCount() > 0) {
                    $found = true;
                    $row = $query->fetch();
                    
                    $ref = substr($row['referenceNumber'], strlen($rows[0]['startingLetter']));
                    if(intval($ref) < 9) {
                        $refnum = $rows[0]['startingLetter'] . "000" . (intval($ref)+1);
                    } else if(intval($ref) < 99) {
                        $refnum = $rows[0]['startingLetter'] . "00" . (intval($ref)+1);;
                    } else if(intval($ref) < 999) {
                        $refnum = $rows[0]['startingLetter'] . "0" . (intval($ref)+1);;
                    } else {
                        $refnum = $rows[0]['startingLetter'] . (intval($ref)+1);;
                    }
                } else {
                    $refnum = $rows[0]['startingLetter'] . '0001';
                }
                
                for($i = 0; $i < count($rows); $i++) {
                    $ret .= '<tr><td><h3>' . $rows[$i]['Name'] . '</h3></td><td><h3>' . $rows[$i]['quantity'] . '</h3></td></tr>';
                }
                $ret .= '</table>[BRK]';
                $ret .= $refnum;
                $ret .= '[BRK]';
                $ret .= $rows[0]['tagColour'];
            } else {
                $response = 'Unable to get reference number from database.';
            }
        }
    } else {
        $response = 'Unable to get food parcel type information from database.';
    }
    
    // Set output to "no suggestion" if no hint were found
    // or to the correct values
    if ($response == '')
    {
        $response = $ret;
    }
    
    //output the response
    echo $response;
    ?>