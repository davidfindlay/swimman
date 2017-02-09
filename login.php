<?php

require_once("includes/setup.php");

$failed = false;

	// Has form been submitted?
	if (isset($_POST['swsubmit'])) {

		$username = $_POST['swusername'];
		$password = $_POST['swpassword'];

		$result = authenticate($username, $password);

		if ($result == true) {

			// Check if neededs to set cookie
			if (isset($_POST['remember'])) {

				$expire = time() + 2592000;
				setcookie('swuname', $_POST['swusername'], $expire);
				setcookie('swpass', $_POST['swpassword'], $expire);

			}

            $GLOBALS['authLog']->info("Log in via web form: $username");
			
			header("Location: index.php");

		} else {

			$failed = true;
            $GLOBALS['authLog']->error("Log in via web form: $username failed");

		}

	}

htmlHeaders("Swimming Management System - Login");

addlog("Access", "Accessed login.php");

?>

<h1>Swimming Management System - Login</h1>
<p>
Log in using your MSQ Members Community username and password.
</p>
<form method="post">
<p>

<?php 

if ($failed == true) {
	
	echo "<strong><i>Username or password incorrect!</i></strong><br />\n";
	
}

?>

<strong>Username: </strong> <input type="text" size="30" name="swusername" /><br />
<strong>Password: </strong> <input type="password" size="30" name="swpassword" /><br />
<input type="checkbox" checked="checked" name="remember" />Remember Me<br />
<input type="submit" name="swsubmit" value="Login" />
<input type="button" value="Cancel" />
</p>
</form>


<?php 

htmlFooters();


?>