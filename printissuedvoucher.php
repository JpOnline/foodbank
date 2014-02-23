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
    
    //get voucher id if is not a new voucher;
    $query = $dbh->prepare("SELECT Voucher.id FROM Voucher, Agency WHERE Agency.id=Voucher.idAgency AND Voucher.wasExchanged=false AND Agency.organisation = '".$agencyReferrer."'AND Voucher.idClient =".$clientId." AND Voucher.dateVoucherIssued='".$issuedDate."' ORDER BY Voucher.id DESC"); 
    if(!$query->execute()) {
	die('Unable to get Voucher from database.');
    }
    else{
	$row = $query->fetch();
	if($row['id']){ 
	    echo '<br />Voucher<br />';
	    echo '<br>id '.$row['id'].'</br>';
	    echo '<br>agencyVoucherReference '.$row['agencyVoucherReference'].'</br>';
	    echo '<br>numberOfAdults '.$row['numberOfAdults'].'</br>';
	    echo '<br>numberOfChildren '.$row['numberOfChildren'].'</br>';
	    echo '<br>wasExchanged '.$row['wasExchanged'].'</br>';
	    echo '<br>helping '.$row['helping'].'</br>';
	    echo '<br>dateVoucherIssued '.$row['dateVoucherIssued'].'</br>';
	    echo '<br>idAgency '.$row['idAgency'].'</br>';
	    echo '<br>organisation '.$row['organisation'].'</br>';
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

    echo '<img src="rw_common/images/Food-bank-logo_NEW.png" width="250" height="148">';
?>
</body></html>
