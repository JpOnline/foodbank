<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    require_once('config.php');
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    if(!isset($_SESSION['logged'])) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
	$dbh = connect();
    
    $query = $dbh->prepare("SELECT problem, date, idUsers FROM ReportedProblems ORDER BY date DESC");
    
    if($query->execute()) {
        $rpRows = $query->fetchAll();
        $rpCount = $query->rowCount();
        
        for($i = 0; $i < count($rpRows); $i++) {
            $query = $dbh->prepare("SELECT familyName, forename FROM Users WHERE id = :id");
            if($query->execute(array(":id" => $rpRows[$i]['idUsers']))) {
                $rowUser = $query->fetch();
                $rpRows[$i]['user'] = $rowUser['forename'] . ' ' . $rowUser['familyName'];
                
            } else {
                die('<h1>Error</h1><br /><h3>Unable to get client information from database.</h3>');
            }
        }
    } else {
        die('<h1>Error</h1><br /><h3>Unable to get reported problems information from database.</h3>');
    }
    auditlog('Viewed reported problems.');
    ?>
        <div><h1>Report Problem</h1></div><br /><br />
		<form><input type="hidden" id="ajaxid" value="client"/>
		<div><table style="width:100%;">
			<tr>
				<td><h1>Problem</h1></td>
            </tr>
            <tr>
				<td><textarea type="text" rows='10' cols='60' id="problem"/></textarea></td>
			</tr>
			<tr>
                <td>&nbsp;<input type='hidden' name='user' id='iduser' value='<?PHP echo $_SESSION['user']['id']; ?>'>
						<input type='hidden' name='user' id='user' value='<?PHP echo $_SESSION['user']['forename'] . ' ' . $_SESSION['user']['familyName']; ?>'>
            		<input class="form-input-button" type='submit' id='submit' value='Submit' onClick='newReportedProblem()'></td>
			</tr>
		</table><br /><br /><br /><br />
		</div>
		<div><hr></div>
		<div><br /><br /><br /><br /><h1>Reported Problems</h1></div><br /><br />

		<div style="height:400px;overflow:auto;">
			<table style='width: 100%;' id='problemstable'>
		        <thead>
					<td height='30px'><h3>Date/Time</h3></td>
					<td><h3>User</h3></td>
					<td><h3>Problem</h3></td>
                </thead>
                <?PHP foreach($rpRows as $row) { ?>
				    <tr>
						<td height='30px'><?PHP echo date('d-m-Y H:i:s', strtotime($row['date'])); ?></td>
						<td><?PHP echo $row['user']; ?></td>
						<td><?PHP echo $row['problem']; ?></td>
						
                    </tr>
				<?PHP } ?>
			</table>
		</div>
    <?PHP
    require_once('footer.php');
    ob_flush(); ?>