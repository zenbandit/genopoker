<?php

require_once('includes.php');

do_html_header("All Players");
echo '<div class="row"><div class="col">';
echo '<a class="btn btn-primary" href="index.php" role="button"> <span class="glyphicon glyphicon-menu-left"></span>Home</a><br><br>'; 
overall_results(0);

echo '</div></div>';
	
do_html_footer();
