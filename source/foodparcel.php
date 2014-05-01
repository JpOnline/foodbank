<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], DPSTAFF) && !getAuth($_SESSION['user']['auth'], PACKER))) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    require_once('config.php'); // Including database information
    
    if(isset($_GET['mode']) && ($_GET['mode'] == 'packnew' || $_GET['mode'] == 'viewfppacked')) {
        $dbh = connect();
        
        $editing = ($_GET['mode'] == 'viewfppacked') ? true : false;
        
        if(!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], DPSTAFF) && !getAuth($_SESSION['user']['auth'], PACKER)) {
            redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
            die();
        }
        
        if(getAuth($_SESSION['user']['auth'], ADMIN)) {
            $admin = true;
        } else {
            $admin = false;
        }
        
        $query = $dbh->prepare("SELECT  name, id FROM FoodParcelType");


        if($query->execute()) {
            $fptypecount = $query->rowCount();
            $rowsfptype = $query->fetchAll();
        } else {
            die('Unable to get food parcel types from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
        }
        

        $query = $dbh->prepare("SELECT  name, id FROM FoodParcelType WHERE edited = 0");


        if($query->execute()) {
            $fptypecountdistinct = $query->rowCount();
            $rowsfptypedistinct = $query->fetchAll();
        } else {
            die('Unable to get food parcel types from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
        }


        if($editing && isset($_GET['id'])) {
            $id = $_GET['id'];
            $query = $dbh->prepare("SELECT FP.idFPType, FP.expiryDate, FP.packingDate, FP.referenceNumber, FP.idAgency, FP.idWarehouse, FP.idDP, FPT.tagColour FROM FoodParcel FP, FoodParcelType FPT WHERE FP.id = :id AND FP.idFPTYPE = FPT.id");
            if($query->execute(array(":id" => $id))) {
				if($query->rowCount() > 0) {
                    $row = $query->fetch();
                    if($row['idAgency']) {
                        $query = $dbh->prepare("SELECT id, organisation as name FROM Agency");
                        if($query->execute()) {
                            $rowsLocation = $query->fetchAll();
                            $locationCount = $query->rowCount();
                            $row['idlocation'] = $row['idAgency'];
                        } else {
                            die('Unable to get agency information from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
                        }
                    } else if($row['idDP']) {
                        $query = $dbh->prepare("SELECT id, distributionPointName as name FROM DistributionPoint");
                        if($query->execute()) {
                            $rowsLocation = $query->fetchAll();
                            $locationCount = $query->rowCount();
                            $row['idlocation'] = $row['idDP'];
                        } else {
                            die('Unable to get distribution point information from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
                        }
                    } else if($row['idWarehouse']) {
                        $query = $dbh->prepare("SELECT id, centralWarehouseName as name FROM Warehouse");
                        if($query->execute()) {
                            $rowsLocation = $query->fetchAll();
                            $locationCount = $query->rowCount();
                            $row['idlocation'] = $row['idWarehouse'];
                        } else {
                            die('Unable to get warehouse information from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
                        }
                    }
                    auditlog('Viewed food parcel, Reference Number: ' . $row['referenceNumber']);
                } else {
                    die('Invalid id.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
                }
            } else {
                die('Unable to get food parcel information from database.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
            }
        } else if($_GET['mode'] == 'viewfppacked' && !isset($_GET['id'])){
            die('Invalid id.<div><form action=\'foodparcel.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
        }
        ?>
        <div><h1>Food Parcel</h1></div><br />
		<?PHP if(!$editing) { ?>
			<form action='foodparcel.php' method='post' onsubmit="return validateFormPackNewFP()">
				<input type='hidden' name='mode' value='update'>
				<input type='hidden' name='adding' value='adding'>
	        <div><h3>Pack New Food Parcel</h3></div><br /><br /><br />
		<?PHP } else { ?>
				<form action='foodparcel.php' method='post' onsubmit="return validateFormPackNewFP()">
				<input type='hidden' name='mode' value='update'>
				<input type='hidden' name='updating' value='updating'>
                <input type='hidden' name='id' value='<?PHP echo $id; ?>'>
	        <div><h3>Edit Food Parcel</h3></div><br /><br /><br />
		<?PHP } ?>
        <div><table style="width: 100%">
        	<?PHP if($_GET['mode'] != 'viewfppacked'){ ?>
            <tr>
        		<td><h3>Food Parcel Type</h3></td>
        		<td><select name='foodparceltype' id='foodparceltype' onchange="getfoodparcelitems()" <?PHP if($editing && !$admin) echo 'disabled'; ?>>
            			<option value=''>Type</option>
						<?PHP for($i = 0; $i < $fptypecountdistinct; $i++) { ?>
							<?PHP $selected = ($editing && $row['idFPType'] == $rowsfptypedistinct[$i]['id']) ? 'selected=\'selected\'' : ''; ?>
							<option value='<?PHP echo $rowsfptypedistinct[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $rowsfptypedistinct[$i]['name']; ?></option>
						<?PHP } ?>
					</select><?PHP if($editing && !$admin) echo '<input type=\'hidden\' name=\'foodparceltype\' value=\'' . $row['idFPType'] . '\'>' ?>
				</td>
            </tr>
            <?PHP }else{?>
                 <tr>
                    <td><h3>Food Parcel Type</h3></td>
                    <td><select name='foodparceltype' id='foodparceltype' onchange="getfoodparcelitems()" <?PHP if($editing && !$admin) echo 'disabled'; ?>>
                            <option value=''>Type</option>
                            <?PHP for($i = 0; $i < $fptypecount; $i++) { ?>
                                <?PHP $selected = ($editing && $row['idFPType'] == $rowsfptype[$i]['id']) ? 'selected=\'selected\'' : ''; ?>
                                <option value='<?PHP echo $rowsfptype[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $rowsfptype[$i]['name']; ?></option>
                            <?PHP } ?>
                        </select><?PHP if($editing && !$admin) echo '<input type=\'hidden\' name=\'foodparceltype\' value=\'' . $row['idFPType'] . '\'>' ?>
                    </td>
                </tr>
            <?PHP } ?>
        	<tr>
        		<td><h3>Expiry date</h3></td>
                <td><select name='expirydate' <?PHP if($editing && !$admin) echo 'disabled'; ?>>
					<?PHP for($i = 0; $i < 15; $i++) { ?>
						<?PHP $date = date('Y-m-d', mktime(0, 0, 0, date("m")+$i, 1, date("Y")));
                            $selected = ($editing && date('Y-m-d', strtotime($row['expiryDate'])) == $date) ? 'selected=\'selected\'' : ''; ?>
							<option value='<?PHP echo $date; ?>' <?PHP echo $selected; ?>><?PHP echo date('m-Y', mktime(0, 0, 0, date("m")+$i, 1, date("Y"))); ?></option>
					<?PHP } ?>
					</select><?PHP if($editing && !$admin) echo '<input type=\'hidden\' name=\'expirydate\' value=\'' . date('Y-m-d', strtotime($row['expiryDate'])) . '\'>' ?>
				</td>
        	</tr>
        	<tr>
        		<td><h3>Packing Date (DD/MM/YYYY)</h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo date('d-m-Y', strtotime($row['packingDate'])); else echo date('d-m-Y'); ?>' name='packingdate' id='packingdate' <?PHP if($editing && !$admin) echo 'readonly=\'readonly\''; ?> maxlength='10'></td>
        	</tr>
        	<tr>
        		<td><h3>Reference Number<?PHP if(!$editing) echo ' (Suggested)'; ?></h3></td>
        		<td><input type='text' value='<?PHP if($editing) echo $row['referenceNumber']; ?>' id='referencenumber' name='referencenumber' <?PHP if(!$admin) echo 'readonly=\'readonly\''; ?>></td>
        	</tr>
			<tr>
				<td><h3>Tag Colour</h3></td>
				<td><input type='text' value='<?PHP if($editing) echo $row['tagColour']; ?>' id='tagcolour' name='tagcolour' <?PHP if(!$admin) echo 'readonly=\'readonly\''; ?>></td>
			</tr>
			<tr>
				<td><h3>Location</h3></td>
                <td><select id='placestype' name='placestype' onchange="getplaces()" >
					<option value=''>Select Location</option>
					<?PHP if($editing) { ?><option value='agency' <?PHP if($editing && $row['idAgency']) echo 'selected=\'selected\'' ?>>Agency</option><?PHP } ?>
					<?PHP if($editing) { ?><option value='dp' <?PHP if($editing && $row['idDP']) echo 'selected=\'selected\'' ?>>Distribution Point</option><?PHP } ?>
					<option value='cw' <?PHP if($editing && $row['idWarehouse']) echo 'selected=\'selected\'' ?>>Central Warehouse</option></select><br />
					<span id='getplaces'><select name='location' id='location' <?PHP if(!$editing ) echo 'disabled'; ?>>
						<?PHP for($i = 0; $i < $locationCount; $i++) {
							$selected = ($editing && $rowsLocation[$i]['id'] == $row['idlocation']) ? 'selected=\'selected\'' : ''; ?>
							<option value='<?PHP echo $rowsLocation[$i]['id'] ?>' <?PHP echo $selected; ?>><?PHP echo $rowsLocation[$i]['name']; ?></option>
						<?PHP } ?>
					</select></span>
				</td>
			</tr>
        </table></div>
        <div>
        <?PHP if(getAuth($_SESSION['user']['auth'], PACKER) || getAuth($_SESSION['user']['auth'], ADMIN)) { ?>
            <input class="form-input-button" type='submit' id='submit' value='Submit'>
            </form>
            <?PHP if($editing) { ?>
                 <form action='foodparcel.php' method='post' onsubmit="return validateFormPackNewFP()">
                        <input type='hidden' name='mode' value='unpack'>
                        <input type='hidden' name='id' value='<?PHP echo $id; ?>'>
                        <input type='hidden' name='location' value='<?PHP echo $row['idlocation'] ?>'>
                        <input type='hidden'  name='foodparceltype' value= '<?PHP echo $row['idFPType']?>'>
                        <input type='hidden' value='<?PHP if($editing) echo $row['referenceNumber']; ?>' id='referencenumber' name='referencenumber' <?PHP if(!$admin) echo 'readonly=\'readonly\''; ?>>
                        <input class="form-input-button" type='submit' id='unpack' value='Unpack'>
                </form>
            <?PHP }
        } ?>
        <form action='foodparcel.php'><input class="form-input-button" type='submit' value='Back' id='back'></div></form><br /><br /><br />
        <div>
            <span id='foodparcelitemsprint' style='display:none;'>
            <span id="foodparcelitems"></span>
            <input class="form-input-button" type='submit' value='Print packing form' onclick='printpackingform()'></span>
        </div>
        <?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'update') {
        $dbh = connect();
        $locationType = $_POST['placestype'];
        $location = $_POST['location'];
        
        if(isset($_POST['adding'])) {
	        $fptype = $_POST['foodparceltype'];
    	    $expiryDate = date('Y-m-d', strtotime($_POST['expirydate']));
        	$packingDate = date('Y-m-d', strtotime(strip_tags($_POST['packingdate'])));
	        $refNumber = strip_tags($_POST['referencenumber']);
            $query = $dbh->prepare("SELECT id FROM FoodParcel where referenceNumber = :rn");
            
            if($query->execute(array(":rn" => $refNumber))) {
                if($query->rowCount() == 0) {
                    // Checking if there is enough for each item in the warehouse and decrement the amount
                    $query = $dbh->prepare("SELECT idFoodItem, quantity FROM FPType_Contains WHERE idFoodParcelType = :id");
                    // First of all, select all the items from the food parcel type
                    if($query->execute(array(":id" => $fptype))) {
                        $items = $query->fetchAll();
                        $itemMissing = false;
                        $itemsMissing = array();
                        $removeItems = array();
                        foreach($items as $item) {
                            // Then, check if there is enough quantity
                            $query = $dbh->prepare("SELECT quantity FROM Store WHERE idFoodItem = :id AND idWarehouse = :idwh");
                            
                            if($query->execute(array(":id" => $item['idFoodItem'], ":idwh" => $location))) {
                                if($query->rowCount() == 0) {
                                    $itemMissing = true;
                                    $itemsMissing[] = $item['idFoodItem'];
                                } else {
                                    $row = $query->fetch();
                                    if($row['quantity'] < $item['quantity']) {
                                        $itemMissing = true;
                                        $itemsMissing[] = $item['idFoodItem'];
                                    } else if($row['quantity'] == $item['quantity']) {
                                        // The stock of the item will be equal to 0. Then, should be removed from the store table.
                                        $removeItems[] = $item['idFoodItem'];
                                    }
                                }
                            } else {
                                die('<h1>Error</h1><br /><h3>Unable to select food parcel type information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                            }
                        }
                        if($itemMissing) {
                            $itemsNames = '';
                            foreach($itemsMissing as $item) {
                                $query = $dbh->prepare("SELECT Name FROM FoodItem WHERE id = :id");
                                
                                if($query->execute(array(":id" => $item))) {
                                    $row = $query->fetch();
                                    $itemsNames .= $row['Name'] . '<br />';
                                } else {
                                    die('<h1>Error</h1><br /><h3>Unable to select food parcel type information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                                }
                            }
                            die('<h1>Error</h1><br /><h3>Sorry, there is not enough amount of the following items in the warehouse:</h3><br /><h4>'.$itemsNames.'</h4><div><form action=\'foodparcel.php\'><input type=\'hidden\' name=\'mode\' value=\'packnew\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
                        } else {
                            try {
                                $dbh->beginTransaction();
                                // Decrementing items
                                $i = 0;
                                foreach($items as $item) {
                                    if(isset($removeItems[$i]) && $item['idFoodItem'] == $removeItems[$i]) {
                                        $query = $dbh->prepare("DELETE FROM Store WHERE idWarehouse = :idwh AND idFoodItem = :idfi");
                                    	$query->execute(array(":idwh" => $location, ":idfi" => $item['idFoodItem']));
                                        $i++;
                                    } else {
                                    	$query = $dbh->prepare("UPDATE Store SET quantity = quantity - :qt WHERE idWarehouse = :idwh AND idFoodItem = :idfi");
                                    	$query->execute(array(":qt" => $item['quantity'], ":idwh" => $location, ":idfi" => $item['idFoodItem']));
                                    }
                                }
                                
                                // Creating Food Parcel
                                switch($locationType) {
                                    case 'agency':
                                        $queryLocation = 'idAgency, idWarehouse, idDP'; // It will set as (1, 0, 0), respectively
                                        break;
                                    case 'cw':
                                        $queryLocation = 'idWarehouse, idAgency, idDP'; // It will set as (1, 0, 0), respectively
                                        break;
                                    case 'dp':
                                        $queryLocation = 'idDP, idAgency, idWarehouse'; // It will set as (1, 0, 0), respectively
                                        break;
                                }
                                $query = $dbh->prepare("INSERT INTO FoodParcel (idFPType, expiryDate, packingDate, referenceNumber, ".$queryLocation.", wasGiven,  idVoucher) VALUES (:fpt, :ed, :pd, :rn, :l, 0, 0, false, 0)");
                                
                                $query->execute(array(":fpt" => $fptype, ":ed" => $expiryDate, ":pd" => $packingDate, ":rn" => $refNumber, ":l" => $location));
                                
                                $dbh->commit();
                            } catch(PDOException $ex) {
                                //Something went wrong rollback!
                                $db->rollBack();
                                die('<h1>Error</h1><br /><h3>Unable to update food parcel database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                            }
                        }
                    } else {
                        die('<h1>Error</h1><br /><h3>Unable to select food parcel type information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                } else {
                    die('<h1>Error</h1><br /><h3>Duplicated reference number.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to get food parcel information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            redirect('foodparcel.php', '<h3>Food Parcel created successfully.</h3>');
            auditlog('Packed new food parcel, Reference Number: ' . $refNumber);
        } else if(isset($_POST['updating']) && isset($_POST['id'])) {
            $id = $_POST['id'];
            $fptype = $_POST['foodparceltype'];
    	    $expiryDate = date('Y-m-d', strtotime($_POST['expirydate']));
        	$packingDate = date('Y-m-d', strtotime(strip_tags($_POST['packingdate'])));
	        $refNumber = strip_tags($_POST['referencenumber']);
            $query = $dbh->prepare("SELECT id FROM FoodParcel where referenceNumber = :rn AND id != :id");
            
            if($query->execute(array(":rn" => $refNumber, ":id" => $id))) {
                if($query->rowCount() == 0) {
                    switch($locationType) {
                        case 'agency':
                            $queryLocation = 'idAgency = ' . $location . ', idWarehouse = 0, idDP = 0'; // It will set as (id, 0, 0), respectively
                            break;
                        case 'cw':
                            $queryLocation = 'idWarehouse = ' . $location . ', idAgency = 0, idDP = 0'; // It will set as (id, 0, 0), respectively
                            break;
                        case 'dp':
                            $queryLocation = 'idDP = ' . $location . ', idAgency = 0, idWarehouse = 0'; // It will set as (id, 0, 0), respectively
                            break;
                    }
                    $query = $dbh->prepare("UPDATE FoodParcel SET idFPType = :idfpt, expiryDate = :ed, packingDate = :pd, referenceNumber = :rn, " . $queryLocation . " WHERE id = :id");
                    
                    if(!$query->execute(array(":idfpt" => $fptype, ":ed" => $expiryDate, ":pd" => $packingDate, ":rn" => $refNumber, ":id" => $id))) {
                        die('<h1>Error</h1><br /><h3>Unable to update food parcel database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                    redirect('foodparcel.php', '<h3>Food Parcel updated successfully.</h3>');
                    auditlog('Updated food parcel, Reference Number: ' . $_POST['referencenumber']);
                } else {
                    die('<h1>Error</h1><br /><h3>Duplicated reference number.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to get food parcel information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
        }
    }else if(isset($_POST['mode']) && $_POST['mode'] == 'unpack' && isset($_POST['id'])) {
        $dbh = connect();
        $id = $_POST['id'];
        $location = $_POST['location'];
        $refNumber = strip_tags($_POST['referencenumber']);
        $fptype = $_POST['foodparceltype'];

        $query = $dbh->prepare("SELECT id FROM FoodParcel where referenceNumber = :rn");
        if($query->execute(array(":rn" => $refNumber))) {
            if($query->rowCount() == 1) {
                $query = $dbh->prepare("SELECT idFoodItem, quantity FROM FPType_Contains WHERE idFoodParcelType = :id");
                // First of all, select all the items from the food parcel type
                if($query->execute(array(":id" => $fptype))) {
                    $items = $query->fetchAll();
                    $itemMissing = false;
                    $itemsMissing = array();
                    $incrementItems = array();
                    if($itemMissing) {
                        $itemsNames = '';
                        foreach($itemsMissing as $item) {
                            $query = $dbh->prepare("SELECT Name FROM FoodItem WHERE id = :id");
                            if($query->execute(array(":id" => $item))) {
                                $row = $query->fetch();
                                $itemsNames .= $row['Name'] . '<br />';
                            } else {
                                die('<h1>Error</h1><br /><h3>Unable to select food parcel type information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                            }
                        }
                        die('<h1>Error</h1><br /><h3>Sorry, there is not enough amount of the following items in the warehouse:</h3><br /><h4>'.$itemsNames.'</h4><div><form action=\'foodparcel.php\'><input type=\'hidden\' name=\'mode\' value=\'packnew\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
                    } else {
                        try {
                            // incrementing items
                            foreach($items as $item) {
                                $query = $dbh->prepare("UPDATE Store SET quantity = quantity + :qt WHERE idWarehouse = :idwh AND idFoodItem = :idfi");
                                $query->execute(array(":qt" => $item['quantity'], ":idwh" => $location, ":idfi" => $item['idFoodItem']));
                            }
                        } catch(PDOException $ex) {
                            //Something went wrong rollback!
                            $db->rollBack();
                            die('<h1>Error</h1><br /><h3>Unable to update food parcel database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                        }
                    }
                } else {
                    die('<h1>Error</h1><br /><h3>Unable to select food parcel type information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Duplicated reference number1.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
        } else {
            die('<h1>Error</h1><br /><h3>Unable to get food parcel information from database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        $query = $dbh->prepare("DELETE FROM FoodParcel WHERE referenceNumber = :rn");
        if(!$query->execute(array(":rn" => $refNumber))) {
            die('<h1>Error</h1><br /><h3>Unable to update food parcel database.</h3><div><input class=\'form-input-button\' type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        redirect('foodparcel.php', '<h3>Food Parcel unpacked successfully.</h3>');
    }else if(isset($_GET['viewfptypes'])) {
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT  name, id FROM FoodParcelType WHERE edited = 0 ORDER BY name ASC");
        if($query->execute()) {
            $fptypecount = $query->rowCount();
            $rowsfptype = $query->fetchAll();
        } else {
            die('Unable to get food parcel types from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        
        $query = $dbh->prepare("SELECT Name, id FROM FoodItem ORDER BY name ASC");
        if($query->execute()) {
            $ficount = $query->rowCount();
            $rowsFI = $query->fetchAll();
        } else {
            die('Unable to get food parcel types from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        auditlog('Viewed food parcels type');
        ?>
        <div><h1>Food Parcel Types</h1></div><br /><br />
		<form action='foodparcel.php' method='post' onsubmit="return validateForm()">
		<div><table>
			<tr><td><h3>Type:</h3></td><td><select name='foodparceltype' id='selectfptype' onchange="fptitems()">
				<option value=''>Type</option>
				<?PHP for($i = 0; $i < $fptypecount; $i++) { ?>
					<option value='<?PHP echo $rowsfptype[$i]['id']; ?>'><?PHP echo $rowsfptype[$i]['name']; ?></option>
    			<?PHP } ?>
            <?PHP if(getAuth($_SESSION['user']['auth'], ADMIN)) {?>
				</select><input type='button' value='Remove Food Parcel Type' onclick="removefptype()"></td>
            <?PHP } ?>
			<tr><td colspan='2'>&nbsp;</td></tr>
			<tr><td colspan='2'><span id='loading' style='display:none'><h3><br />Loading...</h3></span></td></tr>
			<tr><td colspan='2'>&nbsp;</td></tr>

            <tr><td><h3>Name</h3></td><td><input type='text' id='fptname' name='name' maxlength='15'></td></tr>
			<tr><td><h3>Starting Letter</h3></td><td><input type='text' id='fptstartletter' name='startingletter' maxlength='3'></td></tr>
			<tr><td><h3>Tag Colour</h3></td><td><input type='text' id='fptagcolour' name='tagcolour' maxlength='15'></td></tr>

			<tr><td colspan='2'>&nbsp;</td></tr>
			<tr><td colspan='2'>&nbsp;</td></tr>
            <tr><td><h3>Items</h3></td><td><h3>Quantity</h3></td></tr>
            <p>
            <br><br>
			<tr><td colspan='2'>&nbsp;</td></tr>	

            <?PHP for($i = 0; $i < $ficount; $i++) { ?>
				<tr><td><h5><input type='checkbox' value='<?PHP echo $rowsFI[$i]['id']; ?>' id='item<?PHP echo $rowsFI[$i]['id']; ?>' name='item[<?PHP echo $rowsFI[$i]['id']; ?>]' onchange="activateSelectFPTitem(this)">
					&nbsp;&nbsp;<?PHP echo $rowsFI[$i]['Name']; ?></h5></td>
                    <p>
                    <td><select id='quantity<?PHP echo $rowsFI[$i]['id']; ?>' name='quantity[<?PHP echo $rowsFI[$i]['id']; ?>]' disabled>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
                        <option>6</option>
						<option>7</option>
						<option>8</option>
						<option>9</option>
						<option>10</option>
					</select></td></tr>
			<?PHP } ?>
		</table></div><br />
		<div><input type='hidden' name='mode' value='newfptype'>
		<input class="form-input-button" type='submit' id='submit' value='Create New Type'></form>
        <form action='foodparcel.php'><input class="form-input-button" type='submit' value='Back' id='back'></div></form><br /><br /><br />
		<?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'newfptype') {
        $name = strip_tags($_POST['name']);
        $startingletter = strtoupper(strip_tags($_POST['startingletter']));
        $tagcolour = strip_tags($_POST['tagcolour']);
        if(isset($_POST['item']))
        	$items = $_POST['item'];
        else
        	die('<h1>Error</h1><br /><h3>You must select at least one item to create a new Food Parcel Type.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        $quantities = $_POST['quantity'];
        
        //Verifying for duplicated entries
        $dbh = connect();
        $query = $dbh->prepare("SELECT id FROM FoodParcelType WHERE UPPER(name) = :name");
        
        if($query->execute(array(":name" => strtoupper($name)))) {
             // Create the new food parcel type

             // senting the old vertions of a existing parcel type in case of modification of a existing datatype
            $query = $dbh->prepare("UPDATE FoodParcelType SET edited = 1 WHERE name = :n");
            $query->execute(array(":n" => $name));

            $query = $dbh->prepare("INSERT INTO FoodParcelType (name, tagColour, startingLetter) VALUES (:n, :tc, :sl)");
            if($query->execute(array(":n" => $name, ":tc" => $tagcolour, ":sl" => $startingletter))) {
                //Getting the id of the new type
                $query = $dbh->prepare("SELECT MAX(id) FROM FoodParcelType");
                if($query->execute()) {
                    $row = $query->fetch();
                    $fptypeid = $row['MAX(id)']; // The new id
                    foreach($items as $item => $value) {
                        // Inserting on the contains tabel all the items of the new food parcel type
                        $query = $dbh->prepare("INSERT INTO FPType_Contains (quantity, idFoodParcelType, idFoodItem) VALUES (:q, :idfpt, :idfi)");
                        if(!$query->execute(array(":q" => $quantities[$item], ":idfpt" => $fptypeid, ":idfi" => $item))) {
                            die('<h1>Error</h1><br /><h3>Unable to update food parcel type items database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                        }
                    }
                } else {
                    die('<h1>Error</h1><br /><h3>Unable to get food parcel type info from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
                die('<h1>Error</h1><br /><h3>Unable to update food parcel type database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }   
        } else {
                die('<h1>Error</h1><br /><h3>Unable to get food parcel type information from database</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        redirect('foodparcel.php?mode=packnew', '<h3>Food Parcel Type created successfully.</h3>');
        auditlog('Created new food parcel type. Name: ' . $name);
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'viewallpacked') {
        $dbh = connect();
        $query = $dbh->prepare("SELECT id, referenceNumber, packingDate, expiryDate, idAgency, idDP, idWarehouse FROM FoodParcel WHERE wasGiven = 0 ORDER BY id DESC");
        
        if($query->execute()) {
            $packedCount = $query->rowCount();
            $rowsPacked = $query->fetchAll();
            $locations = array();
            $i = 0;
            foreach($rowsPacked as $row) {
                if($row['idAgency'] != '0') {
                    $query = $dbh->prepare("SELECT organisation FROM Agency WHERE id = " . $row['idAgency']);
                    
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'ag';
                        $locations[$i]['location'] = $location['organisation'];
                        $locations[$i++]['idLocation'] = $row['idAgency'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                } else if($row['idDP'] != '0') {
                    $query = $dbh->prepare("SELECT distributionPointName FROM DistributionPoint WHERE id = " . $row['idDP']);
                    
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'dp';
                        $locations[$i]['location'] = $location['distributionPointName'];
                        $locations[$i++]['idLocation'] = $row['idDP'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                } else if($row['idWarehouse'] != '0') {
                    $query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse WHERE id = " . $row['idWarehouse']);
                    
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'cw';
                        $locations[$i]['location'] = $location['centralWarehouseName'];
                        $locations[$i++]['idLocation'] = $row['idWarehouse'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                }
            }
            auditlog('Viewed all food parcels packed');
        } else {
            die('Unable to get packed parcels from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        ?>
        <div><h1>Food parcels</h1></div><br /><br />
        <div><h3>Parcels packed</h3></div><br />
        <div><table style="width: 100%; text-align:center;">
        	<thead>
        		<td><h3>Parcel no.</h3></td>
        		<td><h3>Packing date</h3></td>
        		<td><h3>Expiry date</h3></td>
        		<td><h3>Location</h3></td>
        		<td>&nbsp;</td>
        	</thead>
        	<?PHP for($i = 0; $i < $packedCount; $i++) { ?>
                <tr>
        			<td><?PHP echo $rowsPacked[$i]['referenceNumber']; ?></td>
        			<td><?PHP echo date('d-m-Y', strtotime($rowsPacked[$i]['packingDate'])); ?></td>
        			<td><?PHP echo date('m-Y', strtotime($rowsPacked[$i]['expiryDate'])); ?></td>
        			<td><?PHP echo '<a href=\'locations.php?mode=view'.$locations[$i]['typeLocation'].'&id='.$locations[$i]['idLocation'].'\'>' .$locations[$i]['location'] . '</a></td>'; ?>
        			<td><form action='foodparcel.php' mode='get'><input type='hidden' name='mode' value='viewfppacked'><input type='hidden' name='id' value='<?PHP echo $rowsPacked[$i]['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
        		</tr>
            <?PHP } ?>
        </table></div>
		<div><form action='foodparcel.php'><input class="form-input-button" type='submit' value='Back'></form></div>
		<?PHP
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'viewallgiven') {
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT DISTINCT FP.referenceNumber, FP.packingDate, FP.expiryDate, FP.idVoucher, E.date FROM Exchange E, FoodParcel FP WHERE E.idVoucher = FP.idVoucher ORDER BY date DESC");
        
        if($query->execute()) {
            $givenCount = $query->rowCount();
            $rowsGiven = $query->fetchAll();
        } else {
            die('Unable to get given parcels from database.');
        }
        auditlog('Viewed all food parcels given.');
        ?>
		<div><h1>Food parcels</h1></div><br /><br />
		<div><h3>Parcels given out</h3></div><br />
		<div><table style="width: 100%; text-align:center;">
			<thead><tr>
				<td><h3>Reference number</h3></td>
				<td><h3>Packing date</h3></td>
				<td><h3>Expiry date</h3></td>
				<td><h3>Date given</h3></td>
				<td><h3>Voucher</h3></td>
			</tr></thead>
			<?PHP for($i = 0; $i < $givenCount; $i++) { ?>
				<tr>
					<td><?PHP echo $rowsGiven[$i]['referenceNumber']; ?></td>
					<td><?PHP echo date('d-m-Y', strtotime($rowsGiven[$i]['packingDate'])); ?></td>
					<td><?PHP echo date('m-Y', strtotime($rowsGiven[$i]['expiryDate'])); ?></td>
					<td><?PHP echo date('d-m-Y', strtotime($rowsGiven[$i]['date']));  ?></td>
					<td><form action='voucher.php' mode='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $rowsGiven[$i]['idVoucher']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
				</tr>
            <?PHP } ?>
		</table></div>
		<div><form action='foodparcel.php'><input class="form-input-button" type='submit' value='Back'></form></div>
		<?PHP
    } else {
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT FP.referenceNumber, FP.packingDate, FP.expiryDate, FP.idVoucher, E.date FROM Exchange E, FoodParcel FP WHERE E.idVoucher = FP.idVoucher ORDER BY date DESC LIMIT 0, 5");
        
        if($query->execute()) {
            $givenCount = $query->rowCount();
            $rowsGiven = $query->fetchAll();
        } else {
            die('Unable to get given parcels from database.');
        }
        
        $query = $dbh->prepare("SELECT id, referenceNumber, packingDate, expiryDate, idAgency, idDP, idWarehouse FROM FoodParcel WHERE wasGiven = 0 ORDER BY id DESC LIMIT 0, 5");
        
        if($query->execute()) {
            $packedCount = $query->rowCount();
            $rowsPacked = $query->fetchAll();
            $locations = array();
            $i = 0;
            foreach($rowsPacked as $row) {
                if($row['idAgency'] != '0') {
                    $query = $dbh->prepare("SELECT organisation FROM Agency WHERE id = " . $row['idAgency']);
                  
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'ag';
                        $locations[$i]['location'] = $location['organisation'];
                        $locations[$i++]['idLocation'] = $row['idAgency'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                } else if($row['idDP'] != '0') {
                    $query = $dbh->prepare("SELECT distributionPointName FROM DistributionPoint WHERE id = " . $row['idDP']);
                    
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'dp';
                        $locations[$i]['location'] = $location['distributionPointName'];
                        $locations[$i++]['idLocation'] = $row['idDP'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                } else if($row['idWarehouse'] != '0') {
                    $query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse WHERE id = " . $row['idWarehouse']);
                    
                    if($query->execute()) {
                        $location = $query->fetch();
                        $locations[$i]['typeLocation'] = 'cw';
                        $locations[$i]['location'] = $location['centralWarehouseName'];
                        $locations[$i++]['idLocation'] = $row['idWarehouse'];
                    } else {
			            die('Unable to get packed parcel location from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                    }
                }
            }
        } else {
            die('Unable to get packed parcels from database.<div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
        }
        auditlog('Viewed all food parcels.');
        ?>
        <div><h1>Food parcels</h1></div><br />
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], PACKER)) { ?>
		<div><form action='foodparcel.php' method='get'>
			<input type='hidden' name='mode' value='packnew'>
			<input class="form-input-button" type='submit' value='Pack new food parcel'></form>
		</div><br />
        <div><form action='foodparcel.php' method='get'>
                <input type='hidden' name='viewfptypes' value='viewfptypes'>
                <input class="form-input-button" type='submit' value='Edit or Create Food Parcel Types'></form></div><br />
	<?PHP } ?>
        <div><h3>Parcels packed</h3></div><br />
        <div><table style="width: 100%; text-align:center;">
        	<thead>
        		<td><h3>Parcel no.</h3></td>
        		<td><h3>Packing date</h3></td>
        		<td><h3>Expiry date</h3></td>
        		<td><h3>Location</h3></td>
        		<td>&nbsp;</td>
        	</thead>
        	<?PHP for($i = 0; $i < $packedCount; $i++) { ?>
        		<tr>
        			<td><?PHP echo $rowsPacked[$i]['referenceNumber']; ?></td>
        			<td><?PHP echo date('d-m-Y', strtotime($rowsPacked[$i]['packingDate'])); ?></td>
        			<td><?PHP echo date('m-Y', strtotime($rowsPacked[$i]['expiryDate'])); ?></td>
        			<td><?PHP echo '<a href=\'locations.php?mode=view'.$locations[$i]['typeLocation'].'&id='.$locations[$i]['idLocation'].'\'>' .$locations[$i]['location'] . '</a></td>'; ?>
        			<td><form action='foodparcel.php' mode='get'><input type='hidden' name='mode' value='viewfppacked'><input type='hidden' name='id' value='<?PHP echo $rowsPacked[$i]['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
                </tr>
            <?PHP } ?>
        </table></div>
		<div><form action='foodparcel.php'><input type='hidden' name='mode' value='viewallpacked'><input class="form-input-button" type='submit' value='View all parcels packed'></form></div>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], DPSTAFF)) { ?>
        <br /><hr><br />
        <div><h3>Parcels given out</h3></div><br />
        <div><table style="width: 100%; text-align:center;">
        	<thead>
        		<td><h3>Reference number</h3></td>
        		<td><h3>Packing date</h3></td>
        		<td><h3>Expiry date</h3></td>
        		<td><h3>Date given</h3></td>
        		<td><h3>Voucher</h3></td>
            </thead>
            <?PHP for($i = 0; $i < $givenCount; $i++) { ?>
        		<tr>
        			<td><?PHP echo $rowsGiven[$i]['referenceNumber']; ?></td>
        			<td><?PHP echo date('d-m-Y', strtotime($rowsGiven[$i]['packingDate'])); ?></td>
        			<td><?PHP echo date('m-Y', strtotime($rowsGiven[$i]['expiryDate'])); ?></td>
        			<td><?PHP echo date('d-m-Y', strtotime($rowsGiven[$i]['date']));  ?></td>
        			<td><form action='voucher.php' mode='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $rowsGiven[$i]['idVoucher']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
        		</tr>
            <?PHP } ?>
		</table></div>
		<div><form action='foodparcel.php'><input type='hidden' name='mode' value='viewallgiven'><input class="form-input-button" type='submit' value='View all parcels given'></form></div>
    <?PHP }
        }
    require_once('footer.php');
    ob_flush(); ?>
