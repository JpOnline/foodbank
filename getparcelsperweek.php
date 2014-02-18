<?php
    //get the q parameter from URL
    $t = $_GET["t"];
    
	require('config.php');
    $dbh = connect();
    $ret = '';
    $response = '';
    
	$date = explode(',', $_GET['t']);
	$datei = $date[0];
	$datef = $date[1];
	
	$query = $dbh->prepare("SELECT * FROM FoodParcel WHERE wasGiven = false AND packingDate >= :di AND packingDate <= :df ORDER BY id DESC");
	
	if($query->execute(array(":di" => $datei, ":df" => $datef))) {
		$packedCount = $query->rowCount();
    } else {
		$response = 'Unable to get packed parcels from database.';
    }
	$query = $dbh->prepare("SELECT * FROM FoodParcel WHERE wasGiven = true AND packingDate >= :di AND packingDate <= :df ORDER BY id DESC");
	
	if($query->execute(array(":di" => $datei, ":df" => $datef))) {
	    $givenCount = $query->rowCount();
	} else {
	    die('Unable to get given parcels from database.');
	}
	
    $ret = '<h4>Parcels packed: ' . $packedCount . '<br />Parcels Exchanged: ' . $givenCount . '</h4>';
    
    if($response == '') {
        $response = $ret;
    }
    
    //output the response
    echo $response;
?>