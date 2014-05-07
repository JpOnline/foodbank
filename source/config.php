<?php
    date_default_timezone_set("Europe/London");
 
   //In the fake database is possible to log in the system with the user jpd21 and the password asdfjkl;

   //Database non official, once the official one contain confidential data.
    define('DB_USERNAME', 'zigoavalia');
    define('DB_PASSWORD', 'zigavl@@9$');
    define('DB_HOST', 'aguamarinha.com.br');
    define('DB_DATABASE', 'zigoavalia');
    
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
