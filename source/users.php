<?php
    ob_start(); // To hide header messages
    
    if(!isset($_GET['clear'])) require_once('header.php');
    require_once('log.php');
    require_once('config.php'); // Including database information
    $dbh = connect();
    
    if(!isset($_SESSION)) { // Starting session
        session_start();
    }

    if(isset($_SESSION['logged']) && getAuth($_SESSION['user']['auth'], ADMIN)) {
        $admin = true;
    } else {
        $admin = false;
    }
    
	if(isset($_GET['mode']) && ($_GET['mode'] == 'register' || $_GET['mode'] == 'edit')) {
		if(isset($_SESSION['logged']) && $_GET['mode'] == 'edit') {
            if(isset($_GET['id']) && ($admin || getAuth($_SESSION['user']['auth'], VOLCOORD))) {
            	$id = $_GET['id'];
            } else {
	            $id = $_SESSION['user']['id'];
            }
               
            $edit = true;
            
            $query = $dbh->prepare("SELECT id, enabled, login, auth, title, forename, familyName, email FROM Users WHERE id = :id");
            
            if($query->execute(array(":id" => $id))) {
                $row = $query->fetch();
            } else {
                die('<h1>Error</h1><h3>Unable to get user information from database.</h3>');
            }
        } else {
            $edit = false;
            $admin = false;
        } ?>
		<div><form action='users.php' method='post' onsubmit="return validateForm()"></div>
        <input type='hidden' name='mode' value='update'>
		<?PHP if(!$edit) { ?>
	        <div><h1>New user</h1></div><br />
        	<input type='hidden' name='adding' value='1'>
		<?PHP } else { ?>
	        <div><h1>Edit Profile</h1></div><br />
        	<input type='hidden' name='id' value='<?PHP echo $id; ?>'>
        	<input type='hidden' name='editing' value='1'>
		<?PHP } ?>
		<div><table style="width: 100%">
			<tr>
				<td><h3>Title *</h3></td>
				<td><select name='title'>
					<option value='Mr' <?PHP if($edit && $row['title'] == 'Mr') echo 'selected'; ?>>Mr.</option>
					<option value='Miss' <?PHP if($edit && $row['title'] == 'Miss') echo 'selected'; ?>>Miss</option>
					<option value='Mrs' <?PHP if($edit && $row['title'] == 'Mrs') echo 'selected'; ?>>Mrs.</option>
					<option value='Ms' <?PHP if($edit && $row['title'] == 'Ms') echo 'selected'; ?>>Ms.</option>
					<option value='Other' <?PHP if($edit && $row['title'] == 'Other') echo 'selected'; ?>>Other</option>
				</select></td>
			</tr>
			<tr>
				<td><h3>Forename *</h3></td>
				<td><input type='text' name='forename' value='<?PHP if($edit) echo $row['forename'] ?>'></td>
			</tr>
			<tr>
				<td><h3>Family Name *</h3></td>
				<td><input type='text' name='familyName' value='<?PHP if($edit) echo $row['familyName'] ?>'></td>
			</tr>
			<tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
			<tr>
				<td><h3>Login *</h3><h5>Minimum 4 characters.</h5></td>
				<td><input type='text' name='login' id='login' value='<?PHP if($edit) echo $row['login'] ?>'></td>
			</tr>
			<tr>
				<td><h3>Email *</h3></td>
				<td><input type='text' name='email' value='<?PHP if($edit) echo $row['email'] ?>'></td>
			</tr>
			<tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
			<?PHP if($edit) { ?>
				<tr>
            		<td></td>
					<td><h5>Change password?<input type='checkbox' name='change' onchange="enablePass(this)"></h5></td>
				</tr>
			<?PHP } ?>
			<tr>
				<td><h3>Password *</h3><h5>Minimum 6 characters.</h5></td>
				<td><input type='password' name='pass1' id='pass1' <?PHP if($edit) echo 'disabled=\'disabled\''; ?>></td>
			</tr>
			<tr>
				<td><h3>Confirm Password *</h3></td>
				<td><input type='password' name='pass2' id='pass2' <?PHP if($edit) echo 'disabled=\'disabled\''; ?>></td>
			</tr>
			<tr>
				<td colspan='2'>&nbsp;</td>
			</tr>
			<tr>
				<td><h3>Roles</h3><h5>Ctrl+click to select more than one.</h5></td>
				<td><select name='qualifications[]' multiple='multiple' style="height:100px; width: 150px;">
					<?PHP if($admin) { ?> <option value='<?PHP echo ADMIN; ?>' <?PHP if($edit && getAuth($row['auth'], ADMIN)) echo 'selected=\'selected\''; ?>>Admin</option><?PHP } ?>
                    <option value='<?PHP echo PACKER; ?>' <?PHP if($edit && getAuth($row['auth'], PACKER)) echo 'selected=\'selected\''; ?>>Packer</option>
					<option value='<?PHP echo COUNTER; ?>' <?PHP if($edit && getAuth($row['auth'], COUNTER)) echo 'selected=\'selected\''; ?>>Counter</option>
					<option value='<?PHP echo AGSTAFF; ?>' <?PHP if($edit && getAuth($row['auth'], AGSTAFF)) echo 'selected=\'selected\''; ?>>Agency Staff</option>
					<option value='<?PHP echo DPSTAFF; ?>' <?PHP if($edit && getAuth($row['auth'], DPSTAFF)) echo 'selected=\'selected\''; ?>>Distribution Point Staff</option>
					<option value='<?PHP echo VOLCOORD; ?>' <?PHP if($edit && getAuth($row['auth'], VOLCOORD)) echo 'selected=\'selected\''; ?>>Volunteer Coordinator</option>
					<option value='<?PHP echo TRUSTEE; ?>' <?PHP if($edit && getAuth($row['auth'], TRUSTEE)) echo 'selected=\'selected\''; ?>>Trustee</option>
				</td>
			</tr>
			<?PHP if(($admin || getAuth($_SESSION['user']['auth'], VOLCOORD)) && isset($_GET['id']) && $_SESSION['user']['id'] != $_GET['id']) { ?>
				<tr>
					<td colspan='2'>&nbsp;</td>
	            </tr>
    	        <tr>
					<td><h3>Enabled</h3></td>
					<td><input type='checkbox' name='enabled' <?PHP if($edit && $row['enabled'] == 1) echo 'checked=\'checked\''; ?>></td>
				</tr>
				<?PHP if(!getAuth($row['auth'], ADMIN)) { ?>
                	<tr>
            	        <td><h3>Remove user</h3></td>
        	            <td><input type='checkbox' name='remove' id='removeuser' onchange="removeUser(this)"></td>
    	            </tr>
				<?PHP }?>
			<?PHP } else if($admin || getAuth($_SESSION['user']['auth'], VOLCOORD)) {?>
				<input type='hidden' name='enabled' value='on'>
			<?PHP }?>
        </table></div>
		<input class="form-input-button"  type='submit' name='submit' value='Submit'></form>
		<?PHP
    } else if(isset($_POST['mode']) && $_POST['mode'] == 'update') {
        $title = $_POST['title'];
        $forename = strip_tags($_POST['forename']);
        $familyName = strip_tags($_POST['familyName']);
        $email = strip_tags($_POST['email']);
        $login = strip_tags($_POST['login']);
        $qualifications = (isset($_POST['qualifications'])) ? $_POST['qualifications'] : array();
        
        $temp = 0;
        foreach($qualifications as $q) {
            $temp |= $q;
        }
        $qualifications = $temp;
        
        if(isset($_POST['remove']) && $_POST['remove']) {
            $id = $_POST['id'];
            $query = $dbh->prepare("DELETE FROM Users WHERE id = :id");
            
            if(!$query->execute(array(":id" => $id))) {
                die('<h1>Error</h1><br /><h3>Unable to remove user from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            redirect('index.php', '<h1>User removed successfully.</h1>');
            auditlog('Removed user: ' . $forename . ' ' . $familyName . '. Login: ' . $login);
        } else if(isset($_POST['adding'])) {
            $pass1 = SHA1(strip_tags($_POST['pass1']));
            
            $query = $dbh->prepare("SELECT id FROM Users WHERE login = :l");
            if($query->execute(array(":l" => $login))) {
                if($query->rowCount() > 0) {
                    die('<h1>Error</h1><br /><h3>Duplicated login.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
				die('<h1>Error</h1><br /><h3>Unable to select user information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            
            $query = $dbh->prepare("SELECT id FROM Users WHERE email = :e");
            if($query->execute(array(":e" => $email))) {
                if($query->rowCount() > 0) {
                    die('<h1>Error</h1><br /><h3>Email already registered.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
				die('<h1>Error</h1><br /><h3>Unable to select user information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            
            $query = $dbh->prepare("INSERT INTO Users (login, password, title, forename, familyName, email, auth) VALUES (:l, :p, :t, :f, :fn, :e, :a)");
            if(!$query->execute(array(":l" => $login, ":p" => $pass1, ":t" => $title, ":f" => $forename, ":fn" => $familyName, ":e" => $email, ":a" => $qualifications))) {
                die('<h1>Error</h1><br /><h3>Unable to update users database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            redirect('index.php', '<h1>User registered successfully.</h1>');
            auditlog('New user registered: ' . $forename . ' ' . $familyName . '. Login: ' . $login);
        } else if(isset($_POST['editing'])) {
            $id = $_POST['id'];
            $values[':id'] = $id;
            
            if(isset($_POST['enabled'])) {
                $values[':en'] = 1;
	            $enabled = ', enabled = :en';
            } else {
                $values[':en'] = 0;
	            $enabled = ', enabled = :en';
            }
            
            $query = $dbh->prepare("SELECT id FROM Users WHERE login = :l AND id != :id");
            if($query->execute(array(":l" => $login, ":id" => $id))) {
                if($query->rowCount() > 0) {
                    die('<h1>Error</h1><br /><h3>Duplicated login.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
				die('<h1>Error</h1><br /><h3>Unable to select user information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            
            $query = $dbh->prepare("SELECT id FROM Users WHERE email = :e AND id != :id");
            if($query->execute(array(":e" => $email, ":id" => $id))) {
                if($query->rowCount() > 0) {
                    die('<h1>Error</h1><br /><h3>Email already registered.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
                }
            } else {
				die('<h1>Error</h1><br /><h3>Unable to select user information from database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            
            if(isset($_POST['pass1'])) { // Changing password
                $pass1 = SHA1(strip_tags($_POST['pass1']));
                
                $values[':p'] = $pass1;
                $pass = ', password = :p';
            } else { // Not changing password
                $pass = '';
            }
            
            $values[':t'] = $title;
            $values[':f'] = $forename;
            $values[':fn'] = $familyName;
            $values[':l'] = $login;
            $values[':e'] = $email;
            $values[':a'] = $qualifications;
            
            $query = $dbh->prepare("UPDATE Users SET title = :t, forename = :f, familyName = :fn, login = :l, email = :e, auth = :a".$pass.$enabled." WHERE id = :id");
            if(!$query->execute($values)) {
                die('<h1>Error</h1><br /><h3>Unable to update users database.</h3><div><input class=\'form-input-button\'  type=\'submit\' value=\'Back\' onclick=\'window.history.back()\'></div>');
            }
            redirect('index.php', '<h1>User updated successfully.</h1>');
            auditlog('Modified user: ' . $forename . ' ' . $familyName . '. Login: ' . $login);
        }
    } else {
        if(!$admin && !getAuth($_SESSION['user']['auth'], VOLCOORD)) {
            redirect('index.php', 'Wait while you are being redirected.');
        } else {
            $query = $dbh->prepare("SELECT id, forename, familyName, email, login FROM Users WHERE enabled = false ORDER BY id DESC");
            if($query->execute()) {
                $rowsNew = $query->fetchAll();
                $rowsNewCount = $query->rowCount();
            } else {
                die('<h1>Error</h1><br /><h3>Unable to select users information from database.</h3>');
            }
            $query = $dbh->prepare("SELECT id, forename, familyName, email, login, enabled FROM Users ORDER BY familyName");
            if($query->execute()) {
                $rows = $query->fetchAll();
            } else {
                die('<h1>Error</h1><br /><h3>Unable to select users information from database.</h3>');
            }
            ?>
            <div><h1>Users control panel</h1></div><br /><br />
			<?PHP if($rowsNewCount > 0) { ?>
            	<div><h3>New users</h3></div><br />
	            <div><table style='text-align:center; width:100%;'>
    	        	<tr>
        	    		<td><h3>id</h3></td>
            			<td><h3>name</h3></td>
                        <td><h3>login</h3></td>
	            		<td><h3>email</h3></td>
    	        		<td><h3>enabled</h3></td>
			            <td>&nbsp;</td>
                    </tr>
		            <?PHP foreach($rowsNew as $user) { ?>
			            <tr>
				            <td><?PHP echo $user['id']; ?></td>
        			    	<td><?PHP echo $user['forename'] . ' ' . $user['familyName']; ?></td>
		        		    <td><?PHP echo $user['login']; ?></td>
    		        		<td><?PHP echo $user['email']; ?></td>
	        		    	<td>False</td>
	    	    		    <td><form action='users.php' method='get'><input type='hidden' name='mode' value='edit'><input type='hidden' name='id' value='<?PHP echo $user['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></h4></td>
		            	</tr>
    	            <?PHP } ?>
        	    </table></div><br />
			<?PHP } ?>
            <div><h3>Search for users</h3></div>
			<div><input type="text" id="clientinfo" onkeyup="getUsers(this.value)" />
				<select name='searchtype' id='searchtype' onchange="hideUsers()">
					<option value='lastname'>Family Name</option>
					<option value='login'>Login</option>
					<option value='email'>Email</option>
				</select><input class="form-input-button" type='reset' value='Reset' onClick="hideUsers()">
			</div><br />
			<div><br /><span id="result"></span></div>
			<div style="height:400px;overflow:auto;"><span id="allusers"><table style='text-align:center; width:100%;'>
					<tr>
						<td><h3>id</h3></td>
						<td><h3>name</h3></td>
						<td><h3>login</h3></td>
						<td><h3>email</h3></td>
						<td><h3>enabled</h3></td>
						<td>&nbsp;</td>
                    </tr>
                    <?PHP foreach($rows as $user) { ?>
						<tr>
							<td><?PHP echo $user['id']; ?></td>
							<td><?PHP echo $user['forename'] . ' ' . $user['familyName']; ?></td>
							<td><?PHP echo $user['login']; ?></td>
							<td><?PHP echo $user['email']; ?></td>
							<?PHP if($user['enabled']) { ?>
								<td>True</td>
							<?PHP } else { ?>
								<td>False</td>
							<?PHP } ?>
							<td><form action='users.php' method='get'><input type='hidden' name='mode' value='edit'><input type='hidden' name='id' value='<?PHP echo $user['id']; ?>'><input class="form-input-button" type='submit' value='View'></form></h4></td>
						</tr>
					<?PHP } ?>
				</table></span></div>
		<?PHP
        }
    }
    require_once('footer.php');
    ob_flush(); ?>












