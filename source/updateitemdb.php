<?PHP

    function update($t, $id) {
        require_once('config.php'); // Including database information
        require_once('log.php');
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT Name FROM FoodItem WHERE LOWER(Name) = :n AND id != :id");
        if(!$query->execute(array(":n" => strtolower($t), ":id" => $id))) {
            $response = 'Error: Unable to select food items from database.';
        } else {
            if($query->rowCount() > 0) {
                $response = 'Error: Duplicated item.';
            } else {
                $query = $dbh->prepare("UPDATE FoodItem SET Name = :n WHERE id = :id");
                if($query->execute(array(":n" => $t, ":id" => $id))) {
                    $response = $id;
                    auditlog('Updated food item. Name: ' . $t);
                } else {
                    $response = 'Error: Unable to update food items database.';
                }
            }
        }
        
        //output the response
        return $response;
    }

    function insert($t) {
        require_once('config.php'); // Including database information
        require_once('log.php');
        $dbh = connect();
        
        $query = $dbh->prepare("SELECT Name FROM FoodItem WHERE LOWER(Name) = :n");
        if(!$query->execute(array(":n" => strtolower($t)))) {
            $response = 'Error: Unable to select food items from database.';
        } else {
            if($query->rowCount() > 0) {
                $response = 'Error: Duplicated item.';
            } else {
                $query = $dbh->prepare("INSERT INTO FoodItem (Name) VALUES (:n)");
                if($query->execute(array(":n" => $t))) {
                    $query = $dbh->prepare("SELECT MAX(id) as newid FROM FoodItem");
                    if($query->execute()) {
                        $row = $query->fetch();
                        $response = $row[0]['newid'];
                        auditlog('Added new food item. Name: ' . $t);
                    } else {
                        $response = 'Error: Unable to select new food item id from database.';
                    }
                } else {
                    $response = 'Error: Unable to update food items database.';
                }
            }
        }
        
        //output the response
        return $response;
    }
    
    $mode = $_GET['mode'];
    
    switch($mode) {
    	case 'add':
        	//get the t parameter from URL
    		$t = strip_tags($_GET["t"]);
            $response = insert($t);
    		break;
            
        case 'update':
            //get the t parameter from URL
            $t = strip_tags($_GET["t"]);
	    	//get the id parameter from URL
    		$id = strip_tags($_GET["id"]);
            $response = update($t, $id);
            break;
            
        case 'remove':
            break;
    }
    
    echo $response;
?>