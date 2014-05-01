<?php
    ob_start(); // To hide header messages
    
    if(!isset($_GET['clear'])) require_once('header.php');
    require_once('log.php');
    require_once('config.php'); // Including database information
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged']) || !getAuth($_SESSION['user']['auth'], ADMIN)) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    
	if(isset($_GET['mode']) && $_GET['mode'] == 'clear') {
        $dbh = connect();
        $query = $dbh->prepare("DELETE FROM Logs;");
        
        if(!$query->execute()) {
            die('<h1>Error</h1><br /><h3>Unable to update log database.</h3>');
        }
        redirect('viewlog.php', 'Log cleared successfully.');
        auditlog('Cleared audit log.');
    } else {
        $dbh = connect();
        $query = $dbh->prepare("SELECT * FROM Logs ORDER BY date DESC");
        
        if($query->execute()) {
            $logRows = $query->fetchAll();
            $logCount = $query->rowCount();
            
            for($i = 0; $i < $logCount; $i++) {
                $query = $dbh->prepare("SELECT forename, familyName FROM Users WHERE id = :id");
                
                if($query->execute(array(":id" => $logRows[$i]['idUsers']))) {
                    $row = $query->fetch();
                    $logRows[$i]['user'] = $row['forename'] . ' ' . $row['familyName'];
                } else {
                    die('<h1>Error</h1><br /><h3>Unable to get user information from database.</h3>');
                }
            }
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get log information from database.</h3>');
        }
        auditlog('Viewed audit log.');
    	?>
        <div><h1>Audit log</h1></div><br /><br />
		<?PHP if(!isset($_GET['clear'])) { ?>
			<div style="height:400px;overflow:auto;">
        <?PHP } else { ?>
			<div>
		<?PHP } ?>
			<table style='width: 100%;' id='problemstable'>
		        <thead>
					<td width='20%' height='30px'><h3>Date/Time</h3></td>
					<td width='20%'><h3>User</h3></td>
					<td><h3>Action</h3></td>
                </thead>
                <?PHP foreach($logRows as $row) {
                    ?>
				    <tr>
						<td height='30px'><?PHP echo date('d-m-Y H:i:s', strtotime($row['date'])); ?></td>
						<td><?PHP echo $row['user']; ?></td>
						<td><?PHP echo $row['action']; ?></td>
						
                    </tr>
				<?PHP } ?>
			</table><br /></div>
		<?PHP if(!isset($_GET['clear'])) { ?>
        	<div><input class="form-input-button" type='submit' value='Save Log and Clear' onclick="clearLog()"></div>
			<span id='clear' style='display:none;'>
				<form action='viewlog.php' method='get'>
					<input type='hidden' name='mode' value='clear'>
					<input class="form-input-button" type='submit' value='Clear'>
				</form>
			</span>
	<?PHP }
    }
    require_once('footer.php');
    ob_flush(); ?>