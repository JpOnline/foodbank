<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('config.php'); // Including database information

    $dbh = connect();
    
    $query = $dbh->prepare("SELECT COUNT(*) AS packed FROM FoodParcel");

    if($query->execute()) {
        $row = $query->fetch();
        $packedCountBeginning = $row['packed'];
    } else {
        die('Unable to get food parcel information from database.');
    }
    $query = $dbh->prepare("SELECT COUNT(*) AS exchanged FROM FoodParcel WHERE wasGiven = true");

    if($query->execute()) {
        $row = $query->fetch();
        $exchangedCountBeginning = $row['exchanged'];
    } else {
        die('Unable to get food parcel information from database.');
    }
    
    $query = $dbh->prepare("SELECT COUNT(*) AS exchanged FROM FoodParcel FP, Exchange E WHERE FP.wasGiven = true AND E.idVoucher = FP.idVoucher AND E.date >= :d");
    $date = date('Y-m-d', strtotime('-1 year'));
    
    if($query->execute(array(":d" => $date))) {
        $row = $query->fetch();
        $exchangedCountYear = $row['exchanged'];
    } else {
        die('Unable to get food parcel information from database.');
    }
    
    $query = $dbh->prepare("SELECT COUNT(*) AS packed FROM FoodParcel WHERE packingDate >= :d");
    if($query->execute(array(":d" => $date))) {
        $row = $query->fetch();
        $packedCountYear = $row['packed'];
    } else {
        die('Unable to get food parcel information from database.');
    }
    
    $query = $dbh->prepare("SELECT Name, id FROM FoodItem ORDER BY Name ASC");
    if($query->execute()) {
        $foodItems = $query->fetchAll();
        $fiCount = $query->rowCount();
    } else {
        die('Unable to get food items information from database.');
    }
    
    $datei = (strtotime('last Monday') > strtotime('last Friday')) ? date('Y-m-d', strtotime('last Monday - 1 week')) : date('Y-m-d', strtotime('last Monday'));
    $datef = date('Y-m-d', strtotime('last Friday'));
    
    $query = $dbh->prepare("SELECT items FROM Donation WHERE date >= :di AND date <= :df");
    if($query->execute(array(":di" => $datei, ":df" => $datef))) {
        $donations = $query->fetchAll();
        $itemsIn = 0;
        
        foreach($donations as $don) {
            $items = explode('[BRK]', $don['items']);
            foreach($items as $item) {
                $itemInfo = explode('[BRK2]', $item);
                $id = $itemInfo[0];
                $quantity = $itemInfo[1];
                
                $itemsIn += $quantity;
            }
        }
    } else {
        die('Unable to get food items information from database.');
    }
    // Select how many parcels each is using each food parcel type
    $query = $dbh->prepare("SELECT idFPType, COUNT(*) as amount FROM FoodParcel WHERE packingDate >= :di AND packingDate <= :df GROUP BY idFPType");
    if($query->execute(array(":di" => $datei, ":df" => $datef))) {
        $fptypes = $query->fetchAll();
        $itemsOut = 0;
        
        foreach($fptypes as $fpt) {
            $query = $dbh->prepare("SELECT idFoodItem, quantity FROM FPType_Contains WHERE idFoodParcelType = :idfpt");

            if($query->execute(array(":idfpt" => $fpt['idFPType']))) {
                $rows = $query->fetchAll();
                foreach($rows as $row) {
                    $itemsOut += $row['quantity'] * $fpt['amount'];
                }
            } else {
                die('Unable to get food parcel type information from database.');
            }
        }
    } else {
        die('Unable to get food parcel information from database.');
    }
    
    ?>
	<div><h1>Welcome to Canterbury Food Bank</h1></div><br /><br />
	<div><h3>Some of our statistics:</h3></div><br />

	<div>
		<table style='width: 100%;'>
			<tr>
				<td><h3>Food Parcels in Last Year:</h3><br /><h4>Parcels packed: <?PHP echo $packedCountYear; ?><br />Parcels Exchanged: <?PHP echo $exchangedCountYear; ?></h4></td>
				<td><h3>Food Parcels since records began:</h3><br /><h4>Parcels packed: <?PHP echo $packedCountBeginning; ?><br />Parcels Exchanged: <?PHP echo $exchangedCountBeginning; ?></h4></td>
			</tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
			<tr>
				<td><h3>Food Parcels per week:</h3>
					<select name='packingdate' onchange="getParcelsPerWeek(this)">
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
				</td>
				<td><h3>Food Items last Week:</h3></td>
			</tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
            <tr>
				<td><span id='parcelsperweek'></span></td>
				<td><h4>Items Received: <?PHP echo $itemsIn; ?><br />Items given out: <?PHP echo $itemsOut; ?></h4></td>
            </tr>
		</table>
	</div>
<?PHP
    require_once('footer.php');
    ob_flush(); ?>
