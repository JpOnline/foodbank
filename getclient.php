<?php
    //get the query parameter from URL
    $q = strtolower($_GET["q"]);
    //get the type parameter from URL
    $t = $_GET["t"];
    
    require('config.php');
    require_once('log.php');
    
    $dbh = connect();
    $found = false;
    
    $ret = '<table style=\'width: 100%;\'>';
    $ret .= '<thead>';
    $ret .= '<td><h3>Family Name</h3></td>';
    $ret .= '<td><h3>Forename Name</h3></td>';
    $ret .= '<td><h3>Postcode</h3></td>';
    $ret .= '<td>&nbsp;</td>';
    $ret .= '</thead>';
    
    $query = $dbh->prepare("SELECT id, familyName, forename, postcode FROM Client");
    
    if($query->execute()) {
        if($query->rowCount() > 0) {
            $rows = $query->fetchAll();
        }
        auditlog('Searched for clients with family name or postcode starting with: ' . $q);
    } else {
        die('Unable to get client information from database.');
    }
    
    if(isset($rows)) {
        foreach($rows as $row) {
            if(($t == 'lastname' && substr(strtolower($row['familyName']), 0, strlen($q)) === $q) || ($t == 'postcode' && substr(strtolower(str_replace(' ', '', $row['postcode'])), 0, strlen($q)) === $q)) {
                $found = true;
                $ret1 = '<tr>';
                $ret1 .= '<td>'.$row['familyName'].'</td>';
                $ret1 .= '<td>'.$row['forename'].'</td>';
                $ret1 .= '<td>'.$row['postcode'].'</td>';
                
                $ret1 .= '<td><form action=\'client.php\' method=\'get\'>';
                $ret1 .= '<input type=\'hidden\' name=\'mode\' value=\'view\'>';
                $ret1 .= '<input type=\'hidden\' name=\'id\' value=\''.$row['id'].'\'>';
                $ret1 .= '<div><input class=\'form-input-button\' type=\'submit\' value=\'View\'></div></form></td>';
                $ret1 .= '</tr>';
                $ret .= $ret1;
            }
        }
    }
    
    // Set output to "no suggestion" if no hint were found
    // or to the correct values
    if (!$found) {
        $response = "No client found.";
    } else {
        $ret .= '</table>';
        $response = $ret;
    }
    
    //output the response
    echo $response;
?>