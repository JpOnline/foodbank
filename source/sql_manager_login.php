<?php

ob_start(); // To hide header messages

require_once('header.php');
require_once('log.php');

if(!isset($_SESSION)){
    session_start();
}

require_once('config.php'); // Including database information

if(isset($_POST['login'])) { // Trying to login
    if($_POST['login'] == '' || $_POST['password'] == '') { // Login and/or password fields empty.
	redirect('sql_manager_login.php?login='.$_POST['name'], 'Invalid login and/or password.');
    } else {
	$dbh = connect();
	
	$login = $_POST['name'];
	$pass = $_POST['password'];
	
	$queryLogin = $dbh->prepare("SELECT * FROM Users WHERE login = :l");
	
	if($queryLogin->execute(array(":l" => $login))) { // Try to execute the query
	    if($queryLogin->rowCount() > 0) { // Login found
		$row = $queryLogin->fetch();
		
		if(!$row['enabled']) {
		    die('<h1>Error</h1><br /><h3>Sorry, your registration has not been activated yet.<br />Please contact an administrator.</h3>');
		}
		
		if($row['password'] == SHA1($pass)) { // Log in
		    $_SESSION['logged'] = true;
		    $_SESSION['user'] = array();
		    
		    foreach($row as $info => $value) {
			if($info != 'password' && !is_numeric($info)) {
			    $_SESSION['user'][$info] = $value;
			}
		    }
		    redirect('sqlmanager.php', 'Logged in successfully.');
		    auditlog('Logged in.');
		} else { // Password doesn't match
		    unset($_SESSION['logged']);
		    unset($_SESSION['user']);
		    session_destroy();          // Destroy all the session information
		    redirect('sql_manager_login.php?login='.$login, 'Wrong Password.');
		}
	    } else { // Login not found in the database
		unset($_SESSION['logged']);
		unset($_SESSION['user']);
		session_destroy();          // Destroy all the session information
		redirect('sql_manager_login.php?login='.$login, 'Invalid Login.');
	    }
	} else { // Query execution failed
	    unset($_SESSION['logged']);
	    unset($_SESSION['user']);
	    session_destroy();          // Destroy all the session information
	    redirect('sql_manager_login.php?login='.$login, 'Unable to connect to database.');
	}
	$dbh = null; // Closing Connection
    }
}else{//if it's not logged, show the form
?>
    <form action="sql_manager_login.php" method="post">
    <div>
    <div class="message-text"><strong>Confirm your Administrator login and password to access this area.</strong></div><br /><br />
    <div class="message-text"><strong>Login:</strong></div><br />
    <input class="form-input-field" type="text" value='<?php if(isset($_GET['login'])) echo $_GET['login']; ?>' name="name" size="40"/><br /><br /><br />

    <div class="message-text"><strong>Password:</strong></div><br />
    <input class="form-input-field" type="password" value="" name="password" size="40"/><br /><br />
    <input class="form-input-button" type="submit" name="submitButton" value="Submit" />
    <input type='hidden' name='login' value='1'>
    </div>
    </form>
    </div>
    </div>
<?php
}

require_once('footer.php');
ob_flush(); ?>
