<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], AGSTAFF) && !getAuth($_SESSION['user']['auth'], DPSTAFF) && !getAuth($_SESSION['user']['auth'], ADMIN))) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    require_once('config.php'); // Including database information
    
    if(getAuth($_SESSION['user']['auth'], ADMIN)) {
        $readonly = '';
    } else {
        $readonly = 'readonly = \'readonly\'';
    }

    if(isset($_GET['mode']) && ($_GET['mode'] == 'addag' || $_GET['mode'] == 'viewag')) {
        $dbh = connect();
        
        if($_GET['mode'] == 'viewag' && isset($_GET['id'])) {
            $editing = true;
            $id = $_GET['id'];
            
            $query = $dbh->prepare("SELECT * FROM Agency WHERE id = :id");
            
            if($query->execute(array(":id" => $id))) {
				if($query->rowCount() > 0) {
                    $row = $query->fetch();
                } else {
                	die('Invalid ID.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
                }
            } else {
                die('Unable to get agency info from database.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
            }
            auditlog('Viewed agency information. Name: ' . $row['organisation']);
        } else {
            if(!getAuth($_SESSION['user']['auth'], ADMIN)) {
                redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
                die();
            }
            $editing = false;
        }
        ?>
        <div><h1>Locations</h1></div><br />
        <div><h3>Agency</h3></div><br />
        <form action='locations.php' method='POST' onsubmit="return validateForm()">
		<input type='hidden' name='mode' value='updateag'>
		<?PHP if($editing) { ?>
			<input type='hidden' name='editing' value='1'>
			<input type='hidden' name='id' value='<?PHP echo $id; ?>'>
		<?PHP } else { ?>
			<input type='hidden' name='adding' value='1'>
		<?PHP } ?>
        <div><table style="width: 100%">
        	<tr>
        		<td><h3>Organisation *</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['organisation']; ?>' name='organisation' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
        	</tr>
        	<tr>
                <td><h3>Referral centre reference *</h3></td>
                <td><input type='text' value='<?PHP if($editing) echo $row['referralCentreReference']; ?>' name='rcr' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
            </tr>
            <tr>
                <td><h3>Area of Assistance *</h3></td>
                <td><input type='text' value='<?PHP if($editing) echo $row['areaOfAssistance']; ?>' name='areaOfAssistance' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
            </tr>
            <tr>
                <td><h3>Web Address</h3></td>
                <td><input type='text' value='<?PHP if($editing) echo $row['webAddress']; ?>' name='webAddress' size='30' maxlength='50' <?PHP echo $readonly; ?>></td>
            </tr>
        	<tr>
        		<td><h3>Home telephone number</h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo $row['homeTelephone']; ?>' name='hometelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
        	</tr>
			<tr>
                <td><h3>Mobile telephone number</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['mobileTelephone']; ?>' name='mobiletelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
			</tr>
        	<tr>
        		<td><h3>Address 1 *</h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo $row['address1']; ?>' name='address1' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
            </tr>
        	<tr>
        		<td><h3>Address 2</h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo $row['address2']; ?>' name='address2' id='opt' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
        	</tr>
        	<tr>
        		<td><h3>Town *	</h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo $row['town']; ?>' name='town' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
        	</tr>
			<tr>
				<td><h3>Postcode *</h3></td>
                <td><input type='text' value='<?PHP if($editing) echo $row['postcode']; ?>' name='postcode' size='30' maxlength='10' autocapitalize='characters' <?PHP echo $readonly; ?>></td>
			</tr>
        </table></div>
        <div>
        	<?PHP if($readonly == '') { ?>
        		<input class="form-input-button" type='submit' value='Submit'>             
            <?PHP } ?>
		</form>
        <?PHP if($readonly == '') { ?>
            <?PHP if($editing) { ?>
                <form action='locations.php' method='post' onsubmit="return validateForm()" >
                    <input type='hidden' name='mode' value='deleteag'>
                    <input type='hidden' name='deleting' value='1'>
                    <input type='hidden' name='id' value='<?PHP echo $id; ?>'>
                    <input class="form-input-button" type='submit' id='delete' value='delete'>
                </form>
            <?PHP } ?>
        <?PHP } ?>
        <form><input class="form-input-button" type='button' value='Back' onclick="window.history.back()"></form>
        </div>
        <?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'updateag') {
        $dbh = connect();
        $organisation = strip_tags($_POST['organisation']);
        $rcr = strip_tags($_POST['rcr']);
        $AoA = strip_tags($_POST['areaOfAssistance']);
        $webAddress = strip_tags($_POST['webAddress']);
        $hometelephone = strip_tags($_POST['hometelephone']);
        $mobiletelephone = strip_tags($_POST['mobiletelephone']);
        $address1 = strip_tags($_POST['address1']);
        $address2 = strip_tags($_POST['address2']);
        $postcode = strip_tags($_POST['postcode']);
        $town = strip_tags($_POST['town']);
        
        if(isset($_POST['editing'])) {
            $id = $_POST['id'];
			$query = $dbh->prepare("UPDATE Agency SET organisation = :org, referralCentreReference = :rcr, homeTelephone = :ht, mobileTelephone = :mt, address1 = :a1, address2 = :a2, postcode = :p, town = :t , areaOfAssistance = :aoa, webAddress = :wa WHERE id = " . $id);
            $redirectmsg = '<h1>Agency updated successfully</h1>';
            $logmsg = 'Updated agency information. Name:' . $organisation;
        } else if(isset($_POST['adding'])) {
			$query = $dbh->prepare("INSERT INTO Agency (organisation, referralCentreReference, homeTelephone, mobileTelephone, address1, address2, postcode, town, areaOfAssistance, webAddress) VALUES (:org, :rcr, :ht, :mt, :a1, :a2, :p, :t, :aoa, :wa)");
            $redirectmsg = '<h1>Agency created successfully</h1>';
            $logmsg = 'Added new agency. Name:' . $organisation;
        }
        if($query->execute(array(":org" => $organisation, ":rcr" => $rcr, ":ht" => $hometelephone, ":mt" => $mobiletelephone, ":a1" => $address1, ":a2" => $address2, ":p" => $postcode, ":t" => $town, ":aoa" => $AoA, ":wa" => $webAddress))) {
            redirect('locations.php', $redirectmsg);
            auditlog($logmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update agencies database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }


    } else if(isset($_POST['mode']) && $_POST['mode'] == 'deleteag') {
        $dbh = connect();
        $id = $_POST['id'];
        $query = $dbh->prepare("UPDATE Agency SET deleted = :del WHERE id = " . $id);
        $redirectmsg = '<h1>Agency deleted successfully</h1>';
        if($query->execute(array(":del" => 1 ))) {
            redirect('locations.php', $redirectmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update agencies database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
    } else if(isset($_GET['mode']) && ($_GET['mode'] == 'adddp' || $_GET['mode'] == 'viewdp')) {
        $dbh = connect();
        
        if($_GET['mode'] == 'viewdp' && isset($_GET['id'])) {
            $editing = true;
            $id = $_GET['id'];
            
            $query = $dbh->prepare("SELECT * FROM DistributionPoint WHERE id = :id");
            
            if($query->execute(array(":id" => $id))) {
				if($query->rowCount() > 0) {
                    $row = $query->fetch();
                } else {
                	die('Invalid ID.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
                }
            } else {
                die('Unable to get distribution point info from database.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
            }
            auditlog('Viewed distribution point information. Name: ' . $row['distributionPointName']);
        } else {
            $editing = false;
        }
        ?>
        <div><h1>Locations</h1></div><br />
        <div><h3>Distribution Point</h3></div><br />
			<form action='locations.php' method='POST' onsubmit="return validateForm()">
			<input type='hidden' name='mode' value='updatedp'>
			<?PHP if($editing) { ?>
				<input type='hidden' name='editing' value='1'>
				<input type='hidden' name='id' value='<?PHP echo $id; ?>'>
			<?PHP } else { ?>
				<input type='hidden' name='adding' value='1'>
			<?PHP } ?>
		<div><table style="width: 100%">
			<tr>
	    		<td><h3>Name *</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['distributionPointName']; ?>' name='dpname' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
			</tr>
			<tr>
				<td><h3>Home telephone number</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['homeTelephone']; ?>' name='hometelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
			</tr>
			<tr>
				<td><h3>Mobile telephone number</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['mobileTelephone']; ?>' name='mobiletelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
			</tr>
			<tr>
				<td><h3>Address 1 *</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['address1']; ?>' name='address1' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
			</tr>
			<tr>
				<td><h3>Address 2</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['address2']; ?>' name='address2' id='opt' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
			</tr>
			<tr>
				<td><h3>Town *</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['town']; ?>' name='town' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
			</tr>
            <tr>
				<td><h3>Postcode *</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['postcode']; ?>' name='postcode' size='30' maxlength='10' autocapitalize='characters'  <?PHP echo $readonly; ?>></td>
			</tr>
			</table></div>
        <div>
            <?PHP if($readonly == '') { ?>
        		<input class="form-input-button" type='submit' value='Submit'>
			<?PHP } ?>
            </form>
            </form>
            <?PHP if($readonly == '') { ?>
                <?PHP if($editing) { ?>
                <form action='locations.php' method='post' onsubmit="return validateForm()" >
                    <input type='hidden' name='mode' value='deletedp'>
                    <input type='hidden' name='deleting' value='1'>
                    <input type='hidden' name='id' value='<?PHP echo $id; ?>'>
                    <input class="form-input-button" type='submit' id='delete' value='delete'>
                </form>
                <?PHP } ?>
            <?PHP } ?>
        <form><input class="form-input-button" type='button' value='Back' onclick="window.history.back()"></form>
        </div>
		<?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'updatedp') {
        $dbh = connect();
        $dpname = strip_tags($_POST['dpname']);
        $hometelephone = strip_tags($_POST['hometelephone']);
        $mobiletelephone = strip_tags($_POST['mobiletelephone']);
        $address1 = strip_tags($_POST['address1']);
        $address2 = strip_tags($_POST['address2']);
        $postcode = strip_tags($_POST['postcode']);
        $town = strip_tags($_POST['town']);
        
        if(isset($_POST['editing'])) {
            $id = $_POST['id'];
			$query = $dbh->prepare("UPDATE DistributionPoint SET distributionPointName = :dpn, homeTelephone = :ht, mobileTelephone = :mt, address1 = :a1, address2 = :a2, postcode = :p, town = :t WHERE id = " . $id);
            $redirectmsg = '<h1>Distribution point updated successfully</h1>';
            $logmsg = 'Updated distribution point information. Name:' . $dpname;
        } else if(isset($_POST['adding'])) {
			$query = $dbh->prepare("INSERT INTO DistributionPoint (distributionPointName, homeTelephone, mobileTelephone, address1, address2, postcode, town) VALUES (:dpn, :ht, :mt, :a1, :a2, :p, :t)");
            $redirectmsg = '<h1>Distribution point created successfully</h1>';
            $logmsg = 'Added new distribution point. Name:' . $dpname;
        }
        
        if($query->execute(array(":dpn" => $dpname, ":ht" => $hometelephone, ":mt" => $mobiletelephone, ":a1" => $address1, ":a2" => $address2, ":p" => $postcode, ":t" => $town))) {
            redirect('locations.php', $redirectmsg);
            auditlog($logmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update distribution point database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'deletedp') {
        $dbh = connect();
        $id = $_POST['id'];
        $query = $dbh->prepare("UPDATE DistributionPoint SET deleted = :del WHERE id = " . $id);
        $redirectmsg = '<h1>Distribution point deleted successfully</h1>';
        if($query->execute(array(":del" => 1))) {
            redirect('locations.php', $redirectmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update distribution point database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
	} else if(isset($_GET['mode']) && ($_GET['mode'] == 'addcw' || $_GET['mode'] == 'viewcw')) {
        $dbh = connect();
        
        if($_GET['mode'] == 'viewcw' && isset($_GET['id'])) {
            $editing = true;
            $id = $_GET['id'];
            
            $query = $dbh->prepare("SELECT * FROM Warehouse WHERE id = :id");
            
            if($query->execute(array(":id" => $id))) {
				if($query->rowCount() > 0) {
                    $row = $query->fetch();
                } else {
                	die('Invalid ID.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
                }
            } else {
                die('Unable to get warehouse info from database.<div><form action=\'locations.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
            }
            auditlog('Viewed warehouse information. Name: ' . $row['centralWarehouseName']);
        } else {
            $editing = false;
        }
        ?>
        <div><h1>Locations</h1></div><br />
		<div><h3>Central Warehouse</h3></div><br />
			<form action='locations.php' method='POST' onsubmit="return validateForm()">
			<input type='hidden' name='mode' value='updatecw'>
			<?PHP if($editing) { ?>
    			<input type='hidden' name='editing' value='1'>
				<input type='hidden' name='id' value='<?PHP echo $id; ?>'>
			<?PHP } else { ?>
				<input type='hidden' name='adding' value='1'>
			<?PHP } ?>
			<div><table style="width: 100%">
				<tr>
					<td><h3>Name *</h3></td>
                    <td><input type='text' value='<?PHP if($editing) echo $row['centralWarehouseName']; ?>' name='cwname' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
				</tr>
				<tr>
					<td><h3>Home telephone number</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['homeTelephone']; ?>' name='hometelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
				</tr>
				<tr>
                    <td><h3>Mobile telephone number</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['mobileTelephone']; ?>' name='mobiletelephone' id='opt' size='30' maxlength='15' <?PHP echo $readonly; ?>></td>
				</tr>
				<tr>
					<td><h3>Address 1 *</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['address1']; ?>' name='address1' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
                </tr>
				<tr>
					<td><h3>Address 2</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['address2']; ?>' name='address2' id='opt' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
				</tr>
				<tr>
					<td><h3>Town *</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['town']; ?>' name='town' size='30' maxlength='32' <?PHP echo $readonly; ?>></td>
                </tr>
                <tr>
					<td><h3>Postcode *</h3></td>
					<td><input type='text' value='<?PHP if($editing) echo $row['postcode']; ?>' name='postcode' size='30' maxlength='10' autocapitalize='characters' <?PHP echo $readonly; ?>></td>
				</tr>
            </table></div>
			<div>
            <?PHP if($readonly == '') { ?>
				<input class="form-input-button" type='submit' value='Submit'>
			<?PHP } ?>
				</form>
                </form>
                <?PHP if($readonly == '') { ?>
                    <?PHP if($editing) { ?>
                        <form action='locations.php' method='post' onsubmit="return validateForm()" >
                            <input type='hidden' name='mode' value='deletecw'>
                            <input type='hidden' name='deleting' value='1'>
                            <input type='hidden' name='id' value='<?PHP echo $id; ?>'>
                            <input class="form-input-button" type='submit' id='delete' value='delete'>
                        </form>
                    <?PHP } ?>
                <?PHP } ?>
				<form><input class="form-input-button" type='button' value='Back' onclick="window.history.back()"></form>
            </div>
		<?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'updatecw') {
        $dbh = connect();
        $cwname = strip_tags($_POST['cwname']);
        $hometelephone = strip_tags($_POST['hometelephone']);
        $mobiletelephone = strip_tags($_POST['mobiletelephone']);
        $address1 = strip_tags($_POST['address1']);
        $address2 = strip_tags($_POST['address2']);
        $postcode = strip_tags($_POST['postcode']);
        $town = strip_tags($_POST['town']);
        
        if(isset($_POST['editing'])) {
            $id = $_POST['id'];
			$query = $dbh->prepare("UPDATE Warehouse SET centralWarehouseName = :cwn, homeTelephone = :ht, mobileTelephone = :mt, address1 = :a1, address2 = :a2, postcode = :p, town = :t WHERE id = " . $id);
            $redirectmsg = '<h1>Warehouse updated successfully</h1>';
            $logmsg = 'Updated warehouse information. Name:' . $cwname;
        } else if(isset($_POST['adding'])) {
			$query = $dbh->prepare("INSERT INTO Warehouse (centralWarehouseName, homeTelephone, mobileTelephone, address1, address2, postcode, town) VALUES (:cwn, :ht, :mt, :a1, :a2, :p, :t)");
            $redirectmsg = '<h1>Warehouse added successfully</h1>';
            $logmsg = 'Added new warehouse. Name:' . $cwname;
        }
        
        if($query->execute(array(":cwn" => $cwname, ":ht" => $hometelephone, ":mt" => $mobiletelephone, ":a1" => $address1, ":a2" => $address2, ":p" => $postcode, ":t" => $town))) {
            redirect('locations.php', $redirectmsg);
            auditlog($logmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update Warehouse database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'deletecw') {
        $dbh = connect();
        $id = $_POST['id'];
        $query = $dbh->prepare("UPDATE Warehouse SET deleted = :del WHERE id = " . $id);
        $redirectmsg = '<h1>Warehouse deleted successfully</h1>';
        if($query->execute(array(":del" => 1))) {
            redirect('locations.php', $redirectmsg);
        } else {
            die('<h1>Error</h1><br /><h3>Unable to update warehouse database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }


    } else {
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT id, organisation, homeTelephone, mobileTelephone, town, deleted FROM Agency ORDER BY organisation ASC");
        if($query->execute()) {
            $rowsAgency = $query->fetchAll();
            $agencyCount = $query->rowCount();
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get agencies information from database.</h3><div>');
        }

        $query = $dbh->prepare("SELECT id, distributionPointName, homeTelephone, mobileTelephone, town, deleted FROM DistributionPoint ORDER BY distributionPointName ASC");
        if($query->execute()) {
            $rowsDP = $query->fetchAll();
            $DPCount = $query->rowCount();
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get distribution points information from database.</h3><div>');
        }
        
        $query = $dbh->prepare("SELECT id, centralWarehouseName, homeTelephone, mobileTelephone, town, deleted FROM Warehouse ORDER BY centralWarehouseName ASC");
        if($query->execute()) {
            $rowsWarehouse = $query->fetchAll();
            $warehouseCount = $query->rowCount();
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get warehouse information from database.</h3><div>');
        }
        auditlog('Viewed locations information.');
        ?>
        <div><h1>Locations</h1></div><br /><br />
        <div><h3>Agencies</h3></div><br />
		<div style="height:250px; overflow:auto;"><table style="width: 100%; text-align:center;">
				<thead>
					<td><h3>Organisation</h3></td>
					<td><h3>Home<br />Telephone</h3></td>
					<td><h3>Mobile<br />Telephone</h3></td>
					<td><h3>Town</h3></td>
					<td>&nbsp;</td>
				</thead>
				<?PHP for($i = 0; $i < $agencyCount; $i++) { 
                    if($rowsAgency[$i]['deleted'] != 1){ ?>
                    	<tr>
    						<td><?PHP echo $rowsAgency[$i]['organisation'];?></td>
    						<td><?PHP echo $rowsAgency[$i]['homeTelephone'];?></td>
    						<td><?PHP echo $rowsAgency[$i]['mobileTelephone'];?></td>
    						<td><?PHP echo $rowsAgency[$i]['town'];?></td>
    						<td><form action='locations.php' method='get'>
    							<input type='hidden' name='mode' value='viewag'>
    							<input type='hidden' name='id' value='<?PHP echo $rowsAgency[$i]['id'];?>'>
    							<input class="form-input-button" type='submit' value='View'></form></td>
    					</tr>
    				<?PHP }
                } ?>
    	</table></div>
    <?PHP if($readonly == '') { ?>
        <div><form action='locations.php' mode='get'><input type='hidden' name='mode' value='addag'><input class="form-input-button" type='submit' value='Add new Agency'></div><br /></form>
    <?PHP } ?>
        <br /><div><h3>Distribution Points</h3></div><br />
		<div style="height:250px; overflow:auto;"><table style="width: 100%; text-align:center;">
            <thead>
				<td><h3>Name</h3></td>
				<td><h3>Home<br />Telephone</h3></td>
				<td><h3>Mobile<br />Telephone</h3></td>
				<td><h3>Town</h3></td>
                <td>&nbsp;</td>
			</thead>
            <?PHP for($i = 0; $i < $DPCount; $i++) { 
                if($rowsDP[$i]['deleted'] != 1){ ?>
    				<tr>
    					<td><?PHP echo $rowsDP[$i]['distributionPointName'];?></td>
    					<td><?PHP echo $rowsDP[$i]['homeTelephone'];?></td>
    					<td><?PHP echo $rowsDP[$i]['mobileTelephone'];?></td>
    					<td><?PHP echo $rowsDP[$i]['town'];?></td>
    					<td><form action='locations.php' method='get'>
    						<input type='hidden' name='mode' value='viewdp'>
    						<input type='hidden' name='id' value='<?PHP echo $rowsDP[$i]['id'];?>'>
    						<input class="form-input-button" type='submit' value='View'></form></td>
    				</tr>
    			<?PHP }
            } ?>
    	</table></div>
	<?PHP if($readonly == '') { ?>
        <div><form action='locations.php' mode='get'><input type='hidden' name='mode' value='adddp'><input class="form-input-button" type='submit' value='Add new Distribution Point'></div><br /></form>
	<?PHP } ?>
        <br /><div><h3>Central Warehouses</h3></div><br />
		<div style="height:250px; overflow:auto;"><table style="width: 100%; text-align:center;">
			<thead>
				<td><h3>Name</h3></td>
				<td><h3>Home<br />Telephone</h3></td>
				<td><h3>Mobile<br />Telephone</h3></td>
				<td><h3>Town</h3></td>
				<td>&nbsp;</td>
            </thead>
            <?PHP for($i = 0; $i < $warehouseCount; $i++) { 
                if($rowsWarehouse[$i]['deleted'] != 1){ ?>
    				<tr>
    					<td><?PHP echo $rowsWarehouse[$i]['centralWarehouseName'];?></td>
    					<td><?PHP echo $rowsWarehouse[$i]['homeTelephone'];?></td>
    					<td><?PHP echo $rowsWarehouse[$i]['mobileTelephone'];?></td>
    					<td><?PHP echo $rowsWarehouse[$i]['town'];?></td>
    					<td><form action='locations.php' method='get'>
    						<input type='hidden' name='mode' value='viewcw'>
    						<input type='hidden' name='id' value='<?PHP echo $rowsWarehouse[$i]['id'];?>'>
    						<input class="form-input-button" type='submit' value='View'></form></td>
    				</tr>
    			<?PHP }
            } ?>
    	</table></div>
    <?PHP if($readonly == '') { ?>
        <div><form action='locations.php' mode='get'><input type='hidden' name='mode' value='addcw'><input class="form-input-button" type='submit' value='Add new Central Warehouse'></div></form>
	<?PHP } ?>
    <?PHP }
    require_once('footer.php');
    ob_flush(); ?>
