<?php
    //get the q parameter from URL
    if(isset($_GET["id"])) $fptid = $_GET["id"];
    
    require('config.php');
    $dbh = connect();
    $found = false;
    
    $query = $dbh->prepare("SELECT FPT.id, FPT.name, FPT.tagColour, FPT.startingLetter, FPTC.idFoodParcelType, FPTC.idFoodItem, FPTC.quantity FROM FoodParcelType FPT, FPType_Contains FPTC WHERE FPTC.idFoodParcelType = " . $fptid . " AND FPTC.idFoodParcelType = FPT.id");
    
    if($query->execute()) {
        if($query->rowCount() > 0) {
	        $found = true;
    	    $rows = $query->fetchAll();
        }
    } else {
        die('Unable to get food parcel type information from database.');
    }
    
    $ret = '';
    if(isset($rows)) {
        for($i = 0; $i < count($rows); $i++) {
            $ret .= $rows[$i]['idFoodItem'] . '[BRK3]' . $rows[$i]['quantity'];
            if($i < count($rows)-1)
                 $ret .= '[BRK2]';
        }
        $ret .= '[BRK]';
        $ret .= $rows[0]['name'];
        $ret .= '[BRK]';
        $ret .= $rows[0]['startingLetter'];
        $ret .= '[BRK]';
        $ret .= $rows[0]['tagColour'];
    }
    
    // Set output to "no suggestion" if no hint were found
    // or to the correct values
    if (!$found)
    {
        $response = "Type not found.";
    }
    else
    {
        if(isset($_GET["id"]))
	        $response = $ret;
        else {
            $ret = explode('[BRK]', $ret);
            $response = $ret[0];
        }
    }
    
    //output the response
    echo $response;
    ?>