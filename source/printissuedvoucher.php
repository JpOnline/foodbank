<html>
<head>

<style type="text/css">
table {
	border-width: 1px;
	border-spacing: 0px;
	border-style: outset;
	border-color: gray;
	border-collapse: separate;
	background-color: white;
}
table th {
	border-width: 1px 1px 2px 1px;
	padding: 3px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: ;
}
table td {
	border-width: 1px;
	padding: 3px;
	border-style: inset;
	border-color: gray;
	background-color: white;
	-moz-border-radius: ;
}
.three-col {
       -moz-column-count: 3;
       -moz-column-gap: 20px;
       -webkit-column-count: 3;
       -webkit-column-gap : 20px;
       -moz-column-rule-color:  #ccc;
       -moz-column-rule-style:  solid;
       -moz-column-rule-width:  1px;
       -webkit-column-rule-color:  #ccc;
       -webkit-column-rule-style: solid ;
       -webkit-column-rule-width:  1px;
}
</style>

</head>
<body onload="addEvents()">
<?php
	date_default_timezone_set("Europe/London");
    ob_start(); // To hide header messages

    if(isset($_GET['n']) && isset($_GET['cId'])&& isset($_GET['d']) && isset($_GET['a'])) {
         $clientName = $_GET['n'];
	 $clientId = $_GET['cId'];
	 $agencyReferrer = $_GET['a'];
	 $issuedDate = date('Y-m-d',strtotime($_GET['d']));
    } else {
        die('Voucher not found, be sure that all fields were filled properly.');
    }
    
    require('config.php');
    require_once('log.php');
    $dbh = connect();
    echo '<h1>COLLECTION CAFES<br>';
    echo '<h4>You can collect your food parcels(s) from the following venues: <br></br>';

		echo '<table><tr><td valign = top ><h4>CANTERBURY</h4>';
		
		echo '<h5><b>TUESDAY<br> 9.30AM - 11.00AM</b><br></br>';
		echo 'ALLSAINTS CHURCH<br> MILITARY ROAD<br> CANTERBURY CT1 1PA</h5>';
		echo '<h5><b>FRIDAY<br> 12.00PM - 1.30PM</b><br></br>';
		echo 'ST DUNSTANS CHURCH HALL<br> OFF LONDON ROAD<br> CANTERBURY CT2 8LS</h5></td>';
		
		echo '<td valign = top><h4>WHISTABLE</h4>';
		echo '<h5><b>MONDAY<br> 9.30AM - 10.30AM</b><br></br>';
		echo 'WHITSTABLE UNITED<br> REFORMED CHURCH<br>MIDDLE WALL<br> WHITSTABLE CT5 1BW</td></h5>';
		
		echo '<td valign = top ><h4>HERNE BAY</h4>';
		echo '<h5><b>TUESDAY<br> 12.30PM - 2.00PM</b><br></br>';
		echo 'CHRIST CHURCH<br> WILLIAM STREET<br> HERNE BAY CT6 5NR</h5></td><tr></table>';



	echo '<h5><b>IMPORTANT NOTES FOR CLIENTS - PLEASE READ</b><br>
	<small>1 <b>THIS VOUCHER IS VALID FOR 14 DAYS FROM DATE OF ISSUE.</b><br>
    2   Canterbury Food Bank collection cafes are only open at the times stated above.<br>
	3   One adult food parcel weight aproximately 10kg;<br>
	4   Please bring <b>PROOF OF DEPENDENT CHILDREN</b> (e.g. Child benefit Statement) when you collect a food food parcel for children.<br>
	5   Up to five Canterbury Food Bank vouchers are allowed per <b>household</b> in a <b>12-month period.</b> <br>
	6   Canterbury Food Bank reserves the right to refuse to provide food parcels where the voucher appears to have been amended after issue or where fraud is suspected.</small><br>';
	
	echo '<b>_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _ </b><br>';
	
	echo '<h1>RECEIPT<br>';
	echo '<h3>For three days emergency supply of food</h3>';

	echo '<table><tr><td>REF. NO.: ';
	//get voucher id if is not a new voucher;
    $query = $dbh->prepare("SELECT Voucher.id FROM Voucher, Agency WHERE Agency.id=Voucher.idAgency AND Voucher.wasExchanged=false AND Agency.organisation = '".$agencyReferrer."'AND Voucher.idClient =".$clientId." AND Voucher.dateVoucherIssued='".$issuedDate."' ORDER BY Voucher.id DESC"); 
    if(!$query->execute()) {
		die('Unable to get Voucher from database.');
    }
    else{
		$row = $query->fetch();
		if($row['id']){ 
		    echo ''.$row['id'].'</br>';
		}
		else  //show id of the next entry in the database
		{
		    $query = $dbh->prepare("SELECT id FROM Voucher WHERE wasExchanged=false ORDER BY id DESC LIMIT 1");
		    if(!$query->execute()){
				die('Unable to get Voucher from database.');
		    }
		    else{
				$row = $query->fetch();
				$voucherId = $row['id']+1; 
				echo '<br>'.$row['id'].'</br><br>'; 
		    }
		}
    }
    echo '</td></tr></table>';
    echo '<table>';
    echo '<tr><td>DATE:  </td></tr><td>COLLECTION VENUE:  </td></tr>';
	echo '<tr><td>CLIENT NAME:  '.$clientName.'</td></tr>';
	echo '<tr><td>I HAVE RECEIVED ________ ADULT AND ________ CHILD FOOD PARCELS<br><br> SIGNATURE ________________________________________________________</td></tr></table><br>';
    
    echo '<small> <b>Data protection:</b> The personal information you have supplied will be stored by Canterbury Food Bank under the 
    	terms of the Data Protection Act 1998 and used to process requests for assistance; it will be retained for a reasonable period. 
    	Canterbury Food Bank ensure that only staff who have a reason to look at your information or data can do so and they cannot 
    	look at your information or data for personal reasons or out of curiosity. Canterbury Food Bank will never sell personal 
    	information or data with third parties unrelated to the services we provide unless we are required to do so by law or unless 
    	you have told us you consent to our doing this.</small>';
    

?>
</body></html>
