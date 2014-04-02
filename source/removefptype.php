<?php
    //get the query parameter from URL
    $fpt = $_GET["fpt"];
    
    require('config.php');
    require_once('log.php');
    $dbh = connect();
    $found = false;
    $ret = '';
    
    $query = $dbh->prepare("SELECT name FROM FoodParcelType WHERE id = :fpt");
    if($query->execute(array(":fpt" => $fpt))) {
        $row = $query->fetch();
        $name = $row['name'];
    } else {
        $ret = 'Unable to get food parcel type information from database.';
    }
    
    $query = $dbh->prepare("SELECT id FROM FoodParcel where idFPType = :fpt");
    
    if($query->execute(array(":fpt" => $fpt))) {
       // the program will nod delete the parceltype from the database only make it 'invisible' to mantain old types in the system
        $query = $dbh->prepare("UPDATE FoodParcelType SET edited = 1 WHERE id = :fpt");
        
        if($query->execute(array(":fpt" => $fpt))) {
            $ret = 'true';
            auditlog('Removed food parcel type. Name: ' . $name);
        } else {
            $ret = 'Unable to remove information from database.';
        }
    
    } else {
        $ret = 'Unable to get food parcel type information from database.';
    }
    
    //output the response
    echo $ret;
?>