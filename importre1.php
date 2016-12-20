<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/imgfunctions.php");
require_once("includes/classes/RE1File.php");

checkLogin();

addlog("Access", "Accessed importre1.php");

if (isset($_POST['importmemberssubmit'])) {

	$uploaddir = $GLOBALS['home_dir'] . '/masters-data/';
	$filename = basename($_FILES["userfile"]["name"]);

	addlog("Clubs", "Upload RE1 File", "RE1 Database has been uploaded.");

	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploaddir. basename($_FILES["userfile"]["name"]))) {

		$uploadStatus = "Successfully uploaded.";

		$re1 = new RE1File();
		$re1->setDatafile($filename);
        $membershipType = intval($_POST['membership_type']);

        if ($re1->setMemberShipType($membershipType)) {

            $re1->updateDetails();

        } else {

            addlog("RE1 Import", "Invalid Membership Type", "Unable to set Membership Type $membershipType");

        }


	} else {

		$uploadStatus = "Unable to read upload file!\n";

	}

}

htmlHeaders("Import RE1 Members");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Import RE1 Members</h2>

<h3>Upload Membership File:</h3>
<form enctype="multipart/form-data" method="post">

	<p>
        <label>Type of Membership in RE1 File: </label>
        <select name="membership_type">
            <option></option>
            <?php

            $membershipTypes = $GLOBALS['db']->getAll("SELECT * FROM membership_types 
                                ORDER BY enddate DESC;");
            db_checkerrors($membershipTypes);

            foreach ($membershipTypes as $m) {

                $mId = $m[0];
                $mDesc = $m[1];

                echo "<option value=\"$mId\">$mDesc</option>\n";

            }

            ?>

        </select>
    </p>
<p>
<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
<label>Membership CSV File: </label> <input type="file" name="userfile" />
</p>
<p>
<label> </label><input type="submit" name="importmemberssubmit" value="Import RE1 Member Data" />
</p>
</form>

<?php 

echo "</div>\n"; // main div

htmlFooters();


?>