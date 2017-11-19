<?php

require_once('includes.php');

do_html_header("Geno's Poker Emporium");
echo '<div class="row"><div class="col">';
echo '<h3>Top Ten</h3>';
overall_results(10);
echo '<a href="all_players.php"><p>View All Players</p></a>';
echo '</div><div class="col">';
echo '<h3>Recent Results</h3>';
read_tournaments(2);
echo '<a href="all_tournaments.php"><p>View All Tournaments</p></a>';
echo '</div></div>';

do_html_footer();



?>

  
   