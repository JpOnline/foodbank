<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], DPSTAFF) && !getAuth($_SESSION['user']['auth'], AGSTAFF))) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    
    require_once('config.php'); // Including database information
    
    if(isset($_GET['mode']) && $_GET['mode'] == 'add') { // Adding new client
        if(!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], AGSTAFF)) {
            redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
            die();
        }
        ?>
        <div><h1>New Client</h1></div><br />
        <form action='client.php' method='post' onsubmit="return validateForm()">
		<input type='hidden' name='mode' value='update'>
		<input type='hidden' name='adding' value='true'>
    	<div><table style="width: 100%">
			<tr>
				<td><h3>Title *</h3></td>
				<td><select name='title'>
					<option value='Mr'>Mr.</option>
					<option value='Miss'>Miss</option>
					<option value='Mrs'>Mrs.</option>
					<option value='Ms'>Ms.</option>
					<option value='Other'>Other</option>
				</select></td>
			</tr>
			<tr>
        		<td><h3>Family Name *</h3></td>
            	<td><input type='text' value='' name='familyName' maxlength='15'></td>
			</tr>
			<tr>
        		<td><h3>Forename *</h3></td>
        		<td><input type='text' value='' name='forename' maxlength='15'></td>
			</tr>
			<tr>
        		<td><h3>Date of Birth (DD-MM-YYYY)</h3></td>
        		<td><input type='text' value='' name='dateOfBirth' id='optdob' maxlength='10'></td>
			</tr>
			<tr>
    			<td><h3>Gender *</h3></td>
				<td><select name='gender'>
					<option value='male'>Male</option>
					<option value='female'>Female</option>
				</select></td>
			</tr>
			<tr>
				<td><h3>Ethnic Background</h3></td>
				<td><select name='ethnicBackground'>
					<option value=''>-</option>
                    <option value='whitebritish'>White/British</option>
					<option value='whiteother'>White/Other</option>
					<option value='mixedrace'>Mixed Race</option>
					<option value='blackbritish'>Black/British</option>
					<option value='blackother'>Black/Other</option>
					<option value='asianbritish'>Asian/British</option>
					<option value='asianother'>Asian/Other</option>
					<option value='other'>Other</option>
                </select></td>
			</tr>
			<tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><h5><input type='checkbox' onchange="nofixaddr(this)">No fixed address</h5></td>
			</tr>
			<tr>
        		<td><h3>Address 1 *</h3></td>
        		<td valign='middle'><input type='text' value='' name='address1' id='addr1' maxlength='128'></td>
			</tr>
			<tr>
				<td><h3>Address 2</h3></td>
				<td><input type='text' value='' name='address2' id='optaddr2' maxlength='128'></td>
			</tr>
			<tr>
				<td><h3>Town *</h3></td>
				<td><input type='text' value='' name='town' id='town' maxlength='32'></td>
            </tr>
			<tr>
				<td><h3>Postcode *</h3></td>
				<td><input type='text' value='' name='postcode' id='postcode' maxlength='10'></td>
			</tr>
		</table><br /><h5>* Mandatory fields</h5><br /><br /></div>
        <div>
			<input class="form-input-button" type='submit' value='Submit'></form>
            <input class='form-input-button'  type='button' value='Back' onclick='window.history.back()'>
		</div>
		<?PHP
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'view' && isset($_GET['id'])) { // Viewing client
        if(isset($_GET['id']))
            $id = $_GET['id'];
        else
            die('<br /><h1>Client not found</h1><br /><br /><form><input class="form-input-button" type=\'submit\' value=\'Back\'></form></div>');
        
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT id, title, familyName, forename, dateOfBirth, gender, address1, address2, postcode, town, ethnicBackground, oldAddress FROM Client WHERE id = :id");
        
        if($query->execute(array(":id" => $id))) {
            $found = true;

            if($query->rowCount() == 0) {
                die('<br /><h1>Client not found</h1><br /><br /><form><input class="form-input-button" type=\'submit\' value=\'Back\'></form></div>');
            } else {
            	$row = $query->fetch();
                if($row['address1'] == 'No fixed address')
                    $nofix = true;
                else
                    $nofix = false;
            	auditlog('Viewed client ' . $row['forename'] . ' ' . $row['familyName']);   
            }
            
            if(getAuth($_SESSION['user']['auth'], DPSTAFF)) {
                $readonly = 'disabled=\'disabled\'';
            } else if(getAuth($_SESSION['user']['auth'], AGSTAFF) || getAuth($_SESSION['user']['auth'], ADMIN)) {
                $readonly = '';
            }
            ?>
            
        	<div><h1>View Client</h1></div><br />
			<form action='client.php' method='post' onsubmit="return validateForm()">
			<input type='hidden' name='mode' value='update'>
    		<div><table style="width: 100%">
			<tr>
				<td><h3>ID</h3></td>
            	<td><input type='text' name='id' value='<?PHP echo $row['id']; ?>' readonly='readonly'></td>
			</tr>
			<tr>
    			<td><h3>Title *</h3></td>
				<td><select name='title' <?PHP echo $readonly;?>>
                    <option value='Mr' <?PHP if($row['title'] == 'Mr') echo 'selected'; ?>>Mr.</option>
					<option value='Miss' <?PHP if($row['title'] == 'Miss') echo 'selected'; ?>>Miss</option>
					<option value='Mrs' <?PHP if($row['title'] == 'Mrs') echo 'selected'; ?>>Mrs.</option>
					<option value='Ms' <?PHP if($row['title'] == 'Ms') echo 'selected'; ?>>Ms.</option>
					<option value='Other' <?PHP if($row['title'] == 'Other') echo 'selected'; ?>>Other</option>
                </select></td>
			</tr>
			<tr>
        		<td><h3>Family Name *</h3></td>
				<td><input type='text' value='<?PHP echo $row['familyName']; ?>' name='familyName' maxlength='15' <?PHP echo $readonly;?>></td>
			</tr>
			<tr>
        		<td><h3>Forename *</h3></td>
        		<td><input type='text' value='<?PHP echo $row['forename']; ?>' name='forename' maxlength='15' <?PHP echo $readonly;?>></td>
			</tr>
			<tr>
        		<td><h3>Date of Birth (DD-MM-YYYY)</h3></td>
        		<td><input type='text' value='<?PHP if($row['dateOfBirth'] != '0000-00-00') echo date('d-m-Y', strtotime($row['dateOfBirth'])); ?>' name='dateOfBirth' id='optdob' maxlength='10' <?PHP echo $readonly;?>></td>
			</tr>
			<tr>
        		<td><h3>Gender *</h3></td>
        		<td><select name='gender' <?PHP echo $readonly;?>>
						<option value='male' <?PHP if($row['gender'] == 'male') echo 'selected'; ?>>Male</option>
						<option value='female' <?PHP if($row['gender'] == 'female') echo 'selected'; ?>>Female</option>
				</select></td>
			</tr>
			<tr>
				<td><h3>Ethnic Background</h3></td>
                <td><select name='ethnicBackground' <?PHP echo $readonly;?>>
					<option value='' <?PHP if($row['ethnicBackground'] == '') echo 'selected'; ?>>-</option>
					<option value='whitebritish' <?PHP if($row['ethnicBackground'] == 'whitebritish') echo 'selected'; ?>>White/British</option>
					<option value='whiteother' <?PHP if($row['ethnicBackground'] == 'whiteother') echo 'selected'; ?>>White/Other</option>
					<option value='mixedrace' <?PHP if($row['ethnicBackground'] == 'mixedrace') echo 'selected'; ?>>Mixed Race</option>
					<option value='blackbritish' <?PHP if($row['ethnicBackground'] == 'blackbritish') echo 'selected'; ?>>Black/British</option>
					<option value='blackother' <?PHP if($row['ethnicBackground'] == 'blackother') echo 'selected'; ?>>Black/Other</option>
					<option value='asianbritish' <?PHP if($row['ethnicBackground'] == 'asianbritish') echo 'selected'; ?>>Asian/British</option>
    				<option value='asianother' <?PHP if($row['ethnicBackground'] == 'asianother') echo 'selected'; ?>>Asian/Other</option>
					<option value='other' <?PHP if($row['ethnicBackground'] == 'other') echo 'selected'; ?>>Other</option>
				</select></td>
			</tr>
            <tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
		<?PHP if(getAuth($_SESSION['user']['auth'], AGSTAFF) || getAuth($_SESSION['user']['auth'], ADMIN)) { ?>
			<tr>
				<td>&nbsp;</td>
				<td><h5><input type='checkbox' onclick="nofixaddr(this)" <?PHP if($nofix) echo 'checked=\'checked\'';?>>No fixed address</h5></td>
            </tr>
		<?PHP } ?>
			<tr>
				<td><h3>Address 1 *</h3></td>
				<td><input type='text' value='<?PHP echo $row['address1']; ?>' name='address1' id='addr1' maxlength='32' <?PHP echo $readonly;?>></td>

			</tr>
        	<tr>
				<td><h3>Address 2</h3></td>
				<td><input type='text' value='<?PHP echo $row['address2']; ?>' name='address2' id='optaddr2' <?PHP if($nofix) echo 'disabled';?> maxlength='32' <?PHP echo $readonly;?>></td>
			</tr>
        	<tr>
            	<td><h3>Town *</h3></td>
				<td><input type='text' value='<?PHP echo $row['town']; ?>' name='town' id='<?PHP if($nofix) echo 'opt';?>town' <?PHP if($nofix) echo 'disabled';?> maxlength='32' <?PHP echo $readonly;?>></td>
			</tr>
			<tr>
				<td><h3>Postcode *</h3></td>
                <td><input type='text' value='<?PHP echo $row['postcode']; ?>' name='postcode' id='<?PHP if($nofix) echo 'opt';?>postcode' <?PHP if($nofix) echo 'disabled';?> maxlength='10' <?PHP echo $readonly;?>></td>
			</tr>
            <tr>
				<td><br /><h5>* Mandatory fields</h5><br /></td>
				<td><br /><?PHP if($row['oldAddress'] != null) { ?><h5><a href='#oldaddr'>Client Old addresses</a></h5><?PHP } ?></td>
            </tr>
			</table></div>
	        <div>
				<?PHP if($readonly == '') { ?>
                	<input class="form-input-button" type='submit' value='Update'>
				<?PHP } ?>
				</form>
				<form><input class="form-input-button" type='submit' value='Back'></form>
	        </div><br /><hr><br />
			<?PHP
				$query = $dbh->prepare("SELECT E.idVoucher, E.date, E.pointOfIssueType, E.pointOfIssue, V.dateVoucherIssued FROM Voucher V, Exchange E WHERE E.idVoucher = V.id AND V.idClient = :id");
                
                if($query->execute(array(":id" => $_GET['id']))) {
                    if($query->rowCount() > 0) {
                        $rows = $query->fetchAll();
                        $rowsCount = $query->rowCount();
                        
                        for($i = 0; $i < $rowsCount; $i++) {
                            if($rows[$i]['pointOfIssueType'] == 'agency') {
                                $query = $dbh->prepare("SELECT organisation FROM Agency where id = :id");
                                
                                if($query->execute(array(":id" => $rows[$i]['pointOfIssue']))) {
                                    $rowL = $query->fetch();
                                    $rows[$i]['location'] = $rowL['organisation'];
                                } else {
                                    die('<h1>Unable to get agency information from database.</h1>');
                                }
                            } else if($rows[$i]['pointOfIssueType'] == 'dp') {
                                $query = $dbh->prepare("SELECT distributionPointName FROM DistributionPoint where id = :id");
                                
                                if($query->execute(array(":id" => $rows[$i]['pointOfIssue']))) {
                                    $rowL = $query->fetch();
                                    $rows[$i]['location'] = $rowL['distributionPointName'];
                                } else {
                                    die('<h1>Unable to get distribution point information from database.</h1>');
                                }
                            } else if($rows[$i]['pointOfIssueType'] == 'cw') {
                                $query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse where id = :id");
                                
                                if($query->execute(array(":id" => $rows[$i]['pointOfIssue']))) {
                                    $rowL = $query->fetch();
                                    $rows[$i]['location'] = $rowL['centralWarehouseName'];
                                } else {
                                    die('<h1>Unable to get warehouse information from database.</h1>');
                                }
                            }
                        }
                        ?>
						<div><a id='vouchersexc'></a><h3>Vouchers exchanged</h3></div><br />
						<div><table style="width: 100%; text-align:center;">
							<thead><tr>
								<td><h3>ID</h3></td>
								<td><h3>Exchanged</h3></td>
								<td><h3>Point of Issue</h3></td>
								<td>&nbsp;</td>
							</tr></thead>
							<?PHP
                                foreach($rows as $rowV){ ?>
                                    <tr>
										<td><?PHP echo $rowV['idVoucher']; ?></td>
                                    	<td><?PHP echo date('d-m-Y', strtotime($rowV['date'])); ?></td>
										<td><?PHP echo $rowV['location']; ?></td>
                                    	<td><form action='voucher.php' method='get'>
											<input type='hidden' name='mode' value='viewvoucher'>
											<input type='hidden' name='id' value='<?PHP echo $rowV['idVoucher'] ; ?>'>
											<input class="form-input-button" type='submit' value='View Voucher'></form></td>
                                    </tr>
                                <?PHP } ?>
                        </table></div><br /><hr><br />
                    <?PHP }
                } else {
                    die('<h1>Error</h1><br /><h3>Unable to get exchanged vouchers from database</h3>');
                }
                $query = $dbh->prepare("SELECT V.id, V.dateVoucherIssued, A.organisation FROM Voucher V, Agency A WHERE V.idClient = :id AND V.idAgency = A.id AND V.wasExchanged = false");
                        
                if($query->execute(array(":id" => $_GET['id']))) {
                    if($query->rowCount() > 0) {
                        ?>
                        <div><h3>Vouchers issued</h3></div><br />
                        <div><table style="width: 100%; text-align:center;">
                            <thead><tr>
								<td><h3>ID</h3></td>
                                <td><h3>Issued</h3></td>
                                <td><h3>Agency Referrer</h3></td>
                                <td>&nbsp;</td>
                            </tr></thead>
                    		<?PHP
                                while($rowV = $query->fetch()){ ?>
                                    <tr>
										<td><?PHP echo $rowV['id'] ; ?></td>
                                        <td><?PHP echo date('d-m-Y', strtotime($rowV['dateVoucherIssued'])) ; ?></td>
                                        <td><?PHP echo $rowV['organisation'] ; ?></td>
                                        <td><form action='voucher.php' method='get'>
											<input type='hidden' name='mode' value='viewvoucher'>
											<input type='hidden' name='id' value='<?PHP echo $rowV['id'] ; ?>'>
											<input class="form-input-button" type='submit' value='View Voucher'></form>
										</td>
                                    </tr>
                            <?PHP } ?>
                        </table></div><br /><hr><br />
                    <?PHP }
                } else {
                    die('<h1>Error</h1><br /><h3>Unable to get issued vouchers from database</h3>');
                }
                if($row['oldAddress'] != null) { ?>
                	<div><h3><a id='oldaddr'></a>Old Addresses</h3></div><br />
                	<div><table style="width: 50%;">
                    	<tr>
                        	<td><?PHP echo $row['oldAddress']; ?></td>
                    	</tr>
                	</table></div>
			<?PHP } else {echo $row['oldAddress'];}
		} else {
    		die('<div><br /><h1>Unable to get client info from database.</h1><br /><br /><form><input class="form-input-button" type=\'submit\' value=\'Back\'></form></div>');
		}
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'update') { // Updating database
        $dbh = connect();
        
        if(isset($_POST['adding'])) $adding = true;
        else {
            $adding = false;
         	$id = $_POST['id'];
        }
        
        if(!$adding) $id = strip_tags($_POST['id']);
        $title = strip_tags($_POST['title']);
        $lastname = strip_tags($_POST['familyName']);
        $firstname = strip_tags($_POST['forename']);
        $dob = ($_POST['dateOfBirth'] == '') ? '0000-00-00' : date('Y-m-d', strtotime(strip_tags($_POST['dateOfBirth'])));
        $gender = strip_tags($_POST['gender']);
        $ethnicbg = strip_tags($_POST['ethnicBackground']);
        
        if(!isset($_POST['address2'])) {
            $nofixedaddr = true;
            $address2 = '';
            $postcode = '';
            $town = '';
        } else {
            $nofixedaddr = false;
            $address2 = strip_tags($_POST['address2']);
            $postcode = strip_tags($_POST['postcode']);
            $town = strip_tags($_POST['town']);
        }
        
        $address1 = strip_tags($_POST['address1']);

        
        if($adding) { // If adding new client to database
            // --- Checking for duplicate entries
            $query = $dbh->prepare("SELECT id FROM Client WHERE UPPER(forename) = :forename AND UPPER(familyName) = :familyName");
            if($query->execute(array(":forename" => strtoupper($firstname), ":familyName" => strtoupper($lastname)))) {
            	if($query->rowCount() > 0) {
                    die('<h1>Error</h1><br />Client already registered.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to update client database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            // ---
            
            $query = $dbh->prepare("INSERT INTO Client (title, familyName, 	forename, 	dateOfBirth, 	gender, 	address1,	address2,	postcode,	town,	ethnicBackground)" .
						                    	"VALUES (:title, :lastname,	:firstname,	'$dob', 	:gender,	:address1,	:address2,	:postcode,	:town,	:ethnicbg)");
            
            if($query->execute(array(":title" => $title, ":lastname" => $lastname, ":firstname" => $firstname, ":gender" => $gender, ":address1" => $address1, ":address2" => $address2, ":postcode" => $postcode, ":town" => $town, ":ethnicbg" => $ethnicbg))) {
                auditlog('Added client ' . $firstname . ' ' . $lastname);
                redirect('client.php', 'Client added successfully.');
            } else {
                die('<h1>Error</h1><br /><h3>Unable to update client database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
        } else { // If updating existing client in database
            // --- Checking for duplicate entries
            $query = $dbh->prepare("SELECT id, address1, address2, postcode, town, oldAddress FROM Client WHERE UPPER(forename) = :forename AND UPPER(familyName) = :familyName AND id != :id");
            if($query->execute(array(":forename" => strtoupper($firstname), ":familyName" => strtoupper($lastname), ":id" => $id))) {
            	if($query->rowCount() > 0) {
                    die('<div><br /><h1>Client name/surname is relating to another registered client.</h1><br /><br /><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to select client information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            // ---
            
            
            // -- Checking old addresses
            $query = $dbh->prepare("SELECT address1, address2, postcode, town, oldAddress FROM Client WHERE id = :id");
            if($query->execute(array(":id" => $id))) {
            	$row = $query->fetch();
                if($row['address1'] != $address1 || $row['address2'] != $address2 || $row['postcode'] != $postcode || $row['town'] != $town) {
                    if($row['address1'] == 'No fixed address') {
                        $oldaddr = $row['address1'] . '<br /><br />' . $row['oldAddress'];
                    } else {
                        $oldaddr = $row['address1'] . '<br />' . $row['address2'] . '<br />' . $row['town'] . '<br />' . $row['postcode'] . '<br /><br />' . $row['oldAddress'];
                    }
                } else {
                    $oldaddr = $row['oldAddress'];
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to select client information from database.(2)</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            
            $query = $dbh->prepare("UPDATE Client SET title = :title, familyName = :lastname, forename = :firstname, dateOfBirth = '$dob', gender = :gender, address1 = :address1, address2 = :address2, postcode = :postcode, town = :town, ethnicBackground = :ethnicbg, oldAddress = :oa WHERE id = :id");

            if($query->execute(array(":title" => $title, ":lastname" => $lastname, ":firstname" => $firstname, ":gender" => $gender, ":address1" => $address1, ":address2" => $address2, ":postcode" => $postcode, ":town" => $town, ":ethnicbg" => $ethnicbg, ":id" => $id, ":oa" => $oldaddr))) {
                auditlog('Updated client ' . $firstname . ' ' . $lastname);
                redirect('client.php', 'Client updated successfully.');
            } else {
                die('<h1>Error</h1><br /><h3>Unable to update client database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
    	}
        
    } else {
        $dbh = connect();
        $query = $dbh->prepare("SELECT id, familyName, forename, postcode FROM Client ORDER BY familyName ASC");
        
        if($query->execute()) {
            $clientRows = $query->fetchAll();
            $clientCount = $query->rowCount();
            auditlog('Viewed all clients');
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get client information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        ?>
        <div><h1>Clients</h1></div><br /><br />
		<form><div><h3>Search for Clients</h3>
		<input type="hidden" id="ajaxid" value="client"/>
		<input type="text" id="clientinfo" onkeyup="showResult(this.value)" />
        <select name='searchtype' id='searchtype' onchange="hideResult()">
        	<option value='lastname'>Family Name</option>
        	<option value='postcode'>Postcode</option>
        </select>
        <input class="form-input-button" type='reset' value='Reset' onClick='hideResult()'></form>
    <?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], AGSTAFF)) { ?>
        <form action='client.php' method='get'>
        	<div><input class="form-input-button" type='submit' value='Add new client'><input type='hidden' name='mode' value='add'></div>
        </form>
		</div>
	<?PHP } ?>
		<div style="height:300px;overflow:auto;"><br />
        	<span id="txtResult"></span><span id="allclients">
			<table style='width: 100%;'>
		        <thead>
					<td><h3>Last Name</h3></td>
					<td><h3>First Name</h3></td>
					<td><h3>Postcode</h3></td>
					<td>&nbsp;</td>
                </thead>
					<?PHP foreach($clientRows as $client) { ?>
				    <tr>
						<td><?PHP echo $client['familyName']; ?></td>
						<td><?PHP echo $client['forename']; ?></td>
						<td><?PHP echo $client['postcode']; ?></td>
						<td><form action='client.php' method='get'>
							<input type='hidden' name='mode' value='view'>
							<input type='hidden' name='id' value='<?PHP echo $client['id']; ?>'>
							<div><input class='form-input-button' type='submit' value='View'></div></form></td>
                    </tr>
				<?PHP } ?>
		</span></div>
    <?PHP }
    require_once('footer.php');
    ob_flush(); ?>
