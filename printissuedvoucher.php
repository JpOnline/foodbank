<html>
<head>

<style type="text/css">
table {
	border-width: 2px;
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
</style>

</head>
<body onload="addEvents()">
<?php
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
    
    echo '<div>';
	echo '<div style="float: left">';
	    echo '<img src="rw_common/images/Food-bank-logo_NEW.png" width="170" height="129">';
	echo '</div>';
	echo '<div>';
	    echo '<h1>Voucher</h1>';
	    echo '<h3>For three days emergency supply of food</h3>';
	echo '</div>';
    echo '</div>';
    echo '<div>';
	echo '<div style="float: left">';
	    echo '<h4>See separete sheet for collection points and opening times.</h4>';
	echo '</div>';
	echo '<div>';
	echo '<h4>For further information, please contact: <br>
	    </h4><h5>Telephone: 07718 108875 <br>
		Email: info@canterburyfoodbank.org <br>
		Website: www.canterburyfoodbank.org</h5>';
	echo '</div>';
	echo '<div style="float: right">';
	    echo '<table border="1"><tr><td>Ref. No.';
	echo '</div>';
    echo '</div>';

    //get voucher id if is not a new voucher;
    $query = $dbh->prepare("SELECT Voucher.id FROM Voucher, Agency WHERE Agency.id=Voucher.idAgency AND Voucher.wasExchanged=false AND Agency.organisation = '".$agencyReferrer."'AND Voucher.idClient =".$clientId." AND Voucher.dateVoucherIssued='".$issuedDate."' ORDER BY Voucher.id DESC"); 
    if(!$query->execute()) {
	die('Unable to get Voucher from database.');
    }
    else{
	$row = $query->fetch();
	if($row['id']){ 
	    echo 'id '.$row['id'].'</br>';
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

		echo '<br>id '.$row['id'].'</br>'; 
		echo '<br>'.$clientName.'</br>';
		echo '<br>'.$agencyReferrer.'</br>';
		echo '<br>'.$issuedDate.'</br>';
	    }
	}
    }
    echo '</td></tr></table>';
    echo '<table border="1">';
	echo '<tr><td>Clients fullname</td><td>'.$clientName.'</td></tr>';
	echo '<tr><td>Referer</td><td>'.$agencyReferrer.'</td></tr>';
	echo '<tr><td>Date</td><td>'.$_GET['d'].'</td></tr>';
    echo '</table>';
    echo '<h5>1 The Canterbury Food Bank distribution centre is only open at the times stated above;<br>
	2 One adult food parcel weight aproximately 10kg;<br>
	3 Clients should bring PROOF OF DEPENDENT CHILDREN (e.g. Child Benefit Statement) when they collect a food parcel for children;<br>
	4 A client can be a single person or a family (mum, dad and dependent children). In either case we class this as one "household"<br>
	5 Up to five Canterbury Food Bank vouchers are allowed per household in a 12-month period.<br>
	6 If a client is residing with a friends or wider family, these are not classed as dependants and should not be shown in the voucher;<br>
	7 Canterbury Food Bank reserves the right to refuse to provide food parcels where the voucher appears to have bee</h5>';

?>
</body></html>
