<?php
require_once("includes/setup.php");
require_once("includes/sidebar.php");
require_once("includes/classes/Club.php");
require_once("includes/classes/Member.php");

checkLogin();

addlog("Access", "Accessed membercreate.php");

if (isset($_POST['createMembership'])) {

    // Create member
    $firstname = $_POST['firstname'];
    $othernames = $_POST['othernames'];
    $surname = $_POST['surname'];
    $dob = $_POST['dob'];
    $number = $_POST['number'];
    $gender = $_POST['gender'];

    $member = new Member();
    $member->setFirstname($firstname);
    $member->setOtherNames($othernames);
    $member->setSurname($surname);
    $member->setDob($dob);
    $member->setMSANumber($number);
    $member->setGender($gender);
    $member->store();

    $memberId = $member->getId();

    // Create club if necessary
    $clubId = intval($_POST['club']);

    if ($clubId == 0) {

        $club = new Club();
        $club->setCode($_POST['newClubCode']);
        $club->setClubName($_POST['newClubName']);
        $clubId = $club->store();

    }

    $membershipType = intval($_POST['membershiptype']);

    if ($membershipType != 0) {

        $member->applyMembership($membershipType, $clubId);

    }

}

htmlHeaders("Create Member");

sidebarMenu();

echo "<div id=\"main\">\n";

?>

<h2>Create Member</h2>

    <form class="form-horizontal" method="post">
        <div class="form-group">
            <label class="control-label col-sm-2" for="firstname">First Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="firstname" name="firstname" placeholder="First Name" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="othernames">Other Names:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="othernames" name="othernames" placeholder="Other Names" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="surname">Surname:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="surname" name="surname" placeholder="Surname" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="dob">Date of Birth:</label>
            <div class="col-sm-3">
                <input type="date" class="form-control" id="dob" name="dob" placeholder="" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="number">Membership Number:</label>
            <div class="col-sm-3">
                <input type="text" class="form-control" id="number" name="number" placeholder="" />
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="gender">Gender:</label>
            <div class="col-sm-10">
                <label class="radio-inline"><input type="radio" name="gender" value="M" />Male</label>
                <label class="radio-inline"><input type="radio" name="gender" value="F" />Female</label>
            </div>
        </div>

        <h3>Apply Membership</h3>

        <div class="form-group">
            <label class="control-label col-sm-2" for="club">Club:</label>
            <div class="col-sm-10">
                <select name="club" id="club">
                    <option></option>

                    <?php

                    // Get list of clubs
                    $clubList = $GLOBALS['db']->getAll("SELECT * FROM clubs ORDER BY clubname;");
                    db_checkerrors($clubList);

                    foreach ($clubList as $c) {

                        $cId = $c[0];
                        $cCode = $c[1];
                        $cName = $c[2];

                        echo "<option value=\"$cId\">$cName ($cCode)</option>\n";

                    }

                    ?>

                </select>
            </div>
        </div>

        <div class="form-group form-inline">
            <label class="control-label col-sm-2">or Create New Club:</label>
            <div class="form-group col-sm-4">
                <label for="newClubCode">Club Code:</label>
                <input type="text" class="form-control" id="newClubCode" name="newClubCode" placeholder="ABC">
            </div>
            <div class="form-group col-sm-4">
                <label for="exampleInputEmail2">Club Name:</label>
                <input type="text" class="form-control" id="newClubName" name="newClubName" placeholder="New Club Name">
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="membershiptype">Membership Type:</label>
            <div class="col-sm-10">
                <select name="membershiptype" id="membershipType">
                    <option></option>

                    <?php

                    // Get list of membership types
                    $typeList = $GLOBALS['db']->getAll("SELECT * FROM membership_types ORDER BY id DESC;");
                    db_checkerrors($typeList);

                    foreach ($typeList as $t) {

                        $tId = $t[0];
                        $tName = $t[1];

                        echo "<option value=\"$tId\">$tName</option>\n";

                    }

                    ?>

                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <input type="submit" class="btn btn-default" name="createMembership" value="Create Membership"/>
            </div>
        </div>
    </form>

<script>

    $( document ).ready(function() {
        $('#club').combobox();
        $('#membershipType').combobox();
    });

</script>

<?php

echo "</div>\n"; // main div

htmlFooters();


?>