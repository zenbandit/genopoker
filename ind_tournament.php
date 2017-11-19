<?php

require_once('includes.php');
$tourney = $_GET['tourney'];

do_html_header('Results');
echo '<div class="row"><div class="col">';
echo '<a class="btn btn-primary" href="index.php" role="button"> <span class="glyphicon glyphicon-menu-left"></span>Home</a>'; 
tournament_results($tourney);
	
echo '</div></div>';

do_html_footer();




?>
