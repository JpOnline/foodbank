<?php
ob_start(); // To hide header messages

require_once('header.php');
require_once('log.php');

if(!isset($_SESSION)) { // Starting session
    session_start();
}

if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], DPSTAFF) && !getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], AGSTAFF))) {
    redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
    die();
}

require_once('config.php'); // Including database information

if(isset($_GET['mode']) && ($_GET['mode'] == 'newvoucher' || $_GET['mode'] == 'viewvoucher')) {
    if(!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], AGSTAFF)) {
		if($_GET['mode'] == 'newvoucher') {
		    redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
		    die();
		}
		$readonly = 'readonly = \'readonly\''; 
    } else {
		$readonly = '';
    }

    $dbh = connect();
    $exchanged = false;

    $query = $dbh->prepare("SELECT id, organisation, deleted FROM Agency ORDER BY organisation");
    if($query->execute()) {
		$agencyCount = $query->rowCount();
		$agencyRows = $query->fetchAll();
    } else {
		die('<h1>Unable to get agency information from database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
    }

    $query = $dbh->prepare("SELECT id, familyName, forename, address1, address2, postcode, town, deleted FROM Client ORDER BY familyName ASC");
    if($query->execute()) {
		$clientCount = $query->rowCount();
		$clientRows = $query->fetchAll();
		// Setting the address of the first client to show on the textarea
		$br = '&#13;&#10;';
		$voucherRow['clientAddr'] = $clientRows[0]['address1'].$br.$clientRows[0]['address2'].$br.$clientRows[0]['town'].$br.$clientRows[0]['postcode'];
    } else {
		die('<h1>Unable to get client information from database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
    }
    $query = $dbh->prepare("SELECT name, id, startingLetter FROM FoodParcelType WHERE edited = 0 ORDER BY startingLetter");
    if($query->execute()) {
		$FPTCount = $query->rowCount();
		$FPTRows = $query->fetchAll();
	}else {
		die('Unable to get food parcels from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
	}
    if($_GET['mode'] == 'viewvoucher' && $_GET['id']) {
		$editing = true;
		$id = $_GET['id'];
		$query = $dbh->prepare("SELECT * FROM Voucher WHERE id = :id");

		if($query->execute(array(":id" => $id))) {
		    if($query->rowCount() > 0) {
				$voucherRow = $query->fetch();

				$query = $dbh->prepare("SELECT * FROM NatureOfNeed WHERE idVoucher = :id ORDER BY nature ASC");
				if($query->execute(array(":id" => $id))) {
						$natureRows = $query->fetchAll();
						$natureCount = $query->rowCount();

						if($voucherRow['wasExchanged'] == '1' && $query->rowCount() > 0) {
						    $query = $dbh->prepare("SELECT DISTINCT E.pointOfIssue, E.pointOfIssueType, E.date, E.idVoucher, FP.referenceNumber, FP.id as idFoodParcel FROM Exchange E, FoodParcel FP WHERE E.idVoucher = :id AND FP.idVoucher = E.idVoucher ORDER BY FP.referenceNumber");

					    	if($query->execute(array(":id" => $id))) {
								if($query->rowCount() > 0) {
								    $exchanged = true;
								    $exchangeRows = $query->fetchAll();
								    $exchangeCount = $query->rowCount();
								    if($exchangeRows[0]['pointOfIssueType'] == 'agency') {
										$query = $dbh->prepare("SELECT id, deleted, organisation as name FROM Agency ORDER BY name");
										if($query->execute()) {
										    $rowsLocation = $query->fetchAll();
										    $locationCount = $query->rowCount();
										} else {
										    die('Unable to get agency information from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
										}
								    } else if($exchangeRows[0]['pointOfIssueType'] == 'dp') {
										$query = $dbh->prepare("SELECT id, deleted,  distributionPointName as name FROM DistributionPoint ORDER BY name");
										if($query->execute()) {
										    $rowsLocation = $query->fetchAll();
										    $locationCount = $query->rowCount();
										} else {
										    die('Unable to get distribution point information from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
										}
								    } else if($exchangeRows[0]['pointOfIssueType'] == 'cw') {
										$query = $dbh->prepare("SELECT id, deleted, centralWarehouseName as name FROM Warehouse ORDER BY name");
										if($query->execute()) {
										    $rowsLocation = $query->fetchAll();
										    $locationCount = $query->rowCount();
										} else {
										    die('Unable to get warehouse information from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
										}
								    }
								}
								auditlog('Viewed voucher. Id: ' . $voucherRow['id']);
						    } else {
							die('<h1>Invalid ID.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
						    }
						}
				}
		    } else {
			die('<h1>Invalid ID.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		    }
		} else {
		    die('<h1>Unable to get voucher information from database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		}
    } else if($_GET['mode'] == 'newvoucher') {
	$editing = false;
    } else {
		die('<h1>Invalid ID.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
    } ?>
		<div> 
		    <form action='voucher.php' method='post' onsubmit="return validateVoucherForm()">
			<input type='hidden' name='mode' value='update'>
			    <?PHP if(!$editing) { ?>
				<input type='hidden' name='adding' value='1'>
				<div><h1>New Voucher</h1></div><br /><br />
			    <?PHP } else { ?>
				<input type='hidden' name='id' value='<?PHP echo $id; ?>'>
				<input type='hidden' name='editing' value='1'>
				<div><h1>View Voucher</h1></div><br /><br />
			    <?PHP } ?>
			    <div> 
				<table style="width: 100%;">
				    <tr>
					<td><h3>Agency Referrer</h3></td>
					<td>
					    <?PHP if($readonly == '') { ?>
							<select name='agencyreferrer' id='agencyreferrer'>
					    <?PHP } ?>
					    <?PHP for($i = 0; $i < $agencyCount; $i++) { ?>
							<?PHP if($editing && $agencyRows[$i]['id'] == $voucherRow['idAgency']) {
							    $selected = 'selected=\'selected\'';
							    $agencyName = $agencyRows[$i]['organisation'];
							} else {
							    $selected = '';
							}
							if($readonly == '') { 
								if(!$editing){
									if($agencyRows[$i]['deleted'] != 1){ ?>
								   		<option value='<?PHP echo $agencyRows[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $agencyRows[$i]['organisation']; ?></option>
									<?PHP }
								}else {?>
									<option value='<?PHP echo $agencyRows[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $agencyRows[$i]['organisation']; ?></option>
								<?PHP }
							}
					    }
					    if($readonly == '') { ?>
							</select>
					    <?PHP } else { ?>
							<input type='hidden' name='agencyreferrer' value='<?PHP echo $voucherRow['idAgency']; ?>'><h5><?PHP echo $agencyName; ?></h5>
					    <?PHP } ?>
					</td>
				    </tr>
					<tr>
					    <td style="line-height:130%"><h3>Agency Voucher Reference (optional)</h3></td>
					    <td><input type='text' name='agvoucherref' value='<?PHP if($editing) echo $voucherRow['agencyVoucherReference']; ?>' maxlength='20' id='opt' <?PHP echo $readonly; ?>></td>
					</tr>
					<tr>
					    <td><h3><br />Agency Contact Name (optional)</h3></td>
					    <td><input type='text' name='agContactName' value='<?PHP if($editing) echo $voucherRow['agencyContactName']; ?>' maxlength='20' id='opt' <?PHP echo $readonly; ?>></td>
					</tr>
				    <tr>
					<td><h3>Date Voucher Issued</h3></td>
					<td><input type='text' id='datevoucherissued' name='datevoucherissued' value='<?PHP if($editing) echo date('d-m-Y', strtotime($voucherRow['dateVoucherIssued'])); else echo date('d-m-Y'); ?>' maxlength='10' <?PHP echo $readonly; ?>></td>
				    </tr>
					<tr><td colspan='2'>&nbsp;</td></tr>
					<tr>
					    <td><h3>Client Full Name</h3></td>
					    <td valign='middle'>
						<?PHP if($readonly == '') { ?>
						    <h5><select name='client' id='clientFullName' onchange="getClientAddress(this)" <?PHP echo $readonly; ?>>
						<?PHP }
						for($i = 0; $i < $clientCount; $i++) { 
						    if($editing && $clientRows[$i]['id'] == $voucherRow['idClient']) {
							$selected = 'selected';
							$br = '&#13;&#10;';
							$voucherRow['clientAddr'] =  $clientRows[$i]['address1'].$br. $clientRows[$i]['address2'].$br. $clientRows[$i]['town'].$br. $clientRows[$i]['postcode'];
							$clientName = $clientRows[$i]['familyName'] . ', ' . $clientRows[$i]['forename'];
						    } else {
							$selected = '';
						    }
						    if($readonly == '') {
						    	if($editing){?>
									<option value='<?PHP echo $clientRows[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $clientRows[$i]['familyName'] . ', ' . $clientRows[$i]['forename']; ?></option>			
								<?PHP }else{
									if($clientRows[$i]['deleted'] != 1){?>
										<option value='<?PHP echo $clientRows[$i]['id']; ?>' <?PHP echo $selected; ?>><?PHP echo $clientRows[$i]['familyName'] . ', ' . $clientRows[$i]['forename']; ?></option>
								    <?PHP }
								}
							}
						}
						if($readonly == '') { ?>
							</select>&nbsp;&nbsp;&nbsp; 
							<a href="client.php?mode=add">
							   <input type="button" value="Add New Client" />
							</a>	
						<?PHP } else { ?>
						    <input type='hidden' name='client' value='<?PHP echo $voucherRow['idClient']; ?>'><h5><?PHP echo $clientName; ?></h5>
						<?PHP } ?>
					    </td>
				    </tr>
				    <tr>
					<td><h3>Client Full Address</h3></td>
					<td><textarea id='clientaddr' value='1' rows='5' style='resize:none; width:55%;' readonly='readonly'><?PHP echo $voucherRow['clientAddr']; ?></textarea></td>
				    </tr>
				    <tr><td colspan='2'>&nbsp;</td></tr>
				    <tr>
					<td><h3>Number of Adults</h3></td>
					<td><input type='text' name='noadults' value='<?PHP if($editing) echo $voucherRow['numberOfAdults']; ?>' maxlength='2' <?PHP echo $readonly; ?>></td>
				    </tr>
				    <tr>
					<td><h3>Number of Children</h3></td>
					<td><input type='text' name='nochildren' value='<?PHP if($editing) echo $voucherRow['numberOfChildren']; ?>' maxlength='2' <?PHP echo $readonly; ?>></td>
				    </tr>
				    <tr><td colspan='2'>&nbsp;</td></tr>
				    <tr>
					<td style="line-height:130%"><h3>How is the Agency helping the client?</h3></td>
					<td><textarea name='helping' id='helping' value='1' rows='5' style='resize:none; width:100%;' <?PHP echo $readonly; ?>><?PHP if($editing) echo $voucherRow['helping']; ?></textarea></td>
				    </tr>
				    <tr><td colspan='2'>&nbsp;</td></tr>
				    <tr>
					<td style="line-height:110%"><h3>Nature of Need</h3><?PHP if($readonly == '') { ?><br /><h5>If other, please specify.<br /><br />Please tick one box only.</h5><?PHP } ?></td>
					<td>
					    <?PHP
						$nonValue = array('asylum', 'benefitschanged', 'benefitsstopped', 'childholidaymeals', 'sanctioned', 'debt', 'familycrisis', 'sickness', 'sofasurfing', 'streethomeless', 'unemployed', 'waitingforbenefittostart', 'zother');
						$non = array('Asylum', 'Benefits Changed', 'Benefits Stopped', 'Child Holiday Meals', 'Sanctioned', 'Debt', 'Family Crisis', 'Sickness', 'Sofa Surfing', 'Street Homeless', 'Unemployed', 'Waiting for Benefit to Start', 'Other');
						$j = 0;
						$otherCheck = '';
						for($i = 0; $i < count($non); $i++) { ?>
						    <?PHP
						    if(strpos($nonValue[$i], 'zother') !== false) {
							$otherCheck = 'onclick=\'checkothernature()\' id=\'othernatureinput\'';
						    }
						    if($editing && ($j < $natureCount) && strpos($natureRows[$j]['nature'], $nonValue[$i]) !== false) {
							if(strpos($natureRows[$j]['nature'], 'zother') !== false) {
							    $otherDetails = substr($natureRows[$j]['nature'], 8);
							}
						    $checked = 'checked=\'checked\'';
						    $j++;
						    } else {
							$checked = '';
						    }
						    if($readonly == '') {?>
							<h5><input type='checkbox' name='natureofneed[]' value='<?PHP echo $nonValue[$i]; ?>' <?PHP echo $otherCheck;?> <?PHP echo $checked;?>>
						    <?PHP } else if($readonly != '' && $checked != '') { ?>
							<h5><input type='hidden' name='natureofneed[]' value='<?PHP echo $nonValue[$i]; ?>'>
						    <?PHP }
						    if(($readonly != '' && $checked != '') || $readonly == '') {
							echo $non[$i] . '</h5><br />';
						    }
						} ?>
						<span id='othernature' <?PHP if(!isset($otherDetails)) echo 'style=\'display:none;\'';?>>
						    <textarea type='text' id='othernaturefield' name='othernature' rows='5'><?PHP if(isset($otherDetails)) echo $otherDetails; else echo 'Details';?></textarea>
						</span> 
					</td> 
				    </tr>
				    <tr><td colspan='2'><br /><hr></td></tr> 
				</table>
				<?PHP if(!$exchanged) { ?>
				    <table style="width: 100%;">
					<tr>
					    <td><h3>Would you like to exchange this voucher now?</h3></td>
					    <td width='30%'><input type='checkbox' name='exchangenow' id='exchangenow' onchange="showExchange(this)"></td>
					</tr>
					<tr><td colspan='2'><br /></td></tr> 
				    </table>
				<?PHP } ?>
				<?PHP if($exchanged && !getAuth($_SESSION['user']['auth'], ADMIN))
				    $readonly =  'readonly=\'readonly\'';
				else
				    $readonly =  '';
				?>
				<span id='exchange' <?PHP if(!$exchanged) echo 'style=\'display:none;\''; ?>>
				<table style="width: 100%;">
				    <tr>
					<td><h3>Date Food Parcel(s) given</h3></td>
					<td><input type='date' id='dateGiven' name='dategiven' value='<?PHP if($editing && $exchanged) echo date('Y-m-d', strtotime($exchangeRows[0]['date'])); else echo date('Y-m-d'); ?>' <?PHP if(!$exchanged) echo 'disabled'; ?> maxlength='10' <?PHP echo $readonly; ?>></td>
				    </tr>
				    <tr><td colspan='2'>&nbsp;</td></tr>
				    <tr>
					<td><h3>Point of Issue</h3></td>
					<td> 
					    <?PHP if($readonly == '') { ?>
							<select id='placestype' name='placestype' onchange="getplaces() " <?PHP if(!$exchanged) echo 'disabled'; ?>>
						    <option value=''>Select Location</option>
						    <option value='agency' <?PHP if($exchanged && $exchangeRows[0]['pointOfIssueType'] == 'agency') echo 'selected=\'selected\'' ?>>Agency</option>
						    <option value='dp' <?PHP if($exchanged && $exchangeRows[0]['pointOfIssueType'] == 'dp') echo 'selected=\'selected\'' ?>>Distribution Point</option>
						    <option value='cw' <?PHP if($exchanged && $exchangeRows[0]['pointOfIssueType'] == 'cw') echo 'selected=\'selected\'' ?>>Central Warehouse</option> 
							</select><br />
							<span id='getplaces'><select name='location' id='location' <?PHP if(!$exchanged) echo 'disabled'; ?>>
					    <?PHP } else { ?>
							<?PHP if($exchangeRows[0]['pointOfIssueType'] == 'agency') { ?>
							    <option value='agency' <?PHP if($exchangeRows[0]['pointOfIssueType'] == 'agency') echo 'selected=\'selected\'' ?>>Agency</option>
							    <?PHP if($readonly != '') { ?>
								    <input type='hidden' name='placestype' value='agency'>
							    <?PHP } ?>
						    <?PHP } else if($exchangeRows[0]['pointOfIssueType'] == 'dp') { ?>
								<option value='dp' <?PHP if($exchangeRows[0]['pointOfIssueType'] == 'dp') echo 'selected=\'selected\'' ?>>Distribution Point</option>
							    <?PHP if($readonly != '') { ?>
								<input type='hidden' name='placestype' value='dp'>
							    <?PHP } ?>
						    <?PHP } else if($exchangeRows[0]['pointOfIssueType'] == 'cw') { ?>
								<option value='cw' <?PHP if($exchangeRows[0]['pointOfIssueType'] == 'cw') echo 'selected=\'selected\'' ?>>Central Warehouse</option></select><br />
								<?PHP if($readonly != '') { ?>
								    <input type='hidden' name='placestype' value='cw'>
								<?PHP } ?>
						    <?PHP } ?>
					    <?PHP } ?>
					    <?PHP for($i = 0; $i < $locationCount; $i++) {
							$selected = ($exchanged && $rowsLocation[$i]['id'] == $exchangeRows[0]['pointOfIssue']) ? 'selected=\'selected\'' : ''; ?>
							<?PHP if($readonly == '' || ($readonly != '' && $selected != '')) { ?>
								<option value='<?PHP echo $rowsLocation[$i]['id'] ?>' <?PHP echo $selected; ?>><?PHP echo $rowsLocation[$i]['name']; ?></option>
									<?PHP if($readonly != '') {  ?>
									<input type='hidden' name='location' value='<?PHP echo $rowsLocation[$i]['id'] ?>'>
								<?PHP } ?>
							<?PHP } ?>
					    <?PHP } ?>
					    <?PHP if($readonly == '') { ?>
							</select></span>
					    <?PHP } ?>
					</td>
				    </tr>
				    <tr><td colspan='2'>&nbsp;</td></tr>
				</talbe>

<!-- new part -->
				<table style="width: 100%;">
				    <br><br>
				    <td><h3>Parcels to be given out</h3><br /><h5>Ctrl+click to select more than one.</h5><br /></td>
				    <?php
				    $j = 0;
				    for($k=0; $k< $FPTCount; $k++){
					$query = $dbh->prepare("SELECT referenceNumber, FoodParcel.id, wasGiven, name FROM FoodParcel, FoodParcelType WHERE idFPType = FoodParcelType.id AND name = :n ORDER BY referenceNumber");
					if($query->execute(array(":n" => $FPTRows[$k]['name']))) {
					    $fpCount = $query->rowCount();
					    $fpRows = $query->fetchAll();
					} else {
						    die('Unable to get food parcel types from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
					}
					if($editing){
					    $query = $dbh->prepare("SELECT referenceNumber FROM FoodParcel WHERE idVoucher = :id and idFPType = :fptid");
					    if($query->execute(array(":id" => $voucherRow['id'],":fptid" => $FPTRows[$k]['id']))) {
							$fp2Count = $query->rowCount();
							$fp2Rows = $query->fetchAll();
					    } else {
							die('Unable to get food parcel types from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
					    }
					}
				    ?>
					<tr>
					    <?PHP// if($readonly == '') { ?> <!-- isso aqui vai permitir mostrar  somente na area de trocar o voucher e não na visualização do voucher trocado  -->
						<td><h3><?PHP echo $FPTRows[$k]['name'] ?></h3></td>
						<td><input type='number' min = "0" step = "1" name='amount' pattern="\d+" id= '<?PHP echo $FPTRows[$k]['name'] ?>' value='<?PHP if($editing) echo $fp2Count ;else echo 0 ?>' maxlength='3' <?PHP echo $readonly; ?>></td>
					    <?PHP// } ?>
					    <td><?PHP if($readonly == '') { ?>
						<select id='foodparcels' name='foodparcels[]' style='width:100%;' multiple='multiple' >
					    <?PHP } ?>

					    <?PHP

					     for($i = 0; $i < $fpCount; $i++) {
					     	
							if($fpRows[$i]['wasGiven'] == '0' || ($exchanged && $fpRows[$i]['id'] == $exchangeRows[$j]['idFoodParcel'])) {
								
							    if($exchanged && $fpRows[$i]['id'] == $exchangeRows[$j]['idFoodParcel']) {
									$selected = 'selected=\'selected\'';
									if($j < $exchangeCount-1) $j++;
							    } else {
									$selected = '';
							    } 
							    if($readonly == '' || ($readonly != '' && $selected != '')) { ?>
									<option value='<?PHP echo $fpRows[$i]['id'] ?>' <?PHP echo $selected; ?> > <?PHP echo $fpRows[$i]['referenceNumber'] . ' - ' . $fpRows[$i]['name']; ?></option>
									<?PHP if($readonly != '') { ?>
									    <input type='hidden' name='foodparcels[]" ?>' value='<?PHP echo $fpRows[$i]['id'] ?>'>
									<?PHP } 
							    }
						    }
						}
					    if($readonly == '') { ?>
						</select>
					    <?PHP } ?>
					    </td>
					</tr>

				    <?PHP }	?>
				    </tr>
				</table>
				<tr>
				    <?PHP
				    if($editing){
					$query = $dbh->prepare("SELECT explanation FROM Exchange WHERE idVoucher = :id");
					if($query->execute(array(":id" => $voucherRow['id']))) {
					    $eCount = $query->rowCount();
					    $eRows = $query->fetchAll();
					} else {
					    die('Unable to get food parcel types from database.<div><form action=\'voucher.php\'><input class=\'form-input-button\'  type=\'submit\' value=\'Back\'></form></div>');
					}
				    }?>
				    <br></br>
				    <br><td><h3>Explanation why giving more parcels</h3></td></br>
				    <br><td><h3>than the amout of persons in a the voucher:</h3></td></br>
				    <td><textarea name='explanation' id='explanation' value='1' rows='5' style='resize:none; width:100%;' <?PHP echo $readonly; ?>><?PHP if($readonly != '') { if($editing) { echo $eRows[0]['explanation'];}} ?></textarea></td>
				</tr>
				    <div><input class="form-input-button" type='submit' value='Submit' id='submit'></div>
				</span>
				</table> <!-- I don't know what's this closing-->
			    </div>
			<div><input class="form-input-button" type='submit' value='Submit And Print' id='submitAndPrint' onclick='printIssuedVoucher()'></div> 
		    </form> 
		</div>
		<form><input class="form-input-button" type='button' value='Back' onclick="window.history.back()"></form>


	<!-- new part end -->



	<?PHP
} else if(isset($_POST['mode']) && $_POST['mode'] == 'update') {
    $agency = $_POST['agencyreferrer'];
    $agencyRef = $_POST['agvoucherref'];
    $agContactName = $_POST['agContactName'];
    $dateIssued = date('Y-m-d', strtotime(strip_tags($_POST['datevoucherissued'])));
    $client = $_POST['client'];
    $noadults = $_POST['noadults'];
    $nochildren = $_POST['nochildren'];
    $natureofneed = $_POST['natureofneed'];
   	$explanation = $_POST['explanation'];
    $helping = strip_tags($_POST['helping']);
    if(isset($_POST['othernature']))
	$othernature = strip_tags($_POST['othernature']);

    if(isset($_POST['editing'])) {
	$dbh = connect();
	$editing = true;
	$id = $_POST['id'];
	$redirectmsg = 'Updated';

	$query = $dbh->prepare("UPDATE Voucher SET idAgency = :a, dateVoucherIssued = :dvi, idClient = :c, numberOfAdults = :na, numberOfChildren = :noc, helping = :h, agencyVoucherReference = :avr, agencyContactName = :acn WHERE id = :id");

	if($query->execute(array(":a" => $agency, ":dvi" => $dateIssued, ":c" => $client, ":na" => $noadults, ":noc" => $nochildren, ":h" => $helping, ":avr" => $agencyRef, ":acn" => $agContactName, ":id" => $id))) {
	    $query = $dbh->prepare("DELETE FROM NatureOfNeed WHERE idVoucher = :id");

	    if($query->execute(array(":id" => $id))) {
		for($i = 0; $i < count($natureofneed); $i++) {
		    $query = $dbh->prepare("INSERT INTO NatureOfNeed (nature, idVoucher) VALUES (:n, :idv)");

		    if($natureofneed[$i] == 'zother') {
			if($othernature != '') {
			    $natureofneed[$i] .= ', ' . $othernature;
			    if(!$query->execute(array(":n" => $natureofneed[$i], ":idv" => $id))) {
				die('<h1>Unable to update nature of need database (insert).</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
			    }
			}
		    } else {
			if(!$query->execute(array(":n" => $natureofneed[$i], ":idv" => $id))) {
			    die('<h1>Unable to update nature of need database (insert).</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
			}
		    }
		}
	    } else {
		die('<h1>Unable to update nature of need database (remove).</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
	    }
	} else {
	    die('<h1>Unable to update voucher database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
	}
    } else {
		$dbh = connect();
		$editing = false;
		$redirectmsg = 'Created';

		$query = $dbh->prepare("INSERT INTO Voucher (idAgency, dateVoucherIssued, idClient, numberOfAdults, numberOfChildren, helping, wasExchanged, agencyVoucherReference, agencyContactName) VALUES (:a, :dvi, :c, :na, :noc, :h, false, :avr, :acn)");

		if($query->execute(array(":a" => $agency, ":dvi" => $dateIssued, ":c" => $client, ":na" => $noadults, ":noc" => $nochildren, ":h" => $helping, ":avr" => $agencyRef, ":acn" => $agContactName))) {
		    $query = $dbh->prepare("SELECT MAX(id) FROM Voucher");

		    if($query->execute()) {
				$row = $query->fetch();
				$id = $row['MAX(id)'];
				for($i = 0; $i < count($natureofneed); $i++) {
				    $query = $dbh->prepare("INSERT INTO NatureOfNeed (nature, idVoucher) VALUES (:n, :idv)");

				    if($natureofneed[$i] == 'zother') {
						$natureofneed[$i] .= ', ' . $othernature;
				    }
				    if(!$query->execute(array(":n" => $natureofneed[$i], ":idv" => $id))) {
						die('<h1>Unable to update nature of need database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
				    }
				}
		    } else {
				die('<h1>Unable to get voucher information from database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		    }
		} else {
		    die('<h1>Unable to update voucher database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		}
    }
    if(isset($_POST['exchangenow']) || (isset($_POST['dategiven']) && isset($_POST['editing']))) {
		$exchanging = true;
		$dategiven = date('Y-m-d', strtotime(strip_tags($_POST['dategiven'])));
		$typelocation = $_POST['placestype'];





//new part
		if (empty($_POST['location'])) {
			die('<h1>Unable to exchange voucher the locations is missing.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		}
		$location = $_POST['location'];
		if (empty($_POST['foodparcels'])) {
			die('<h1>Unable to exchange voucher the parcels are missing.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		}
		$foodparcels = $_POST['foodparcels'];
//new part end




		$query = $dbh->prepare("SELECT wasExchanged FROM Voucher WHERE id = :id");

		if($query->execute(array(":id" => $id))) {
		    $row = $query->fetch();
		    if($row['wasExchanged'] == 0) {
				$logmsg = 'Exchanged';
				$query = $dbh->prepare("UPDATE Voucher SET wasExchanged = 1 WHERE id = :id");
				if(!$query->execute(array(":id" => $id))) {
				    die('<h1>Unable to update voucher database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
				}
				$query = $dbh->prepare("INSERT INTO Exchange (pointOfIssue, pointOfIssueType, date, idVoucher, explanation) VALUES (:poi, :poit, :d, :idv, :e)");
			} else {
				$logmsg = 'Updated exchanged';
				// Unsetting all the food parcels from this voucher
				$query = $dbh->prepare("UPDATE FoodParcel SET wasGiven = 0, idVoucher = 0 WHERE idVoucher = :idv");
				if(!$query->execute(array(":idv" => $id))) {
				    die('<h1>Unable to update food parcel database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
				}
				$query = $dbh->prepare("UPDATE Exchange SET pointOfIssue = :poi, pointOfIssueType = :poit, date = :d, explanation = :e WHERE idVoucher = :idv");
			}

		    if($query->execute(array(":poi" => $location, ":poit" => $typelocation, ":d" => $dategiven, ":idv" => $id, ":e" => $explanation))) {
				for($i = 0; $i < count($foodparcels); $i++) {
				    $query = $dbh->prepare("UPDATE FoodParcel SET wasGiven = 1, idVoucher = :idv WHERE id = :idfp");
				    if(!$query->execute(array(":idv" => $id, ":idfp" => $foodparcels[$i]))) {
						die('<h1>Unable to update food parcel database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
				    }
				}
		    } else {
				die('<h1>Unable to update exchange database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		    }
		} else {
		    die('<h1>Unable to get voucher information from database.</h1><div><form action=\'voucher.php\'><input class=\'form-input-button\' type=\'submit\' value=\'Back\'></form></div>');
		}
		auditlog($logmsg . ' voucher. Id: ' . $id);
    } else {
		auditlog($redirectmsg . ' voucher. Id: ' . $id);
    }
    redirect('voucher.php', '<h1>Voucher '.strtolower($redirectmsg).' successfully.</h1>');





}else if(isset($_GET['mode']) && $_GET['mode'] == 'viewallexchanged') {
    $dbh = connect();

    $query = $dbh->prepare("SELECT DISTINCT E.date, E.pointOfIssueType, E.pointOfIssue, E.idVoucher, C.forename, C.familyName FROM Exchange E, Voucher V, Client C WHERE E.idVoucher = V.id AND V.idClient = C.id ORDER BY E.date DESC, V.id DESC");

    if($query->execute()) {
	$voucherExRows = $query->fetchAll();
	$voucherExCount = $query->rowCount();

		for($i = 0; $i < $voucherExCount; $i++) {
		    if($voucherExRows[$i]['pointOfIssueType'] == 'agency') {
				$query = $dbh->prepare("SELECT organisation FROM Agency where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['organisation'];
				} else {
				    die('<h1>Unable to get agency information from database.</h1>');
				}
		    } else if($voucherExRows[$i]['pointOfIssueType'] == 'dp') {
				$query = $dbh->prepare("SELECT distributionPointName FROM DistributionPoint where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['distributionPointName'];
				} else {
				    die('<h1>Unable to get distribution point information from database.</h1>');
				}
		    } else if($voucherExRows[$i]['pointOfIssueType'] == 'cw') {
				$query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['centralWarehouseName'];
				} else {
				    die('<h1>Unable to get warehouse information from database.</h1>');
				}
		    }
		}
    } else {
		die('<h1>Unable to get voucher information from database.</h1>');
    }
    auditlog('Viewed all vouchers exchanged.');
?>
	<div><h1>Vouchers</h1></div><br /><br />
	<div><h3>Vouchers exchanged</h3></div><br />
	<div><table style="width: 100%; text-align:center;">
	    <thead>
				<td><h3>ID</h3></td>
				<td><h3>Date given</h3></td>
		<td><h3>Point of Issue</h3></td>
		<td><h3>Client</h3></td>
		<td>&nbsp;</td>
	    </thead>
	    <?PHP for($i = 0; $i < $voucherExCount; $i++) { ?>
		<tr>
					<td><?PHP echo $voucherExRows[$i]['idVoucher']; ?></td>
		    <td><?PHP echo date('d-m-Y', strtotime($voucherExRows[$i]['date'])); ?></td>
		    <td><?PHP echo $voucherExRows[$i]['location']; ?></td>
		    <td><?PHP echo $voucherExRows[$i]['forename'] . ' ' . $voucherExRows[$i]['familyName']; ?></td>
		    <td><form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $voucherExRows[$i]['idVoucher']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
		</tr>
	    <?PHP } ?>
	</table></div>
	<form><div><input class="form-input-button" type='submit' value='Back'></div></form>
<?PHP
} else if(isset($_GET['mode']) && $_GET['mode'] == 'viewallissued') {
    $dbh = connect();
    $query = $dbh->prepare("SELECT V.dateVoucherIssued, V.id, A.organisation, C.forename, C.familyName FROM Agency A, Voucher V, Client C WHERE V.wasExchanged = 0 AND V.idClient = C.id AND V.idAgency = A.id ORDER BY V.dateVoucherIssued DESC, V.id DESC");

    if($query->execute()) {
		$voucherIssuedRows = $query->fetchAll();
		$voucherIssuedCount = $query->rowCount();
    } else {
		die('<h1>Unable to get voucher information from database.</h1>');
    }
    auditlog('Viewed all vouchers issued.');
?>
	<div><h1>Vouchers</h1></div><br /><br />
	<div><h3>Vouchers issued</h3></div><br />
		<div><table style="width: 100%; text-align:center;">
		<thead>
				<td><h3>ID</h3></td>
				<td><h3>Date voucher issued</h3></td>
			<td><h3>Agency Referrer</h3></td>
			<td><h3>Client</h3></td>
			<td>&nbsp;</td>
		</thead>
	    <?PHP for($i = 0; $i < $voucherIssuedCount; $i++) { ?>
				<tr>
					<td><?PHP echo $voucherIssuedRows[$i]['id']; ?></td>
					<td><?PHP echo date('d-m-Y', strtotime($voucherIssuedRows[$i]['dateVoucherIssued'])); ?></td>
					<td><?PHP echo $voucherIssuedRows[$i]['organisation']; ?></td>
					<td><?PHP echo $voucherIssuedRows[$i]['forename'] . ' ' . $voucherIssuedRows[$i]['familyName']; ?></td>
					<td><form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $voucherIssuedRows[$i]['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
				</tr>
			<?PHP } ?>
	</table></div>
	<form><div><input class="form-input-button" type='submit' value='Back'></div></form>
<?PHP
} else {
    $dbh = connect();

    $query = $dbh->prepare("SELECT DISTINCT E.date, E.pointOfIssueType, E.pointOfIssue, E.idVoucher, C.forename, C.familyName FROM Exchange E, Voucher V, Client C WHERE E.idVoucher = V.id AND V.idClient = C.id ORDER BY E.date DESC, V.id DESC LIMIT 0,5");

    if($query->execute()) {
		$voucherExRows = $query->fetchAll();
		$voucherExCount = $query->rowCount();

		for($i = 0; $i < $voucherExCount; $i++) {
		    if($voucherExRows[$i]['pointOfIssueType'] == 'agency') {
				$query = $dbh->prepare("SELECT organisation FROM Agency where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['organisation'];
				} else {
				    die('<h1>Unable to get agency information from database.</h1>');
				}
		    } else if($voucherExRows[$i]['pointOfIssueType'] == 'dp') {
				$query = $dbh->prepare("SELECT distributionPointName FROM DistributionPoint where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['distributionPointName'];
				} else {
				    die('<h1>Unable to get distribution point information from database.</h1>');
				}
		    } else if($voucherExRows[$i]['pointOfIssueType'] == 'cw') {
				$query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse where id = :id");

				if($query->execute(array(":id" => $voucherExRows[$i]['pointOfIssue']))) {
				    $row = $query->fetch();
				    $voucherExRows[$i]['location'] = $row['centralWarehouseName'];
				} else {
				    die('<h1>Unable to get warehouse information from database.</h1>');
				}
		    }
		}
    } else {
		die('<h1>Unable to get voucher information from database.</h1>');
    }

    $query = $dbh->prepare("SELECT V.dateVoucherIssued, V.id, A.organisation, C.forename, C.familyName FROM Agency A, Voucher V, Client C WHERE V.wasExchanged = 0 AND V.idClient = C.id AND V.idAgency = A.id ORDER BY V.dateVoucherIssued DESC, V.id DESC LIMIT 0,5");

    if($query->execute()) {
		$voucherIssuedRows = $query->fetchAll();
		$voucherIssuedCount = $query->rowCount();
    } else {
		die('<h1>Unable to get voucher information from database.</h1>');
    }
    auditlog('Viewed all vouchers.');
?>
	<div><h1>Vouchers</h1></div><br />
    <?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], AGSTAFF)) {?>
			<form action='voucher.php' method='get'><div><input type='hidden' name='mode' value='newvoucher'><input class="form-input-button" type='submit' value='Create new voucher'></div></form><br /><hr><br />
		<?PHP } ?>
	<div><h3>Vouchers exchanged</h3></div><br />
		<div><table style="width: 100%; text-align:center;">
		<thead>
		<td><h3>ID</h3></td>
			<td><h3>Date given</h3></td>
			<td><h3>Point of Issue</h3></td>
			<td><h3>Client</h3></td>
			<td>&nbsp;</td>
	    </thead>
	    <?PHP for($i = 0; $i < $voucherExCount; $i++) { ?>
		<tr>
					<td><?PHP echo $voucherExRows[$i]['idVoucher']; ?></td>
				<td><?PHP echo date('d-m-Y', strtotime($voucherExRows[$i]['date'])); ?></td>
				<td><?PHP echo $voucherExRows[$i]['location']; ?></td>
				<td><?PHP echo $voucherExRows[$i]['forename'] . ' ' . $voucherExRows[$i]['familyName']; ?></td>
				<td><form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $voucherExRows[$i]['idVoucher']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
			</tr>
	    <?PHP } ?>
	</table></div>
	<form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewallexchanged'><input class="form-input-button" type='submit' value='View all exchanged vouchers'></form><br /><hr><br />
	<div><h3>Vouchers issued</h3></div><br />
		<div><table style="width: 100%; text-align:center;">
		<thead>
		<td><h3>ID</h3></td>
			<td><h3>Date voucher issued</h3></td>
		    <td><h3>Agency Referrer</h3></td>
			<td><h3>Client</h3></td>
				<td>&nbsp;</td>
		</thead>
			<?PHP for($i = 0; $i < $voucherIssuedCount; $i++) { ?>
				<tr>
					<td><?PHP echo $voucherIssuedRows[$i]['id']; ?></td>
					<td><?PHP echo date('d-m-Y', strtotime($voucherIssuedRows[$i]['dateVoucherIssued'])); ?></td>
					<td><?PHP echo $voucherIssuedRows[$i]['organisation']; ?></td>
					<td><?PHP echo $voucherIssuedRows[$i]['forename'] . ' ' . $voucherIssuedRows[$i]['familyName']; ?></td>
					<td><form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewvoucher'><input type='hidden' name='id' value='<?PHP echo $voucherIssuedRows[$i]['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></td>
				</tr>
			<?PHP } ?>
	</table></div>
		<form action='voucher.php' method='get'><input type='hidden' name='mode' value='viewallissued'><input class="form-input-button" type='submit' value='View all issued vouchers'></form>
<?PHP }
require_once('footer.php');
ob_flush(); ?> 
