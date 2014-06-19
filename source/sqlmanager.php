<?php
ob_start(); // To hide header messages

require_once('header.php'); //To load the default visual.
require_once('log.php'); //To enable store actions in the audit log.
require_once('config.php'); // Including database information

if(!isset($_SESSION)) { // Starting session
    session_start();
}

if(!isset($_SESSION['logged']) || !getAuth($_SESSION['user']['auth'], ADMIN)) {
    redirect('sql_manager_login.php', '<h1>Wait while you are being redirected.</h1>');
    die();
}

?>
<h3>Extreme dangerous area, don't execute any command if you don't know what are you doing.</h3>
<form action="sqlmanager.php" method='post'>
    <textarea cols='60' rows='6' name='sqlquery' placeholder='SQL query'><?php echo $_POST['sqlquery']; ?></textarea>  
    <input type='submit' class='form-input-button' value='execute' name='submit'</input>
</form>
<?php

if(isset($_POST['submit'])){
    $dbh = connect();
    $query = $dbh->prepare($_POST['sqlquery']);
    try{
	$query->execute();
	$error = $query->errorInfo(); //showing query error later
	auditlog("SQL query executed: ".$_POST['sqlquery']." - ".$error[2]);
	$tableRows = $query->fetchAll();
	echo '<table>';
	$count = 0; //workaround to not count the columns twice
	echo '<tr>';
	foreach($tableRows[0] as $columnNames=>$cel){
	    if($count%2==0)
		echo "<th style=\"border:1px solid gray\">$columnNames</th>";
	    $count++;
	}
	echo "</tr>";
	foreach($tableRows as $row){
	    echo "<tr>";
	    $count=0;
	    foreach($row as $cel){
		if($count%2==0)
		    echo "<td style=\"border:1px solid gray\">$cel</td>";
		$count++;
	    }
	    echo "</tr>";
	}
	echo '</table>';
	echo "<h4><br />$error[2]</h4>";
    }
    catch(Exception $e){
	die('Unable to execute the query');
    }

}

require_once('footer.php');
ob_flush(); ?>
