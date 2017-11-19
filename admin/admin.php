<?php
require_once('../includes.php');
$conn = db_connect();
do_html_header('Admin');
echo '<div class="row"><div class="col-sm">';
		
// register players 
display_accept_entry();
		
echo '</div><div class="col-sm">';
 
// add results for date
display_insert_date_form('add_results.php');
	echo '<br><a class="btn btn-primary" href="insert_player_form.php" role="button">Add/Edit Players</a>';	

// echo '<br><a class="btn btn-primary" href="insert_date.php" role="button">Add/Edit Dates</a>';	
echo '</div></div>';	

do_html_footer();


?>
