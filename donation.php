<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    require_once('config.php'); // Including database information
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }
    
    if(!isset($_SESSION['logged']) || (!getAuth($_SESSION['user']['auth'], ADMIN) && !getAuth($_SESSION['user']['auth'], TRUSTEE))) {
        redirect('index.php', '<h1>Wait while you are being redirected.</h1>');
        die();
    }
    
    $dbh = connect();
    
    $orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'datedesc' ;
    switch($orderby) {
            case 'dateasc':
	            $date = true;
            	$ordersql = 'date ASC, id ASC';
                $img = 'Asc';
            	break;
        	case 'datedesc':
            	$date = true;
            	$ordersql = 'date DESC, id DESC';
            	$img = 'Desc';
            	break;
        	case 'nameasc':
            	$name = true;
            	$ordersql = 'name ASC';
            	$img = 'Asc';
           	 	break;
        	case 'namedesc':
            	$name = true;
            	$ordersql = 'name DESC';
            	$img = 'Desc';
            	break;
        	case 'whasc':
            	$wh = true;
            	$ordersql = 'idWarehouse ASC, id DESC';
            	$img = 'Asc';
            	break;
        	case 'whdesc':
            	$wh = true;
            	$ordersql = 'idWarehouse DESC, id DESC';
            	$img = 'Desc';
            	break;
        	case 'totalasc':
            	$total = true;
            	$ordersql = 'total Asc, id DESC';
            	$img = 'Asc';
            	break;
        	case 'totaldesc':
            	$total = true;
            	$ordersql = 'total Desc, id DESC';
            	$img = 'Desc';
            	break;
    }
    
    $imgorder = '<img src=\'rw_common/images/navArrow'.$img.'.jpeg\' style=\'border:0;\'>';
    
    $query = $dbh->prepare("SELECT * FROM Donation ORDER BY " . $ordersql);
    
    if($query->execute()) {
        $donRows = $query->fetchAll();
        $donCount = $query->rowCount();
        
        for($i = 0; $i < $donCount; $i++) {
            $query = $dbh->prepare("SELECT centralWarehouseName FROM Warehouse WHERE id = :id");
            
            if($query->execute(array(":id" => $donRows[$i]['idWarehouse']))) {
                $row = $query->fetch();
                $donRows[$i]['location'] = $row['centralWarehouseName'];
            } else {
                die('<h1>Error</h1><br /><h3>Unable to get warehouse information from database.</h3>');
            }
            
            // Splitting the items
            if($donRows[$i]['items'] != null && $donRows[$i]['items'] != '') {
                $items = explode('[BRK]', $donRows[$i]['items']);
                $count = 0;
                foreach($items as $item) {
                    $itemQt = explode('[BRK2]', $item);
                    if(count($itemQt) > 1) {
                        $query = $dbh->prepare("SELECT Name FROM FoodItem WHERE id = :id");
                        
                        if($query->execute(array(":id" => $itemQt[0]))) {
                            $row = $query->fetch();
                            $donRows[$i]['item'][$count]['name'] = $row['Name'];
                            $donRows[$i]['item'][$count++]['quantity'] = $itemQt[1];
                        } else {
                            die('<h1>Error</h1><br /><h3>Unable to get warehouse information from database.</h3>');
                        }
                    }
                }
            }
        }
    } else {
        die('<h1>Error</h1><br /><h3>Unable to get donation information from database.</h3>');
    }
    auditlog('Viewed donations.');
    ?>
        <div><h1>Donations</h1></div><br /><br />

		<div style="height:400px;overflow:auto;">
			<table style='width: 100%;'>
		        <thead>
                    <td width='15%' height='30px'><h5>Order by:</h5></td>
					<td width='20%' height='30px' valign='middle'>
						<a href='donation.php?orderby=date<?PHP if($orderby == 'dateasc') echo 'desc'; else echo 'asc';?>'>
							<h3>Date
							<?PHP if(isset($date)) echo $imgorder; ?></h3>
						</a></td>
                    <td width='20%'>
						<a href='donation.php?orderby=name<?PHP if($orderby == 'nameasc') echo 'desc'; else echo 'asc';?>'>
						<h3>Name
						<?PHP if(isset($name)) echo $imgorder; ?></h3>
                    </a></td>
					<td width='40%'>
						<a href='donation.php?orderby=wh<?PHP if($orderby == 'whasc') echo 'desc'; else echo 'asc';?>'>
						<h3>Warehouse
						<?PHP if(isset($wh)) echo $imgorder; ?></h3>
                    </a></td>
                    <td colspan='2'>
						<a href='donation.php?orderby=total<?PHP if($orderby == 'totalasc') echo 'desc'; else echo 'asc';?>'>
						<h3>Total
						<?PHP if(isset($total)) echo $imgorder; ?></h3>
                    </a></td>
                </thead>
                <?PHP foreach($donRows as $row) { ?>
				    <tr>
						<td></td>
						<td height='30px'><?PHP echo date('d-m-Y', strtotime($row['date'])); ?></td>
						<td><?PHP echo $row['name']; ?></td>
						<td><?PHP echo $row['location']; ?></td>
						<td><?PHP echo $row['total']; ?></td>
						<td><div><input class='form-input-button' type='submit' value='View' id='button<?PHP echo $row['id']; ?>' onclick="DonationItems(<?PHP echo $row['id']; ?>, 'show')"></div></td>
                    </tr>
					<tr>
						<td></td><td colspan='4'><span id='viewitems<?PHP echo $row['id']; ?>' style='display:none;'><table style='width: 60%; border:1;'>
							<tr><td colspan='2'><hr></td></tr>
							<?PHP if(isset($row['item'])) foreach($row['item'] as $item) { ?>
								<tr><td><h5><?PHP echo $item['name']; ?></h5></td><td><h5><?PHP echo $item['quantity']; ?></h5></td></tr>
							<?PHP } ?>
							<tr><td colspan='2'><hr></td></tr>
						</table></span></td>
					</tr>
				<?PHP } ?>
			</table>
		</div>
    <?PHP
    require_once('footer.php');
    ob_flush(); ?>