<?php

function sidebarMenu() {

	echo "<div id=\"navigation\">\n";
	echo "<nav class=\"nav\">\n";

	echo "<ul>\n";

	echo "<li>\n";
	echo "<a href=\"dashboard.php\">Dashboard</a>\n";
	echo "</li>\n";

	// Meets Submenu
	echo "<li>\n";
	echo "Meets\n";
	echo "<ul>\n";
	echo "<li>\n";
	echo "<a href=\"meets.php\">Meet List</a>";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"eprogram.php\">eProgram</a>";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"uploadeprogram.php\">eProgram Upload</a>";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"ppmg.php\">Pan Pacific Masters Games</a>";
	echo "</li>\n";
	echo "</ul>\n";
	echo "</li>\n";

	// Programs Submenu
	echo "<li>\n";
	echo "Programs\n";
	echo "<ul>\n";
	echo "<li>\n";
	echo "<a href=\"perfprog.php\">Performance Programs</a>\n";
	echo "</li>\n";
	echo "</ul>\n";
	echo "</li>\n";

	// Clubs Submenu
	echo "<li>\n";
	echo "Clubs\n";
	echo "<ul>\n";
	echo "<li>\n";
	echo "<a href=\"clubs.php\">Club List</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"branches.php\">Branch List</a>\n";
	echo "</li>\n";
	echo "</ul>\n";
	echo "</li>\n";
	
	// Members Submenu
	echo "<li>\n";
	echo "Members\n";
	echo "<ul>\n";
	echo "<li>\n";
	echo "<a href=\"membersearch.php\">Member Search</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"memberlist.php\">Member List</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"importmembers.php\">Import IMG Members</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"importre1.php\">Import RE1 Members</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"userlist.php\">User List</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"juserlink.php\">Link Joomla Users</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"importuser.php\">Import Joomla Users</a>\n";
	echo "</li>\n";
	echo "</ul>\n";
	echo "</li>\n";
	
	// Admin Submenu
	echo "<li>\n";
	echo "Admin\n";
	echo "<ul>\n";
	echo "<li>\n";
	echo "<a href=\"logviewer.php\">Log Viewer</a>\n";
	echo "</li>\n";
	echo "<li>\n";
	echo "<a href=\"login.php?logout=yes\">Log Out</a>\n";
	echo "</li>\n";
	echo "</ul>\n";
	echo "</li>\n";
	
	echo "</ul>\n";
	echo "</nav>\n";
	echo "</div>\n";

	?>
	
	<script>
    	$('#responsive-menu-button').sidr({
      	name: 'sidr-main',
      	source: '#navigation'
    	});

	</script>
	
	<?php
	
}

?>