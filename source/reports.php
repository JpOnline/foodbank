<?php
    ob_start(); // To hide header messages

    
    if(isset($_GET['mode']) && ($_GET['mode'] != 'listofagencies' && $_GET['mode'] != 'brieflistofclients' && $_GET['mode'] != 'fulllistofclients') || !isset($_GET['mode'])) require_once('header.php');
    require_once('log.php');
    require_once('config.php'); // Including database information
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged'])) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    
    $dbh = connect();
    
    if(isset($_GET['mode']) && $_GET['mode'] == 'parcelsoutofdate') {
        $dbh = connect();
        $query = $dbh->prepare("SELECT id, referenceNumber, packingDate, expiryDate, idAgency, idDP, idWarehouse FROM FoodParcel WHERE wasGiven = 0 AND expiryDate < CURDATE() ORDER BY id DESC");
        
        auditlog('Viewed report: parcels out of date');
        
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
        ?>
        <div><h1>Reports</h1></div><br /><br />
		<div><h3>Parcels out of date</h3></div><br />
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
		<div><form action='reports.php'><input class="form-input-button" type='submit' value='Back'></form></div>
    	<?PHP
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'parcelsperlocations') {
        $dbh = connect();
        ?>
        <div><h1>Reports</h1></div><br /><br />
        <div><h3>Parcels per location</h3></div>
        <form action='reports.php' methog='get'>
        <input type='hidden' name='mode' value='parcelsperlocations'>
        <div><select id='placestype' name='placestype' onchange="getplaces()">
            <option value=''>Select Location</option>
            <option value='agency'>Agency</option>
            <option value='dp'>Distribution Point</option>
            <option value='cw'>Central Warehouse</option>
        </select>
        <span id='getplaces'><select name='location' id='location' disabled></select></span>
        <input  class="form-input-button" type='submit' value='View'></form></div><br /><br />
        <?PHP
        auditlog('Viewed report: parcels per location');
        if(isset($_GET['placestype'])) {
            $placestype = $_GET['placestype'];
            $location = $_GET['location'];
            
            if($placestype == 'agency') {
                $placestype = 'idAgency';
            } else if($placestype == 'dp') {
                $placestype = 'idDP';
            } else if($placestype == 'cw') {
                $placestype = 'idWarehouse';
            }
            
            $query = $dbh->prepare("SELECT id, referenceNumber, packingDate, expiryDate, idAgency, idDP, idWarehouse FROM FoodParcel WHERE " . $placestype . " = :l AND wasGiven = 0 ORDER BY id DESC");
            
        	if($query->execute(array(":l" => $location))) {
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
        	?>
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
			<?PHP
        }
        echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'parcelsperweek') {
        $dbh = connect();
        ?>
        <div><h1>Reports</h1></div><br /><br />
        <div><h3>Parcels per week</h3></div>
        <form action='reports.php' methog='get'>
        <input type='hidden' name='mode' value='parcelsperweek'>
		<div><select name='packingdate'>
			<?PHP $datei = date('Y-m-d', strtotime('last Monday'));
    			$datef = date('Y-m-d', strtotime('now'));
                if($datei != $datef) {?>
					<option value='<?PHP echo $datei.','.$datef; ?>'><?PHP echo date('d-m-Y', strtotime('last Monday')) . ' to ' . date('d-m-Y', strtotime('now')); ?></option>
                <?PHP }
                for($i = 0; $i < 50; $i++) {
        			$datei = date('Y-m-d', strtotime('last Monday - '.($i+1).' week'));
        			$datef = date('Y-m-d', strtotime('last Monday - '.$i.' week')); ?>
					<option value='<?PHP echo $datei.','.$datef; ?>'><?PHP echo date('d-m-Y', strtotime('last Monday - '.($i+1).' week')) . ' to ' . date('d-m-Y', strtotime('last Monday - '.$i.' week')); ?></option>
				<?PHP } ?>
        </select>
        <input  class="form-input-button" type='submit' value='View'></form></div><br /><br />
        <?PHP
    	auditlog('Viewed report: parcels per week');
            
        if(isset($_GET['packingdate'])) {
            $date = explode(',', $_GET['packingdate']);
            $datei = $date[0];
            $datef = $date[1];
            
            echo '<h3>Week '.date('d-m-Y', strtotime($datei)).' to '.date('d-m-Y', strtotime($datef)).'</h3><br />';
            
            $query = $dbh->prepare("SELECT id, referenceNumber, packingDate, expiryDate, idAgency, idDP, idWarehouse FROM FoodParcel WHERE packingDate >= :di AND packingDate <= :df ORDER BY id DESC");
            
        	if($query->execute(array(":di" => $datei, ":df" => $datef))) {
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
            $query = $dbh->prepare("SELECT FP.referenceNumber, FP.packingDate, FP.expiryDate, FP.idVoucher, E.date FROM Exchange E, FoodParcel FP WHERE E.idVoucher = FP.idVoucher AND E.date >= :di AND E.date <= :df ORDER BY date DESC");
            
            if($query->execute(array(":di" => $datei, ":df" => $datef))) {
                $givenCount = $query->rowCount();
                $rowsGiven = $query->fetchAll();
            } else {
                die('Unable to get given parcels from database.');
            }
            echo '<div><h3>Parcels Exchanged';
            if($givenCount == 0) {
                echo '</h3><br /></div>';
			    echo '<div><h3>No parcels were exchanged in this period.</h3><br /></div>';
			} else {
                echo ' - total: '.$givenCount.'</h3><br /></div>';
                ?>
                <div style="height:270px;overflow:auto;">
                <table style="width: 100%; text-align:center;">
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
				<?PHP
            } ?>
            <div><hr></div><br />
            <div><h3>Parcels packed
            <?PHP if($packedCount == 0) {
                echo '</h3><br /></div>';
                echo '<div><h3>No parcels were packed in this period.</h3><br /></div>';
            } else {
                echo ' - total: '.$packedCount.'</h3><br /></div>';
            	?>
       	 		<div style="height:270px;overflow:auto;"><table style="width: 100%; text-align:center;">
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
				</table></div><br />
			<?PHP }
        }
        echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'parcelssincerecbeg') {
        $query = $dbh->prepare("SELECT COUNT(*) AS packed FROM FoodParcel");
        
        if($query->execute()) {
            $row = $query->fetch();
            $packedCount = $row['packed'];
        } else {
            die('Unable to get food parcel information from database.');
        }
        $query = $dbh->prepare("SELECT COUNT(*) AS exchanged FROM FoodParcel WHERE wasGiven = true");
        
        if($query->execute()) {
            $row = $query->fetch();
            $exchangedCount = $row['exchanged'];
        } else {
            die('Unable to get food parcel information from database.');
        }
        auditlog('Viewed report: parcels since records began.');
    	?>
		<div><h1>Reports</h1></div><br /><br />
		<div><h3>Parcels since records began:</h3></div><br />
        <div><h3>Parcels packed: <?PHP echo $packedCount; ?></h3></div>
		<div><h3>Parcels exchanged: <?PHP echo $exchangedCount; ?></h3></div><br /><br />
    	<?PHP
        echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'parcelsinlastyear') {
        $query = $dbh->prepare("SELECT COUNT(*) AS exchanged FROM FoodParcel FP, Exchange E WHERE FP.wasGiven = true AND E.idVoucher = FP.idVoucher AND E.date >= :d");
        
        $date = date('Y-m-d', strtotime('-1 year'));
        
        if($query->execute(array(":d" => $date))) {
            $row = $query->fetch();
            $exchangedCount = $row['exchanged'];
        } else {
            die('Unable to get food parcel information from database.');
        }
        
        $query = $dbh->prepare("SELECT COUNT(*) AS packed FROM FoodParcel WHERE packingDate >= :d");
        if($query->execute(array(":d" => $date))) {
            $row = $query->fetch();
            $packedCount = $row['packed'];
        } else {
            die('Unable to get food parcel information from database.');
        }
        auditlog('Viewed report: parcels in last 12 months.');
    	?>
		<div><h1>Reports</h1></div><br /><br />
        <div><h3>Parcels in last 12 months:</h3></div><br />
        <div><h3>Parcels Given Out: <?PHP echo $packedCount; ?></h3></div>
		<div><h3>Parcels exchanged: <?PHP echo $exchangedCount; ?></h3></div><br /><br />
		<?PHP
    	echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'fooditemsgivencomingin') {
        ?>
        <div><h1>Reports</h1></div><br /><br />
        <div><h3>Food items given out vs. coming in</h3></div>
			<form action='reports.php' methog='get'>
			<input type='hidden' name='mode' value='fooditemsgivencomingin'>
            <div><select name='week'>
				<?PHP $datei = date('Y-m-d', strtotime('last Monday'));
                $datef = date('Y-m-d', strtotime('now'));
                if($datei != $datef) {?>
                    <option value='<?PHP echo $datei.','.$datef; ?>'><?PHP echo date('d-m-Y', strtotime('last Monday')) . ' to ' . date('d-m-Y', strtotime('now')); ?></option>
                <?PHP }
                for($i = 0; $i < 50; $i++) {
                    $datei = date('Y-m-d', strtotime('last Monday - '.($i+1).' week'));
                    $datef = date('Y-m-d', strtotime('last Monday - '.$i.' week')); ?>
                    <option value='<?PHP echo $datei.','.$datef; ?>'><?PHP echo date('d-m-Y', strtotime('last Monday - '.($i+1).' week')) . ' to ' . date('d-m-Y', strtotime('last Monday - '.$i.' week')); ?></option>
				<?PHP } ?>
			</select>
        <input  class="form-input-button" type='submit' value='View'></form></div><br /><br />
    	<?PHP
        if(isset($_GET['week'])) {
            $date = explode(',', $_GET['week']);
            $datei = $date[0];
            $datef = $date[1];
            $itemsIn = array();
            $itemsOut = array();
            
            echo '<h3>Week '.date('d-m-Y', strtotime($date[0])).' to '.date('d-m-Y', strtotime($date[1])).'</h3><br />';
            
            auditlog('Viewed reports: Food items given vs. coming in.');
            
            $query = $dbh->prepare("SELECT Name, id FROM FoodItem ORDER BY Name ASC");
            if($query->execute()) {
                $foodItems = $query->fetchAll();
                $fiCount = $query->rowCount();
            } else {
                die('Unable to get food items information from database.');
            }
            
            $query = $dbh->prepare("SELECT items FROM Donation WHERE date >= :di AND date <= :df");
            if($query->execute(array(":di" => $datei, ":df" => $datef))) {
                $donations = $query->fetchAll();
                
                foreach($donations as $don) {
                    $items = explode('[BRK]', $don['items']);
                    foreach($items as $item) {
                        $itemInfo = explode('[BRK2]', $item);
                        $id = $itemInfo[0];
                        $quantity = $itemInfo[1];
                        
                        if(!isset($itemsIn[$id])) $itemsIn[$id] = 0;
                        $itemsIn[$id] += $quantity;
                    }
                }
            } else {
                die('Unable to get food items information from database.');
            }
            // Select how many parcels each is using each food parcel type
            $query = $dbh->prepare("SELECT idFPType, COUNT(*) as amount FROM FoodParcel WHERE packingDate >= :di AND packingDate <= :df GROUP BY idFPType");
            if($query->execute(array(":di" => $datei, ":df" => $datef))) {
                $fptypes = $query->fetchAll();
                
                foreach($fptypes as $fpt) {
					$query = $dbh->prepare("SELECT idFoodItem, quantity FROM FPType_Contains WHERE idFoodParcelType = :idfpt");
                    
                    if($query->execute(array(":idfpt" => $fpt['idFPType']))) {
                        $rows = $query->fetchAll();
                        foreach($rows as $row) {
	                        if(!isset($itemsOut[$row['idFoodItem']])) $itemsOut[$row['idFoodItem']] = 0;
                        
    	                    $itemsOut[$row['idFoodItem']] += $row['quantity'] * $fpt['amount'];
                        }
                    } else {
                        die('Unable to get food parcel type information from database.');
                    }
                }
            } else {
                die('Unable to get food parcel information from database.');
            }
            ?>
			<div><table style='width:100%;text-align:center;'>
				<thead><tr>
					<td><h3>Food Item</h3></td>
					<td><h3>Counted in</h3></td>
					<td><h3>Given out</h3></td>
					<td><h3>Balance</h3></td>
				</tr></thead>
				<?PHP for($i = 0; $i < $fiCount; $i++) { ?>
					<tr>
                        <td><h4><?PHP echo $foodItems[$i]['Name']; ?></h4></td>
						<?PHP $itemIn = (isset($itemsIn[$foodItems[$i]['id']])) ? $itemsIn[$foodItems[$i]['id']] : 0; ?>
						<td><h4><?PHP echo $itemIn; ?></h4></td>
						<?PHP $itemOut = (isset($itemsOut[$foodItems[$i]['id']])) ? $itemsOut[$foodItems[$i]['id']] : 0; ?>
						<td><h4><?PHP echo $itemOut; ?></h4></td>
						<td><font color='<?PHP if(($itemIn-$itemOut) > 0) echo 'green'; else echo 'red'; ?>' size='2'><strong><?PHP echo $itemIn-$itemOut; ?></strong></font></td>
					</tr>
                <?PHP } ?>
			</table></div>
			<?PHP
        }
        echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'donations') {

    } else if(isset($_GET['mode']) && $_GET['mode'] == 'fooditemsinshortsupply') { ?>
        <div><h1>Reports</h1></div><br /><br />
        <div><h3>Food items in short supply (4 weeks)</h3></div><br />
        <div><form action='reports.php' methog='get'></div>
        <input type='hidden' name='mode' value='fooditemsinshortsupply'>
    	<?PHP
        if(date('Y-m-d', strtotime('last Monday')) > date('Y-m-d', strtotime('last Friday'))) {
            $datei = date('Y-m-d', strtotime('5 Mondays ago'));
        } else {
            $datei = date('Y-m-d', strtotime('4 Mondays ago'));
        }
        $datef = date('Y-m-d', strtotime('last Friday'));
        $itemsIn = array();
       	$itemsOut = array();
            
        auditlog('Viewed reports: Food items in short supply.');
       
        echo '<h3>Week '.date('d-m-Y', strtotime($datei)).' to '.date('d-m-Y', strtotime($datef)).'</h3><br />';
        
       	$query = $dbh->prepare("SELECT Name, id FROM FoodItem ORDER BY Name ASC");
       	if($query->execute()) {
       		$foodItems = $query->fetchAll();
           	$fiCount = $query->rowCount();
       	} else {
           	die('Unable to get food items information from database.');
       	}
        
       	$query = $dbh->prepare("SELECT items FROM Donation WHERE date >= :di AND date <= :df");
       	if($query->execute(array(":di" => $datei, ":df" => $datef))) {
           	$donations = $query->fetchAll();
            	
           	foreach($donations as $don) {
	            $items = explode('[BRK]', $don['items']);
                foreach($items as $item) {
                    $itemInfo = explode('[BRK2]', $item);
                    $id = $itemInfo[0];
                    $quantity = $itemInfo[1];
                	    
                    if(!isset($itemsIn[$id])) $itemsIn[$id] = 0;
                    $itemsIn[$id] += $quantity;
                }
            }
        } else {
            die('Unable to get food items information from database.');
        }
        // Select how many parcels each is using each food parcel type
        $query = $dbh->prepare("SELECT idFPType, COUNT(*) as amount FROM FoodParcel WHERE packingDate >= :di AND packingDate <= :df GROUP BY idFPType");
        if($query->execute(array(":di" => $datei, ":df" => $datef))) {
            $fptypes = $query->fetchAll();
        
            foreach($fptypes as $fpt) {
                $query = $dbh->prepare("SELECT idFoodItem, quantity FROM FPType_Contains WHERE idFoodParcelType = :idfpt");
                
                if($query->execute(array(":idfpt" => $fpt['idFPType']))) {
                    $rows = $query->fetchAll();
                    foreach($rows as $row) {
                        if(!isset($itemsOut[$row['idFoodItem']])) $itemsOut[$row['idFoodItem']] = 0;
                        
                        $itemsOut[$row['idFoodItem']] += $row['quantity'] * $fpt['amount'];
                    }
                } else {
                    die('Unable to get food parcel type information from database.');
                }
            }
        } else {
            die('Unable to get food parcel information from database.');
        }
        ?>
        <div><table style='width:100%;text-align:center;'>
            <thead><tr>
                <td><h3>Food Item</h3></td>
                <td><h3>Counted in</h3></td>
                <td><h3>Given out</h3></td>
                <td><h3>Balance</h3></td>
            </tr></thead>
            <?PHP for($i = 0; $i < $fiCount; $i++) { ?>
                <tr>
                    <td><h4><?PHP echo $foodItems[$i]['Name']; ?></h4></td>
                    <?PHP $itemIn = (isset($itemsIn[$foodItems[$i]['id']])) ? $itemsIn[$foodItems[$i]['id']] : 0; ?>
                    <td><h4><?PHP echo $itemIn; ?></h4></td>
                    <?PHP $itemOut = (isset($itemsOut[$foodItems[$i]['id']])) ? $itemsOut[$foodItems[$i]['id']] : 0; ?>
                    <td><h4><?PHP echo $itemOut; ?></h4></td>
                    <td><font color='<?PHP if(($itemIn-$itemOut) > 0) echo 'green'; else echo 'red'; ?>' size='2'><strong><?PHP echo $itemIn-$itemOut; ?></strong></font></td>
                </tr>
            <?PHP } ?>
        </table></div>
    	<?PHP
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'clientswith3vouchers') {
        $dbh = connect();
        $date = date('Y-m-d', strtotime('-1 year'));
        $query = $dbh->prepare("SELECT Client.id, forename, familyName, COUNT(*) as quantity FROM Client, Voucher, Exchange WHERE Client.id = idClient AND idVoucher = Voucher.id AND date >= :d AND wasExchanged = true GROUP BY idClient HAVING COUNT(*) >= 3");
        
        if($query->execute(array(":d" => $date))) {
			$clients = $query->fetchAll();
            auditlog('Viewed reports: Clients with 3 vouchers in last year.');
        } else {
            die('Unable to get client information from database.');
        }
        ?>
		<div><h1>Reports</h1></div><br /><br />
		<div><h3>Client with 3 or more vouchers in last 12 months</h3></div><br />

		<div><table style='width:100%;text-align:center;'>
			<thead><tr>
				<td><h3>Client</h3></td>
				<td><h3>Vouchers Exchanged</h3></td>
				<td><h3>&nbsp;</h3></td>
			</tr></thead>
			<?PHP foreach($clients as $client) { ?>
				<tr>
					<td><h4><?PHP echo $client['forename'] . ' ' . $client['familyName']; ?></h4></td>
					<td><h4><?PHP echo $client['quantity']; ?></h4></td>
					<td><h4><form action='client.php#vouchersexc' method='get'>
						<input type='hidden' name='mode' value='view'>
						<input type='hidden' name='id' value='<?PHP echo $client['id']; ?>'>
						<div><input class='form-input-button' type='submit' value='View'></div></form></td>
				</tr>
			<?PHP } ?>
		</table></div>
		<?PHP
        echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'clientswith5vouchers') {
        $dbh = connect();
        $date = date('Y-m-d', strtotime('-1 year'));
        $query = $dbh->prepare("SELECT Client.id, forename, familyName, COUNT(*) as quantity FROM Client, Voucher, Exchange WHERE Client.id = idClient AND idVoucher = Voucher.id AND date >= :d AND wasExchanged = true GROUP BY idClient HAVING COUNT(*) >= 5");
        
        if($query->execute(array(":d" => $date))) {
			$clients = $query->fetchAll();
            auditlog('Viewed reports: Clients with 5 vouchers in last year.');
        } else {
            die('Unable to get client information from database.');
        }
        ?>
        <div><h1>Reports</h1></div><br /><br />
        <div><h3>Client with 5 or more vouchers in last 12 months</h3></div><br />

        <div><table style='width:100%;text-align:center;'>
        	<thead><tr>
        		<td><h3>Client</h3></td>
        		<td><h3>Vouchers Exchanged</h3></td>
        		<td><h3>&nbsp;</h3></td>
            </tr></thead>
			<?PHP foreach($clients as $client) { ?>
				<tr>
					<td><h4><?PHP echo $client['forename'] . ' ' . $client['familyName']; ?></h4></td>
					<td><h4><?PHP echo $client['quantity']; ?></h4></td>
					<td><h4><form action='client.php#vouchersexc' method='get'>
						<input type='hidden' name='mode' value='view'>
						<input type='hidden' name='id' value='<?PHP echo $client['id']; ?>'>
						<div><input class='form-input-button' type='submit' value='View'></div></form></td>
                </tr>
			<?PHP } ?>
        </table></div>
		<?PHP
	    echo '<div><form action=\'reports.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>';
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'listofagencies') {
        $dbh = connect();
        $query = $dbh->prepare("SELECT * FROM Agency WHERE deleted = 0 ORDER BY organisation");
        
        auditlog('Viewed reports: List of Agencies.');
        
        if($query->execute()) {
            foreach($query->fetchAll() as $agency) {
                echo '<table>';
                if($agency['referralCentreReference'] != '') echo '<tr><td>Organisation</td><td><strong>' . $agency['organisation'] . '</strong></td></tr>';
                if($agency['referralCentreReference'] != '') echo '<tr><td>Referral Centre Reference&nbsp;&nbsp;</td><td>' . $agency['referralCentreReference'] . '</td></tr>';
                if($agency['homeTelephone'] != '') echo '<tr><td>Home Telephone</td><td>' . $agency['homeTelephone'] . '</td></tr>';
                if($agency['mobileTelephone'] != '') echo '<tr><td>Mobile Telephone</td><td>' . $agency['mobileTelephone'] . '</td></tr>';
                if($agency['address1'] != '') echo '<tr><td>Address 1</td><td>' . $agency['address1'] . '</td></tr>';
                if($agency['address2'] != '') echo '<tr><td>Address 2</td><td>' . $agency['address2'] . '</td></tr>';
                if($agency['town'] != '') echo '<tr><td>Town</td><td>' . $agency['town'] . '</td></tr>';
                if($agency['postcode'] != '') echo '<tr><td>Postcode</td><td>' . $agency['postcode'] . '</td></tr>';
                echo '</table>';
                echo '<br /><br />';
            }
        } else {
            die('Unable to get agencies information from database.');
        }
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'brieflistofclients') {
        $dbh = connect();
        $query = $dbh->prepare("SELECT * FROM Client WHERE deleted = 0 ORDER BY familyName");
        
        auditlog('Viewed reports: Brief list of clients.');
        
        if($query->execute()) {
            foreach($query->fetchAll() as $client) {
                echo '<table>';
                echo '<tr><td><p style=\'margin-right:15px\'>Client: <strong>' . $client['title'] . ' ' . $client['forename'] . ' ' . $client['familyName'] . '</strong></p></td>';
                echo '<td><p style=\'margin-right:15px\'>Address 1: <strong>' . $client['address1'] . '</strong></p></td>';
                if($client['postcode'] != '') echo '<td><p style=\'margin-right:15px\'>Postcode: <strong>' . $client['postcode'] . '</strong></p></td>';
                echo '</table>';

        
        		$query = $dbh->prepare("SELECT E.idVoucher, E.date, E.pointOfIssueType, E.pointOfIssue, V.dateVoucherIssued, A.organisation as agency FROM Voucher V, Exchange E, Agency A WHERE V.idAgency = A.id AND E.idVoucher = V.id AND V.idClient = :id");
                
		        if($query->execute(array(":id" => $client['id']))) {
        		    if($query->rowCount() > 0) {
		                $rows = $query->fetchAll();
        		        $rowsCount = $query->rowCount();
                        $fptNames = array();
                        
                        // Getting food parcel types names
                        $query = $dbh->prepare("SELECT id, name FROM FoodParcelType");
                        
                        if($query->execute()) {
                            $fpts = $query->fetchAll();
                            foreach($fpts as $fpt) {
                                $fptNames[$fpt['id']] = $fpt['name'];
                            }
                        } else {
                            die('<h1>Unable to get food parcel type information from database.</h1>');
                        }
                        
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
                            $query = $dbh->prepare("SELECT idFPType, COUNT(*) as amount FROM FoodParcel WHERE idVoucher = :id GROUP BY idFPType");
                            
                            if($query->execute(array(":id" => $rows[$i]['idVoucher']))) {
                                $parcels = $query->fetchAll();
                                foreach($parcels as $parcel) {
                                    if(!isset($rows[$i]['parcels'])) $rows[$i]['parcels'] = '';
                                    $rows[$i]['parcels'] .= $parcel['amount'] . ' ' . $fptNames[$parcel['idFPType']] . ', ';
                                }
                                $rows[$i]['parcels'] = substr($rows[$i]['parcels'], 0, -2);
                            } else {
                                die('<h1>Unable to get food parcel information from database.</h1>');
                            }
        		        }
		                ?>
						<br /><strong>Vouchers exchanged</strong>
						<table>
							<?PHP
							    foreach($rows as $rowV){ ?>
									<tr>
										<td><p style='margin-right:15px'>ID: <strong><?PHP echo $rowV['idVoucher']; ?></strong></p></td>
										<td><p style='margin-right:15px'>Exchanged: <strong><?PHP echo date('d-m-Y', strtotime($rowV['date'])); ?></strong></p></td>
										<td><p style='margin-right:15px'>Issued: <strong><?PHP echo $rowV['location']; ?></strong></p></td>
										<td><p style='margin-right:15px'>Agency Refererrer: <strong><?PHP echo $rowV['agency']; ?></strong></p></td>
										<td><p style='margin-right:15px'>Parcels: <strong><?PHP echo $rowV['parcels']; ?></strong></p></td>
                                    </tr>
                                <?PHP } ?>
                        </table></div>
		            <?PHP }
	    		} else {
		    	    die('<h1>Error</h1><br /><h3>Unable to get exchanged vouchers from database</h3>');
			    }
    			$query = $dbh->prepare("SELECT V.id, V.dateVoucherIssued, A.organisation FROM Voucher V, Agency A WHERE V.idClient = :id AND V.idAgency = A.id AND V.wasExchanged = false");
    
		    	if($query->execute(array(":id" => $client['id']))) {
        			if($query->rowCount() > 0) {
            			?>
		                <br /><strong>Vouchers issued</strong>
        		        <table>
                			<thead><tr>
		                	<td>ID&nbsp;&nbsp;</td>
        		        	<td>Date Voucher&nbsp;&nbsp;<br />Issued</td>
                			<td>Agency Referrer</td>
		                </tr></thead>
						<?PHP
						    while($rowV = $query->fetch()){ ?>
								<tr>
									<td><?PHP echo $rowV['id'] ; ?></td>
									<td><?PHP echo date('d-m-Y', strtotime($rowV['dateVoucherIssued'])) ; ?></td>
									<td><?PHP echo $rowV['organisation'] ; ?></td>
								</tr>
							<?PHP } ?>
						</table></div>
					<?PHP }
	    		} else {
		    	    die('<h1>Error</h1><br /><h3>Unable to get issued vouchers from database</h3>');
	    		}
                echo '<hr>';
            }
        } else {
	         die('Unable to get agencies information from database.');
        }
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'fulllistofclients') {
        $dbh = connect();
        $query = $dbh->prepare("SELECT * FROM Client WHERE deleted = 0 ORDER BY familyName");
        
        auditlog('Viewed reports: Full list of clients.');
        
        if($query->execute()) {
            foreach($query->fetchAll() as $client) {
                echo '<table>';
                echo '<tr><td><strong>' . $client['title'] . ' ' . $client['forename'] . ' ' . $client['familyName'] . '</strong></td></tr>';
                if($client['dateOfBirth'] != '') echo '<tr><td>Date of Birth</td><td>' . date('d-m-Y', strtotime($client['dateOfBirth'])) . '</td></tr>';
                echo '<tr><td>Gender</td><td>' . $client['gender'] . '</td></tr>';
                if($client['ethnicBackground'] != '-') echo '<tr><td>Ethnic Background&nbsp;&nbsp;</td><td>' . $client['ethnicBackground'] . '</td></tr>';
                echo '<tr><td>Address 1</td><td>' . $client['address1'] . '</td></tr>';
                if($client['address2'] != '') echo '<tr><td>Address 2</td><td>' . $client['address2'] . '</td></tr>';
                if($client['town'] != '') echo '<tr><td>Town</td><td>' . $client['town'] . '</td></tr>';
                if($client['postcode'] != '') echo '<tr><td>Postcode</td><td>' . $client['postcode'] . '</td></tr>';
                echo '</table>';

        
        		$query = $dbh->prepare("SELECT E.idVoucher, E.date, E.pointOfIssueType, E.pointOfIssue, V.dateVoucherIssued, A.organisation as agency FROM Voucher V, Exchange E, Agency A WHERE V.idAgency = A.id AND E.idVoucher = V.id AND V.idClient = :id");
        
		        if($query->execute(array(":id" => $client['id']))) {
        		    if($query->rowCount() > 0) {
		                $rows = $query->fetchAll();
        		        $rowsCount = $query->rowCount();
                        $fptNames = array();

                        // Getting food parcel types names
                        $query = $dbh->prepare("SELECT id, name FROM FoodParcelType");
                        
                        if($query->execute()) {
                            $fpts = $query->fetchAll();
                            foreach($fpts as $fpt) {
                                $fptNames[$fpt['id']] = $fpt['name'];
                            }
                        } else {
                            die('<h1>Unable to get food parcel type information from database.</h1>');
                        }
                        
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
                            $query = $dbh->prepare("SELECT idFPType, COUNT(*) as amount FROM FoodParcel WHERE idVoucher = :id GROUP BY idFPType");
                            
                            if($query->execute(array(":id" => $rows[$i]['idVoucher']))) {
                                $parcels = $query->fetchAll();
                                foreach($parcels as $parcel) {
                                    if(!isset($rows[$i]['parcels'])) $rows[$i]['parcels'] = '';
                                    $rows[$i]['parcels'] .= $parcel['amount'] . ' ' . $fptNames[$parcel['idFPType']] . ', ';
                                }
                                $rows[$i]['parcels'] = substr($rows[$i]['parcels'], 0, -2);
                            } else {
                                die('<h1>Unable to get food parcel information from database.</h1>');
                            }
        		        }
		                ?>
						<br /><strong>Vouchers exchanged</strong>
						<table>
							<?PHP
					    		foreach($rows as $rowV){ ?>
									<tr>
										<td><p style="margin-right:15px">ID: <strong><?PHP echo $rowV['idVoucher']; ?></strong></p></td>
										<td><p style="margin-right:15px">Exchanged: <strong><?PHP echo date('d-m-Y', strtotime($rowV['date'])); ?></strong></p></td>
										<td><p style="margin-right:15px">Issued: <strong><?PHP echo $rowV['location']; ?></strong></p></td>
										<td><p style="margin-right:15px">Agency Refererrer: <strong><?PHP echo $rowV['agency']; ?></strong></p></td>
										<td><p style="margin-right:15px">Parcels: <strong><?PHP echo $rowV['parcels']; ?></strong></p></td>
									</tr>
								<?PHP } ?>
							</table></div>
		            <?PHP }
	    		} else {
		    	    die('<h1>Error</h1><br /><h3>Unable to get exchanged vouchers from database</h3>');
			    }
    			$query = $dbh->prepare("SELECT V.id, V.dateVoucherIssued, A.organisation FROM Voucher V, Agency A WHERE V.idClient = :id AND V.idAgency = A.id AND V.wasExchanged = false");
    
		    	if($query->execute(array(":id" => $client['id']))) {
        			if($query->rowCount() > 0) {
            			?>
		                <br /><strong>Vouchers issued</strong>
        		        <table>
						<?PHP
						    while($rowV = $query->fetch()){ ?>
								<tr>
									<td><p style="margin-right:15px">ID: <strong><?PHP echo $rowV['id'] ; ?></strong></p></td>
									<td><p style="margin-right:15px">Issued: <strong><?PHP echo date('d-m-Y', strtotime($rowV['dateVoucherIssued'])) ; ?></strong></p></td>
									<td><p style="margin-right:15px">Agency Referrer: <strong><?PHP echo $rowV['organisation'] ; ?></strong></p></td>
								</tr>
							<?PHP } ?>
						</table></div>
					<?PHP }
	    		} else {
		    	    die('<h1>Error</h1><br /><h3>Unable to get issued vouchers from database</h3>');
	    		}
                echo '<hr>';
            }
        } else {
	         die('Unable to get agencies information from database.');
        }
	} else {
    	$query = $dbh->prepare("SELECT * FROM Donation");
    	if($query->execute()) {
        	$donRows = $query->fetchAll();
        	$donCount = $query->rowCount();
    	} else {
        	die('<h1>Error</h1><br /><h3>Unable to get donation information from database.</h3>');
    	}
    	//auditlog('Viewed reports.');
    	?>
        <div><h1>Reports</h1></div><br /><br />

		<div>
			<table style='width: 100%;'>
				<tr><td colspan='2'><h1><ul><li>Food Parcels</li></h1></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=parcelsoutofdate'>Parcels out-of-date</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=parcelsperlocations'>Parcels per location</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=parcelsperweek'>Parcels per week</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=parcelssincerecbeg'>Parcels since records began</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=parcelsinlastyear'>Parcels in last 12 months</a></li></ul></h3></td></tr>
				<tr><td colspan='2'>&nbsp;</td></tr>
				<tr><td colspan='2'><h1><ul><li>Food Items</li></h1></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=fooditemsgivencomingin'>Food items given out vs. coming in</a></li></ul></h3></td></tr>
                <tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=donations'>Donations</a></li></ul></h3></td></tr>
				<tr><td colspan='2'>&nbsp;</td></tr>
				<tr><td colspan='2'><h1><ul><li>Clients</li></h1></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=clientswith3vouchers'>Clients with 3 or more vouchers in last 12 months</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=clientswith5vouchers'>Clients with 5 or more vouchers in last 12 months</a></li></ul></h3></td></tr>
				<tr><td colspan='2'>&nbsp;</td></tr>
				<tr><td colspan='2'><h1><ul><li>Printable reports</a></li></h1></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=listofagencies' target='_blank'>List of agencies</a></li></ul></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=brieflistofclients' target='_blank'>Brief list of clients</a></li></h3></td></tr>
				<tr><td width='5%'></td><td><h3><ul><li><a href='reports.php?mode=fulllistofclients' target='_blank'>Full list of clients</a></li></h3></td></tr>
				</ul>
			</table>
		</div>
    <?PHP }
    require_once('footer.php');
    ob_flush(); ?>