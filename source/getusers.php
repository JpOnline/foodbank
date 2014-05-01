<?php
    //get the query parameter from URL
    $q = strtolower($_GET["query"]);
    //get the type parameter from URL
    $t = $_GET["type"];
    
    require('config.php');
    require_once('log.php');
    
    $dbh = connect();
    $found = false;
    
    $ret = '<table style=\'text-align:center; width: 100%;\'>';
    $ret .= '<tr>';
    $ret .= '<td><h3>Id</h4></td>';
    $ret .= '<td><h3>Name</h3></td>';
    $ret .= '<td><h3>Login</h3></td>';
    $ret .= '<td><h3>Email</h3></td>';
    $ret .= '<td><h3>Enabled</h3></td>';
    $ret .= '<td>&nbsp;</td>';
    $ret .= '</tr>';
    
    $query = $dbh->prepare("SELECT id, forename, familyName, login, email, enabled FROM Users");
    
    if($query->execute()) {
        if($query->rowCount() > 0) {
            $rows = $query->fetchAll();
        }
        auditlog('Searched for users with surname/login/email starting with: ' . $q);
    } else {
        die('Unable to get client information from database.');
    }
    
    if(isset($rows)) {
        foreach($rows as $row) {
            if(($t == 'email' && substr(strtolower($row['email']), 0, strlen($q)) === $q) ||
               ($t == 'lastname' && substr(strtolower($row['familyName']), 0, strlen($q)) === $q) ||
               ($t == 'login' && substr(strtolower($row['login']), 0, strlen($q)) === $q)) {
                $found = true;
                $ret1 = '<tr>';
                $ret1 .= '<td>'.$row['id'].'</td>';
                $ret1 .= '<td>'.$row['forename'] . ' ' . $row['familyName'] .'</td>';
                $ret1 .= '<td>'.$row['login'].'</td>';
                $ret1 .= '<td>'.$row['email'].'</td>';
                if($row['enabled'])
	                $ret1 .= '<td>True</td>';
                else
                    $ret1 .= '<td>False</td>';
                
                $ret1 .= '<td><form action=\'users.php\' method=\'get\'>';
                $ret1 .= '<input type=\'hidden\' name=\'mode\' value=\'edit\'>';
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
        $response = "No user found.";
    } else {
        $ret .= '</table>';
        $response = $ret;
    }
    
    //output the response
    echo $response;
?>