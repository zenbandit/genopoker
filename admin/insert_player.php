<?php

require_once('../includes.php');
$new_player = $_POST['player'];

do_html_header('Adding Player');

$conn = db_connect();
try {
		
	insert_player($new_player);
	echo '<div class="alert alert-success" role="alert">Player '.htmlspecialchars($new_player).' was added to the database.</div>';  	
}
catch (Exception $e) {
	echo $e->getMessage();
}
echo '<a class="btn btn-primary" href="insert_player_form.php" role="button">&laquo; Back</a>';

do_html_footer();

?>





	

 
     
    
    
  
  

  
  
  