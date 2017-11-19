<?php

require_once('includes.php');

do_html_header("All Tournaments");

echo '<div class="row"><div class="col">';
echo '<a class="btn btn-primary" href="index.php" role="button"> <span class="glyphicon glyphicon-menu-left"></span>Home</a><br><br>'; 
read_tournaments(0);

echo '</div></div>';
	
do_html_footer();