<?PHP
    
    $user = $_POST['user'];
    $problem = $_POST['problem'];
    $date = date('Y-m-d H:i:s');
    $response = '';
    
    require('config.php');
    require_once('log.php');
    $dbh = connect();
    
    $query = $dbh->prepare("INSERT INTO ReportedProblems (date, problem, idUsers) VALUES (:d, :p, :idu)");

    if($query->execute(array(":d" => $date, ":p" => $problem, ":idu" => $user))) {
        $response = date('d-m-Y H:i:s', strtotime($date));
        auditlog('Reported a new problem.');
    }
    
    echo $response;
?>