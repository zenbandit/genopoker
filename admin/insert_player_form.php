<?php

require_once('../includes.php');

do_html_header('All Players');

$conn = db_connect();

echo '<div class="row"><div class="col">';
display_all_players();
echo '</div>';

echo '<div class="col">';
display_insert_player_form(); 
display_toggle_archive_form();
echo '</div></div>';

do_html_footer();








 
     
    
    
  
  
  ?>
  
  
  