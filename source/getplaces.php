<?php
    //get the q parameter from URL
    $t = $_GET["t"];
    
	require('config.php');
    $dbh = connect();
    $ret = '<select name=\'location\' id=\'location\'>';
    $response = '';
    
    if($t == 'cw') {
		$query = $dbh->prepare("SELECT id, centralWarehouseName as name FROM Warehouse WHERE deleted = 0 ORDER BY name");
    } else if($t == 'dp') {
		$query = $dbh->prepare("SELECT id, distributionPointName as name FROM DistributionPoint WHERE deleted = 0 ORDER BY name");
    } else if($t == 'agency') {
        $query = $dbh->prepare("SELECT id, organisation as name FROM Agency WHERE deleted = 0 ORDER BY name");
    }

    if($query->execute()) {
        foreach($query->fetchAll() as $row) {
            $ret .= '<option value=\'' . $row['id'] . '\'>' . $row['name'] .'</option>';
        }
        $ret .= '</select>';
    } else {
        $response = 'Unable to get location information from database.';
    }
    
    if($response == '') {
        $response = $ret;
    }
    
    //output the response
    echo $response;
?>