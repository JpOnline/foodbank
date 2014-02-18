<?php
    function auditlog($action) {
        if(!isset($_SESSION)) { // Starting session
            session_start();
        }
        
        if(!isset($_SESSION['logged'])) {
            redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
            die();
        }
        require_once('config.php');
        
        $dbh = connect();
        $query = $dbh->prepare("INSERT INTO Logs (idUsers, date, action) VALUES (:idu, :d, :a)");
        
        if(!$query->execute(array(":idu" => $_SESSION['user']['id'], ":d" => date('Y-m-d H:i:s'), ":a" => $action))) {
            die('<h1>Error</h1><br /><h3>Unable to update log table.</h3>');
        }
    }
?>