<html>
<head>
<script>
function move(img) {
    var row = img.parentNode.parentNode,
    	sibling = row.previousElementSibling,
		parent = row.parentNode;

    if(img.name == 'up' && parent.childNodes[1] != row)
	    parent.insertBefore(row, sibling);
    else if(img.name == 'down')
        parent.insertBefore(row.nextSibling, row)
}
</script>
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
@media print {
img {
    display: none;
}
</style>
</head>
<body onload="addEvents()">
<?php
    ob_start(); // To hide header messages

    if(isset($_GET['t'])) {
         $type = $_GET['t'];
    } else {
        die('Food Parcel Type missing.');
    }
    
    require('config.php');
    require_once('log.php');
    $dbh = connect();
    
    //get food items;
    
    $query = $dbh->prepare("SELECT FI.Name, FPT.name, FPT.tagColour, FPT.startingLetter, FPTC.quantity FROM FoodItem FI, FoodParcelType FPT, FPType_Contains FPTC WHERE FPTC.idFoodParcelType = " . $type . " AND FPTC.idFoodParcelType = FPT.id AND FPTC.idFoodItem = FI.id ORDER BY FI.Name");
    
    if($query->execute()) {
        $rows = $query->fetchAll();
        
        echo '<br />PACKING FORM<br /><br />';
        echo '<table id=\'tablepackingform\'>';
        echo '<thead><tr><th>Food Parcel Type</th><th>'.$rows[0]['name'].'</th></tr>';
        echo '<tr><th>Tag Colour</th><th>Orange</th></tr></thead>';
        echo '<tr><th>Item</th><th>Quantity</th></tr></thead>';
        
        for($i = 0; $i < $query->rowCount(); $i++) {
            $imgAsc = '<img src=\'rw_common/images/navArrowAsc.jpeg\' style=\'border:0;\' onclick=\'move(this)\' name=\'up\'>';
            $imgDesc = '<img src=\'rw_common/images/navArrowDesc.jpeg\' style=\'border:0;\' onclick=\'move(this)\' name=\'down\'>';
            echo '<tr><td>' . $rows[$i]['Name'] . ' ' . $imgAsc . ' ' . $imgDesc . '</td><td>' . $rows[$i]['quantity'] . '</td></tr>';
        }
        echo '</table>';
        auditlog('Printed packing form. Food parcel type: ' . $rows[0]['name']);
    } else {
        die('Unable to get food items from database.');
    }
?>
</body></html>
