<?PHP
	if(!isset($_SESSION)) {
    	session_start();
	}
    require_once('config.php');
    ?>
<!doctype html>
<!--[if IE 7 ]><html lang="en" class="no-js ie7"><![endif]-->
<!--[if IE 8 ]><html lang="en" class="no-js ie8"><![endif]-->
<!--[if IE 9 ]><html lang="en" class="no-js ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="en" class="no-js"><!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>Canterbury Food Bank</title>
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/styles.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/colour_tags.css" />
<script type="text/javascript" src="rw_common/themes/j_line/javascript.js"></script>
<script type="text/javascript" src="rw_common/themes/j_line/scripts/function.js"></script>
<script>RwSet={pathto:"rw_common/themes/j_line/javascript.js",baseurl:"http://www.canterburyfoodbank.org/"};</script>
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/heading_no_scroll.css" />
<script type="text/javascript" src="rw_common/themes/j_line/scripts/background_1.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/texture-dot-50.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/title_bebasneue.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/nav_arial_black.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/nav_22.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/nav_rollover_2.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/break_single_dashed.css" />
<link rel="stylesheet" type="text/css" media="screen" href="rw_common/themes/j_line/css/feature_none.css" />

<head>
<script language="javascript" type="text/javascript" src="functions.js"></script>
</head>

</head>
<body>
<img src="img" alt="" id="background" />
<div id="accent" class="fade-in one"></div>
<div id="texture">
<header>
<a id="siteLogo" href="http://www.canterburyfoodbank.org/"><img src="rw_common/images/Food-bank-logo.png" width="250" height="148" alt="Site logo"/></a>
<div id="siteTitle"><div>Emergency food in a crisis</div></div>
<nav><ul>
<li><a href="index.php" rel="self">Home</a></li>
<?PHP if(isset($_SESSION['logged']) && $_SESSION['logged']) { ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], AGSTAFF) || getAuth($_SESSION['user']['auth'], DPSTAFF)) { ?>
		<li><a href="client.php" rel="self">Clients</a></li>
	<?PHP } ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], TRUSTEE)) { ?>
		<li><a href="donation.php" rel="self">Donations</a></li>
	<?PHP } ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) ||  getAuth($_SESSION['user']['auth'], TRUSTEE) ||getAuth($_SESSION['user']['auth'], COUNTER) || getAuth($_SESSION['user']['auth'], PACKER)) { ?>
    	<li><a href="fooditem.php" rel="self">Food Items</a></li>
	<?PHP } ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], DPSTAFF) || getAuth($_SESSION['user']['auth'], PACKER)) { ?>
		<li><a href="foodparcel.php" rel="self">Food Parcels</a></li>
	<?PHP } ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], DPSTAFF) || getAuth($_SESSION['user']['auth'], AGSTAFF)) { ?>
		<li><a href="locations.php" rel="self">Locations</a></li>
	<?PHP } ?>
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], TRUSTEE)) { ?>
		<li><a href="reports.php" rel="self">Reports</a></li>
	<?PHP } ?>
	<!-- now the volunteer coordinator is also responsable for the registration of new volunteers -->
	<?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], DPSTAFF) || getAuth($_SESSION['user']['auth'], AGSTAFF)) { ?>
		<li><a href="voucher.php" rel="self">Vouchers</a></li>
	<?PHP } ?>
	<li>&nbsp;</li>
	<li><a href="login.php" rel="self">Log Out</a></li>
	<li><a href="users.php?mode=edit" rel="self">Edit Profile</a></li>
	<li><a href="problems.php" rel="self">Report Problem</a></li>
	<li>&nbsp;</li>
    <?PHP if(getAuth($_SESSION['user']['auth'], ADMIN)) { ?>
		<li><a href="viewlog.php" rel="self">Audit Log</a></li>
	<?PHP } ?>
    <?PHP if(getAuth($_SESSION['user']['auth'], ADMIN) || getAuth($_SESSION['user']['auth'], VOLCOORD)) { ?>
		<li><a href="users.php" rel="self">User Control</a></li>
		<li><a href="users.php?mode=register" rel="self">Register</a></li>
	<?PHP } ?>
<?PHP } else { ?>
	<li><a href="login.php" rel="self">Log in</a></li>
	<!--only the admin will be responsible for the resgitration of new volunteers-->
	<!--<li><a href="users.php?mode=register" rel="self">Register</a></li> -->
<?PHP } ?>
</ul></nav>
<footer>Serving the Canterbury District - Canterbury, Whitstable, Herne Bay & villages <a href="#" id="rw_email_contact">Email Webmaster</a><script type="text/javascript">var _rwObsfuscatedHref0 = "mai";var _rwObsfuscatedHref1 = "lto";var _rwObsfuscatedHref2 = ":in";var _rwObsfuscatedHref3 = "fo@";var _rwObsfuscatedHref4 = "can";var _rwObsfuscatedHref5 = "ter";var _rwObsfuscatedHref6 = "bur";var _rwObsfuscatedHref7 = "yfo";var _rwObsfuscatedHref8 = "odb";var _rwObsfuscatedHref9 = "ank";var _rwObsfuscatedHref10 = ".or";var _rwObsfuscatedHref11 = "g";var _rwObsfuscatedHref = _rwObsfuscatedHref0+_rwObsfuscatedHref1+_rwObsfuscatedHref2+_rwObsfuscatedHref3+_rwObsfuscatedHref4+_rwObsfuscatedHref5+_rwObsfuscatedHref6+_rwObsfuscatedHref7+_rwObsfuscatedHref8+_rwObsfuscatedHref9+_rwObsfuscatedHref10+_rwObsfuscatedHref11; document.getElementById('rw_email_contact').href = _rwObsfuscatedHref;</script></footer>
</header>
<?PHP
    if(!defined('REDIRECT')) {
        define('REDIRECT', 'function');
        function redirect($url, $message) { // Redirects to other pages
            echo '<div class="message-text">'.$message.'</div><br />';
            header('Refresh:1;URL='.$url);
        }
    }
    
?>
<div id="container">
<section>
<div id="swatchA"></div>
<div id="swatchB"></div>
<div id="swatchC"></div>
<div id="socialIcons"></div>
<div id="sectionWrap">
<div id="breadcrumb"></div>
<div id="featureImage">
<div id="extraContainer1"></div>
</div><!-- .featureImage -->