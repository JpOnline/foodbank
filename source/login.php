<?php
    ob_start(); // To hide header messages
    
    require_once('header.php');
    require_once('log.php');
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }

    require_once('config.php'); // Including database information
    
    if(isset($_POST['login'])) { // Trying to login
        if($_POST['login'] == '' || $_POST['password'] == '') { // Login and/or password fields empty.
            redirect('login.php?login='.$_POST['name'], 'Invalid login and/or password.');
        } else {
            if(!isset($_SESSION['logged'])) {
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
                            redirect('index.php', 'Logged in successfully.');
                            auditlog('Logged in.');
                        } else { // Password doesn't match
                            redirect('login.php?login='.$login, 'Wrong Password.');
                        }
                    } else { // Login not found in the database
                        redirect('login.php?login='.$login, 'Invalid Login.');
                    }
                } else { // Query execution failed
                    redirect('login.php?login='.$login, 'Unable to connect to database.');
                }
                $dbh = null; // Closing Connection
            } else {
                redirect('index.php', 'Logged in successfuly.');
            }
        }
    } else if(isset($_GET['mode']) && $_GET['mode'] == 'logout') { // Trying to logout
        if(isset($_SESSION['logged'])) { // If it is logged in
            unset($_SESSION['logged']);
            unset($_SESSION['user']);
            session_destroy();          // Destroy all the session information
            redirect('index.php', 'You were logged out successfuly');
        } else { // If it is not logged in
            redirect('login.php', 'You are not logged in.');
        }
        
    } else {
        // If it is not logged, shows the form
        if(!isset($_SESSION['logged']) || $_SESSION['logged'] == false) {
            ?>
<form action="login.php" method="post">
<div>
<div class="message-text"><strong>Login:</strong></div><br />
<input class="form-input-field" type="text" value='<?php if(isset($_GET['login'])) echo $_GET['login']; ?>' name="name" size="40"/><br /><br /><br />

<div class="message-text"><strong>Password:</strong></div><br />
<input class="form-input-field" type="password" value="" name="password" size="40"/><br /><br />
<input class="form-input-button" type="submit" name="submitButton" value="Submit" />
<input type='hidden' name='login' value='1'>
</div>
</form>
		<?php
	} else { // Otherwise, shows the logout button
		//echo '<a href=\'login.php?mode=logout\'>Logout</a>';
        ?>
<form action="login.php" method="GET">
<div>
<div class="message-text"><strong>Logout:</strong></div><br />
<input class="form-input-button" type="submit" value="Logout" />
<input type='hidden' name='mode' value='logout'>
</div>
</form>
		<?PHP
	}
}
?>
</div>
</div>
<?php
    require_once('footer.php');
    ob_flush(); ?>