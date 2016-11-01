<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");

checkLogin();

addlog("Access", "Accessed memberbulk.php");

if ($_isset($_POST['checknames'])) {

    $memberArray = preg_split ('/$\R?^/m', $_POST['bulkmembernames']);

    foreach ($memberArray as $m) {



    }

}

htmlHeaders("Check Bulk Members");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Check Bulk Members</h2>

<form method="post">

<p>
    <label>Paste Member Names:</label>
    <textarea name="bulkmembernames" rows="10" cols="80">

    </textarea>
</p>

<p>
<label> </label><input type="submit" name="checknames" value="Check Names" />
</p>
</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>