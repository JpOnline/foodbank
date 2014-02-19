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
	 $issuedDate = $_GET['d'];
    } else {
        die('Voucher not found');
    }
    
    require('config.php');
    require_once('log.php');
    $dbh = connect();
    
    //get voucher information;
    $query = $dbh->prepare("SELECT V.id FROM Voucher V JOIN Agency A ON A.id=V.idAgency WHERE V.idClient =".$clientId." AND A.organisation =".$agencyReferrer.""); 

    echo '<br>'.$clientName.'</br>';
    echo '<br>'.$agencyReferrer.'</br>';
    echo '<br>'.$issuedDate.'</br>';

    if($query->execute()) {
        $row = $query->fetch();
        
        echo '<br />Voucher<br /><br />';
        echo $row['id'];
        
    } else {
        die('Unable to get Voucher from database.');
    }
?>
</body></html>
