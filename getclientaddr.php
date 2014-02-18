<?php
    //get the q parameter from URL
    $id = $_GET["id"];
    
	require('config.php');
    
    $dbh = connect();
    $response = '';

    $query = $dbh->prepare("SELECT address1, address2, postcode, town FROM Client WHERE id = :id");

    if($query->execute(array(":id" => $id))) {
        $client = $query->fetch();
        
        $br = '&#13;&#10;';
        $response = $client['address1'].$br.$client['address2'].$br.$client['town'].$br.$client['postcode'];
    } else {
        $response = 'Unable to get location information from database.';
    }
    
    //output the response
    echo $response;
?>