<?php

require_once('../includes.php');
do_html_header('Register');
$conn = db_connect();

// check if form empty
if(empty($_POST['date'] ) || empty($_POST['player'])) {
	throw new Exception("No player was selected, or date was not selected.");
} else {
	// get date and players array
	$tid = $_POST['date'];
	$player_arr = $_POST['player'];	
	
	//check if tournament full
	$result = $conn->query("SELECT * FROM `cash_results` WHERE `tournament_id` = ".$tid." AND `cash_result_type` = 1 ");
	$pre_registered = $result->num_rows;
	
	$total = $pre_registered + count($player_arr);
	if($total >= 21) {
		echo "<p>Sorry, that exceeds the maximum number of players.</p>";
	} else {		
		
		// attempt to register each player
		foreach ($player_arr as $register) {
		
			// Check if player is already registered
			$result = $conn->query("SELECT * FROM `cash_results` WHERE `tournament_id`= ".$tid." AND `player_id` = ".$register);
			$row = $result->fetch_row();
			if (!empty($row)) {
				echo "Player is already registered";
			} else {		

			// insert player
				$result = $conn->query("INSERT INTO `cash_results` (`id`, `tournament_id`, `player_id`, `cash_result_type`) VALUES (NULL, '".$tid."', '".$register."', '1'); ");
				if(!$result) {
					throw new Exception("Unable to register player(s)");
				}else{
					echo "<p><strong>Player has been registered.</strong></p>";
			
				}
			
			}
		}
	}

}
echo 'There are currently '.$total.' players registered for this tourney.<br>';
echo '<a class="btn btn-primary" href="admin.php" role="button"> <span class="glyphicon glyphicon-menu-left"></span><< Admin Home</a><br><br>';

	do_html_footer();
	?>

