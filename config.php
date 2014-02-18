<?php
	//define('DB_USERNAME', 'hfl3');
	//define('DB_PASSWORD', 'etabaki');
	//define('DB_HOST', 'dragon.ukc.ac.uk');
	//define('DB_DATABASE', 'hfl3');
    
    //define('DB_USERNAME', 'cdms2');
	//define('DB_PASSWORD', 'fa#roub');
	//define('DB_HOST', 'dragon.ukc.ac.uk');
	//define('DB_DATABASE', 'cdms2');
	
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', 'asdfjkl;');
    define('DB_HOST', 'localhost');
    define('DB_DATABASE', 'fakeFoodbank2');
    
    define('ADMIN', 1);
    define('PACKER', 2);
    define('COUNTER', 4);
    define('AGSTAFF', 8);
    define('DPSTAFF', 16);
    define('VOLCOORD', 32);
    define('TRUSTEE', 64);
    
    function getAuth($usertype, $auth) {
        return ($usertype & $auth) == $auth;
    }
    
    function connect() {
        return new PDO('mysql:host=' . DB_HOST . ';'. // Connecting to database
                       'dbname=' . DB_DATABASE,
                       DB_USERNAME, DB_PASSWORD);
    }
    if(!defined('REDIRECT')) {
        define('REDIRECT', 'function');
        function redirect($url, $message) { // Redirects to other pages
            echo '<div class="message-text">'.$message.'</div><br />';
            header('Refresh:1;URL='.$url);
        }
    }
?>
