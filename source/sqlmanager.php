<?php
ob_start(); // To hide header messages

require_once('header.php'); //To load the default visual.
require_once('log.php'); //To enable store actions in the audit log.
require_once('config.php'); // Including database information

if(!isset($_SESSION)) { // Starting session
    session_start();
}

if(!isset($_SESSION['logged']) || !getAuth($_SESSION['user']['auth'], ADMIN)) {
    redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
    die();
}

$dbh = connect();
$query = $dbh->prepare("SELECT * FROM agency");
if($query->execute()){
    die('<h1>Error</h1><br /><h3>Unable to get tables information from database.</h3>');
}
else{ 
    $tableRows = $query->fetchAll();
    $tableRowsCount = $query->rowCount();
    ?>
    <div><h3>Tables:
	<select name='table' id='table' onchange="showTable()">
	    <?php for($i=0; $i < $tableRowsCount; $i++) { ?>
		<option value='<?php echo $tableRows[$i]['TABLE_NAME']; ?> '> <?php echo $tableRows[$i]['TABLE_NAME']; ?></option>
	    <?php } ?>
    } 
<?php
$query = $dbh->prepare("SELECT * FROM Agency");

if($query->execute()) {
    $logRows = $query->fetchAll();
    $logCount = $query->rowCount();
    
    for($i = 0; $i < $logCount; $i++) {
	$query = $dbh->prepare("SELECT organization, referralCenreReference FROM Agency");
	
//                if($query->execute(array(":id" => $logRows[$i]['idUsers']))) {
//                    $row = $query->fetch();
//                    $logRows[$i]['user'] = $row['forename'] . ' ' . $row['familyName'];
//                } else {
//                    die('<h1>Error</h1><br /><h3>Unable to get user information from database.</h3>');
//                }
    }
} else {
    die('<h1>Error</h1><br /><h3>Unable to get tables information from database.</h3>');
}
require_once('footer.php');
ob_flush(); ?>
