<?php
function read_players() {
	$conn = db_connect();
	// fetch all player name and type
	$result = $conn->query("SELECT `players`.`player`, `player_types`.`name` FROM `player_types` LEFT JOIN `players` ON `players`.`player_type_id` = `player_types`.`id` ORDER BY player ASC ");
	if(!$result) {
		throw new exception('Could not retrieve players');
	} else {
		echo '<table class="table table-sm">';		
		foreach ($result as $player) {
			
			//do not show archived players
			if($player['name'] !== "Archived") {
			echo '<tr><td>'.$player['player'].'</td><td>'.$player['name'].'</td></tr>';
			}
		}
		echo '</table>';
	}
}
	
function insert_player($new_player) {
	
	// add new player to db 
	echo "Attempting to add ".htmlspecialchars($new_player)."<br />";
	
	
	$conn = db_connect();
	
	//check for no repeat
	$result = $conn->query("SELECT * FROM `players` 
							WHERE `player` = '".$new_player."' ");
	
		if($result && ($result->num_rows>0)) {
			throw new Exception('Player is already in database.');
		}
		
		// insert new Player 
		if (!$conn->query("INSERT INTO `players` (`id`, `player`, `password`, `player_type_id`) VALUES (NULL, '".$new_player."', '', '1');")) {
			throw new Exception('Player could not be inserted.');
		}
						   
		return true;
}


function archive_player($archive_player) {
	
	// toggle archive/unarchive
	echo "Attempting to archive/unarchive ".htmlspecialchars($archive_player)."<br />";	
	
	$conn = db_connect();
	
	//check player exists in db
	$result = $conn->query("SELECT `id`,`player_type_id` FROM `players` 
	WHERE `player` = '".$archive_player."'");
	if(empty($result)) {
			throw new Exception('Player is not in database.');
		} 
	$row = $result->fetch_row();
	
	$ptype = $row[1];
	$pid = $row[0];
	
	// change status from 3 to 1 or vice versa
	if ($ptype == 3) {
		$archive_status = 1;
	} elseif ($ptype == 1) {
		$archive_status = 3;
	} else {
		throw new Exception('Archive status of this player is unclear');
	}
	
		
	// update player to new status
		if (!$conn->query("UPDATE `players` 
		SET `player_type_id`= '".$archive_status."' 
		WHERE `id` = '".$pid."' ")) {
			throw new Exception('Player type could not be changed.');
		}
						   
		return true;
}


function register_tourney($tourney_id, $player_id) {
	$conn = db_connect();
	
	// check  tournament and player exist in db
	if (!$result = $conn->query("SELECT `tournaments`.`id`, `players`.`id` FROM `tournaments`, `players` WHERE ((`tournaments`.`id` = ".$tourney_id.") AND (`players`.`id` = ".$player_id.")) ")) {		
		
		printf("Error: %s\n", $result->error);
	} else {
		
		// check player is'nt already registered
		$result = $conn->query("SELECT * FROM `results` WHERE `tournament_id` = ".$tourney_id." AND `player_id` = ".$player_id." ");
		if ($result->num_rows > 0) {
			echo '<div class="alert alert-danger" role="alert">Player has already been registered for this tournament.</div>';
			exit;
		} else {
			
			// make sure tournament is not full
			$result=$conn->query("SELECT * FROM `results` WHERE `tournament_id` =". $tourney_id);
			$players = $result->num_rows;
			if($players >= 20) {
				echo "This tournament is full.";
			} else {
				
				// attempt to register player		
				if (!$result = $conn->query("INSERT INTO `results` (`id`, `tournament_id`, `player_id`, `paid_in`, `bounty_lost`, `cash_out`, `bounty_won`) VALUES (NULL, '".$tourney_id."', '".$player_id."', '".$amount_paid."', '0', '0', '0');")) {
					  
					printf("Error: %s\n", $result->error);			
				} else {			
					echo '<div class="alert alert-success" role="alert">Registration was successful</div>';
				}	
			}
	
		}		
	}			
}
	


















?>