<?php
ob_start(); // To hide header messages

require_once('header.php');
require_once('log.php');

if(!isset($_SESSION)) { // Starting session
    session_start();
}

if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], COUNTER) && !getAuth($_SESSION['user']['auth'], PACKER) && !getAuth($_SESSION['user']['auth'], TRUSTEE) && !getAuth($_SESSION['user']['auth'], ADMIN))) {
    redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
    die();
}
require_once('config.php'); // Including database information

$dbh = connect();

$query = $dbh->prepare("SELECT Name, id FROM FoodItem ORDER BY Name ASC");
if($query->execute()) {
    $ficount = $query->rowCount();
    $rowFI = $query->fetchAll();
} else {
    die('<h1>Error</h1><br /><h3>Unable to select food items from database.</h3>');
}

if(isset($_POST['foodin'])) { // Add item
    $query = $dbh->prepare("SELECT centralWarehouseName, id FROM Warehouse WHERE deleted = 0  ORDER BY centralWarehouseName");
    if($query->execute()) {
		$cwcount = $query->rowCount();
		$rowCW = $query->fetchAll();
    } else {
	die('<h1>Error</h1><br /><h3>Unable to select warehouses from database.</h3>');
    }
?>
	<div><h1>Food items</h1></div><br />

		<form action='fooditem.php' method='post'>
			<input type='hidden' name='mode' value='update'>
			<input type='hidden' name='donation' value='1'>
		<div><table style="width: 100%; text-align:center;">
	    <tr><td><h3>Warehouse</h3></td>
				<td><select name='warehouse'>
<?PHP foreach($rowCW as $cw)
echo '<option value=\''.$cw['id'].'\'>'.$cw['centralWarehouseName'].'</option>';?>
				</select></td>
	    </tr>
		<tr><td><h3>Donation Date (dd-mm-yyyy)</h3></td>
				<td><input type='text' size='20' value='<?PHP echo Date("d-m-Y"); ?>' name='donationdate' onblur="verifyDate(this)" maxlength='10'></td>
		</tr>
		<tr><td><h3>Donation Name</h3></td>
		<td><input type='text' size='20' value='Normal' name='donationname' maxlength='32'></td>
	    </tr>
			<tr><td colspan='<?PHP echo $count ?>'>&nbsp;</tr>
<?PHP
for($j = 0; $j < $ficount; $j++) {
    echo '<tr><td><h3>' . $rowFI[$j]['Name'] . '</h3></td>';
    echo '<td><input type=\'number\' size=\'20\' name=\'item['. $rowFI[$j]['id'] . ']\' maxlength=\'5\'></td>';
    echo '</tr>';
}
?>
			</table></div>
			<div><input class="form-input-button" type='submit' value='Add Items'></div>
		</form>
	<form action='fooditem.php'>
			<div><input class="form-input-button" type='submit' value='Back'></div>
		</form>
<?PHP
} else if(isset($_POST['stocktake'])) {
    if(isset($_POST['warehouse'])) {
	$warehouse = $_POST['warehouse'];

	$query = $dbh->prepare("SELECT s.quantity, s.idFoodItem, cw.centralWarehouseName, fi.Name FROM Store s, Warehouse cw, FoodItem fi WHERE fi.id = s.idFoodItem AND cw.id = idWarehouse AND idWarehouse = 1 ORDER BY fi.Name ASC");
	if($query->execute()) {
	    $rowStore = $query->fetchAll();
	} else {
	    die('<h1>Error</h1><br /><h3>Unable to select warehouses from database.</h3>');
	}
?>
			<div><h1>Stock take</h1></div><br />
			<div><h3><?PHP echo $rowStore[0]['centralWarehouseName']; ?></h3></div><br />

			<form action='fooditem.php' method='post'>
			<input type='hidden' name='mode' value='update'>
			<input type='hidden' name='stock' value='1'>
			<input type='hidden' name='warehouse' value='<?PHP echo $warehouse; ?>'>
		<div><table style="width: 70%; text-align:center;">

<?PHP
	$i = 0;
	for($j = 0; $j < $ficount; $j++) {
	    echo '<tr><td><h3>' . $rowFI[$j]['Name'] . '</h3></td>';
	    if(isset($rowStore[$i]['idFoodItem']) && $rowStore[$i]['idFoodItem'] == $rowFI[$j]['id']) {
		$value = $rowStore[$i]['quantity'];
		$i++;
	    } else {
		$value = '';
	    }
	    echo '<td><h3><input type=\'text\' size=\'10\' name=\'item['. $rowFI[$j]['id'] .']\' value=\'' . $value . '\' maxlength=\'5\'></h3></td>';
	    echo '</tr>';
	}
?>
			</table></div>
			<div><input class="form-input-button" type='submit' value='Update'></div>
			</form>
			<form action='fooditem.php'>
			<div><input class="form-input-button" type='submit' value='Back'></div>
			</form>
<?PHP
    } else {
	$query = $dbh->prepare("SELECT centralWarehouseName, id FROM Warehouse");
	if($query->execute()) {
	    $cwcount = $query->rowCount();
	    $rowCW = $query->fetchAll();
	} else {
	    die('<h1>Error</h1><br /><h3>Unable to select warehouses from database.</h3>');
	} ?>

	    <div><h1>Stock take</h1></div><br /><br />
	    <div><h1>Choose a warehouse:</h1></div><br />
			<form action='fooditem.php' method='post'>
	    <input type='hidden' name='stocktake' value='1'>
	    <div><select name='warehouse'>
<?PHP foreach($rowCW as $cw)
echo '<option value=\''.$cw['id'].'\'>'.$cw['centralWarehouseName'].'</option>';?>
			</select>
			<input class="form-input-button" type='submit' value='Stock take'></form></div>
<?PHP }
} else if(isset($_GET['mode']) && $_GET['mode'] == 'edititems') { // Edit items to remove add new one
?>
	<div><h1>Edit items</h1></div><br />
		<div><table style='text-align:center; width:100%;' id='tableitems'>
<?PHP
    for($i = 0; $i < $ficount; $i++) {
	echo '<tr><td><h3><span id=\'item'.$rowFI[$i]['id'].'\'>'.$rowFI[$i]['Name'].'</span></h3></td>';
	echo '<td><input class=\'form-input-button\' type=\'submit\' id=\'edititem'.$rowFI[$i]['id'].'\' value=\'Edit\' onclick=\'edititem('.$rowFI[$i]['id'].')\'></td>';
	//echo '<td><input class=\'form-input-button\' type=\'submit\' value=\'Remove\' onclick=\'removeitem('.$rowFI[$i]['id'].')\' disabled></td>';
    }
?>
		<tr><td colspan='3'>&nbsp;</td></tr>
		<tr><td><h3>New Item</h3></td><td><input type='text' name='newitem' id='newitem' maxlength='32'></td><td><input class='form-input-button' type='submit' value='Submit' onclick='newitem()'></td></tr>
		</table></div><div><span id='errorinserting'><br /></span></div>
	<form action='fooditem.php'>
			<div><input class="form-input-button" type='submit' value='Back'></div>
		</form>
<?PHP
} else if(isset($_POST['mode']) && $_POST['mode'] == 'update') {
    if(isset($_POST['donation'])) {
	$warehouse = $_POST['warehouse'];
	$name = strip_tags($_POST['donationname']);
	$date = date('Y-m-d', strtotime(strip_tags($_POST['donationdate'])));
	$total = 0;
	$donationItems = '';

	foreach($_POST['item'] as $id => $quantity) {
	    if($quantity != '' && intval($quantity) >= 0) {
		$query = $dbh->prepare("SELECT quantity FROM Store WHERE idWarehouse = " . $warehouse . " AND idFoodItem = " . $id);
		if($query->execute())
		    $item = $query->fetch();
		else
		    die('Unable to get store information from database');

		if($query->rowCount() > 0) { // If there is some quantity of this food item in the warehouse
		    $query = $dbh->prepare("UPDATE Store SET quantity = quantity + " . $quantity . " WHERE idWarehouse = " . $warehouse . " AND idFoodItem = " . $id);
		    if(intval($quantity) != 0) { // If the new quantity counted in is not 0, update the quantit
			$total += intval($quantity); // Storing the total donated
			$donationItems .= $id.'[BRK2]'.$quantity.'[BRK]'; // Storing the items donated for specifying on the donation
		    }
		} else { // If there is no quantity of this food already in the warehouse
		    if(intval($quantity) != 0) { // If the quantity is not 0, insert this item on the database with the new quantity
			$total += intval($quantity); // Storing the total donated
			$donationItems .= $id.'[BRK2]'.$quantity.'[BRK]'; // Storing the items donated for specifying on the donation
			$query = $dbh->prepare("INSERT INTO Store (quantity, idWarehouse, idFoodItem) VALUES (" . $quantity . ", " . $warehouse . ", " . $id . ")");
		    }
		}
		if(!$query->execute()) {
		    die('<h1>Error</h1><br /><h3>Unable to update store database.</h3>');
		}
	    } else if($quantity != '' && (!is_int($quantity) || intval($quantity) < 0)) {
		die('<h1>Error</h1><br /><h3>Invalid parameters for quantities.</h3>');
	    }
	}
	if($total > 0) {
	    $donationItems = substr($donationItems, 0, -5); // Removing the last [BRK]

	    $query = $dbh->prepare("INSERT INTO Donation (name, date, total, items, idWarehouse) VALUES (:n, :d, :t, :it, :idcw)");

	    if(!$query->execute(array(":n" => $name, ":d" => $date, ":t" => $total, ":it" => $donationItems, ":idcw" => $warehouse))) {
		die('<h1>Error</h1><br /><h3>Unable to update donation database.</h3>');
	    }
	    auditlog('Added items: Donation name: ' . $name . ', Date: ' . date('d-m-Y', strtotime(strip_tags($_POST['donationdate']))) . ', Warehouse id: ' . $warehouse);
	}
	redirect('fooditem.php', 'Database updated successfully.');
    } else if(isset($_POST['stock'])) {
	$warehouse = $_POST['warehouse'];

	foreach($_POST['item'] as $id => $quantity) {
	    if(intval($quantity) >= 0 || $quantity == '') {
		$query = $dbh->prepare("SELECT quantity FROM Store WHERE idWarehouse = " . $warehouse . " AND idFoodItem = " . $id);
		if($query->execute())
		    $rows = $query->fetch();
		else
		    die('Unable to get food item information from database');

		if($query->rowCount() > 0) {
		    if($quantity != '')
			$query = $dbh->prepare("UPDATE Store SET quantity = " . $quantity . " WHERE idWarehouse = " . $warehouse . " AND idFoodItem = " . $id);
		    else
			$query = $dbh->prepare("DELETE FROM Store WHERE idWarehouse = " . $warehouse . " AND idFoodItem = " . $id);
		} else {
		    if($quantity != '' && $quantity != 0)
			$query = $dbh->prepare("INSERT INTO Store (quantity, idWarehouse, idFoodItem) VALUES (" . $quantity . ", " . $warehouse . ", " . $id . ")");
		}
		if(!$query->execute()) {
		    die('Unable to update store database.');
		}
	    } else if($quantity != '' && (!is_int($quantity) || intval($quantity) < 0)) {
		die('<h1>Error</h1><br /><h3>Invalid parameters for quantities.</h3>');
	    }
	}
	auditlog('Added items: Stocktake, Warehouse id: ' . $warehouse);
	redirect('fooditem.php', 'Database updated successfully.');
    }
} else {
    $dbh = connect();

    $query = $dbh->prepare("SELECT centralWarehouseName, id FROM Warehouse WHERE deleted = 0 ORDER BY centralWarehouseName");
    if($query->execute()) {
	$cwcount = $query->rowCount();
	$rowCW = $query->fetchAll();
	auditlog('Viewed all items');
    } else {
	die('<h1>Error</h1><br /><h3>Unable to select warehouses from database.</h3>');
    }

    if(getAuth($_SESSION['user']['auth'], COUNTER) || getAuth($_SESSION['user']['auth'], ADMIN)) {
?>
		<div><h1>Food items</h1><form action='fooditem.php' method='get'><input type='hidden' name='mode' value='edititems'><input class="form-input-button" type='submit' value='Edit Items'></form></div><br /><br />
	<?PHP } ?>
	<div><h3>Stock levels: &nbsp;&nbsp;&nbsp;
	<input type='hidden' name='mode' value='edititems'>
	<input type="hidden" id="ajaxid" value="fooditem"/>
	<select name='name' id='name' onchange="showResult('fooditem')">
	    <option value=''>Name</option>
	    <?PHP for($i = 0; $i < $ficount; $i++) { ?>
		<option value='<?PHP echo $rowFI[$i]['id']; ?>'><?PHP echo $rowFI[$i]['Name']; ?></option>
	    <?PHP } ?>
	</select>

	<select name='location' id='location' onchange="showResult('fooditem')">
			<option value=''>Location</option>
	    <?PHP for($i = 0; $i < $cwcount; $i++) { ?>
		<option value='<?PHP echo $rowCW[$i]['id']; ?>'><?PHP echo $rowCW[$i]['centralWarehouseName']; ?></option>
			<?PHP } ?>
	</select></h3>

		<br /><div><span id="txtResult"></span></div><br />

	<?PHP if(getAuth($_SESSION['user']['auth'], COUNTER) || getAuth($_SESSION['user']['auth'], ADMIN)) { ?>
			<form action='fooditem.php' method='post'>
		<div>
				<input class="form-input-button" type='submit' name='foodin' value='Count food in'>
		<input class="form-input-button" type='submit' name='stocktake' value='Stock take'></div>
			</form>
<?PHP }
}
require_once('footer.php');
ob_flush(); ?>
