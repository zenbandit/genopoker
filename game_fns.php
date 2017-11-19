<?php

function read_tournaments($num_tourns) {
	$conn = db_connect();
	// show results of recent games
	// $num_tourns var sets how many games to show

	
	// if $num_tourns = 0 show all players
	if ($num_tourns == 0) {
		$limit = " ";
	} else {
		$limit = "LIMIT ".$num_tourns;		
	}
	$today = date("Y-m-d");
	
	$result = $conn->query("SELECT `tournaments`.`id`, `tournaments`.`tournament` 
	FROM `tournaments` 
	WHERE `tournament` < '".$today."'  
	ORDER BY `tournaments`.`tournament` DESC ".$limit);
	
	if(!$result) {
		throw new exception('Could not retrieve tournaments');
	}
	
	foreach ($result as $tournament) {			
		$tid = $tournament['id'];
		
	// show results individual tourney, count number of players		
		$result = $conn->query("SELECT `tournaments`.`tournament`, `tournament_types`.`name`, `tournament_types`.`entry`, `tournament_types`.`bounty`, COUNT( DISTINCT `cash_results`.`player_id`) as num_players, `tournaments`.`notes` 
		FROM `tournaments` 
		LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` 
		LEFT JOIN `cash_results` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
		WHERE (`tournaments`.`id` = ".$tid.")");
			if(!$result) {
			throw new Exception('No tournament results');	
		}		
		$row = $result->fetch_row();	
		
		// tournament date type and buyin amount 
		$date = $row[0];
		$type = $row[1];
		$buyin = $row[2];
		$bounty = $row[3];
		$num_players = $row[4];
		$notes = $row[5];

		// only show tournaments with results		
		if ($num_players > 0) {
	
			echo '<strong><a href="ind_tournament.php?tourney='.$tid.'">'.$date.'</a></strong> - <strong>'.$type.'</strong><br>'.$num_players.' Players -  $'.$buyin.' Buyin';		
			
			if ($bounty > 0) {
				echo " - $".$bounty." Bounty";
			} 	
			if (!empty($notes)) {
				echo " - ".$notes;
			} 				
			
			// get payout based on number of players
			$result = $conn->query("SELECT * FROM `payout` WHERE `num_players` = ".$num_players);
			if(!$result) {
				throw new Exception('Could not get payout info.');
			 }
			 $row = $result->fetch_row();		
		 
			// get payout results for all players
			$result = $conn->query("SELECT `players`.`player`, `cash_results_type`.`name`, `cash_results_type`.`sort` FROM `players` 
			LEFT JOIN `cash_results` ON `cash_results`.`player_id` = `players`.`id` 
			LEFT JOIN `tournaments` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
			LEFT JOIN `cash_results_type` ON `cash_results`.`cash_result_type` = `cash_results_type`.`id` 
			WHERE (`tournaments`.`id` = ".$tid.") 
			ORDER BY `cash_results_type`.`sort` ,`players`.`player` ");
			if(!$result) {
				throw new Exception('Could not get payout info.');
			}
			echo '<table class="table table-sm">';	
			$n = 2;	 
			while($row2 = $result->fetch_row()) {
			 
				// show cashes		
				if ($row2[2] < 8) {
					echo '<tr><td>'.$row2[0].'</td><td>'.$row2[1].'</td><td>+$'.$row[$n].'</td></tr>';
					$n++;
				}			
			}echo'</table>';
			
		}		
	}
		
}

function insert_date($date, $notes) {
	
	// add new date to db 
	echo "Attempting to add ".htmlspecialchars($date)."<br />";	
	
	$conn = db_connect();
	
	//check for no repeat
	$result = $conn->query("SELECT * FROM `tournaments` WHERE`tournament`= ".$date."");
	
		if($result && ($result->num_rows>0)) {
			throw new Exception('Date is already in database.');
		}
		
		// insert new Player 
		if (!$conn->query("INSERT INTO `tournaments` (`id`, `tournament`, `notes`) VALUES (NULL, '".$date."', '".$notes."')")) {
			throw new Exception('Date could not be inserted.');
		}
						   
		return true;
}

function recent_cashes($num_tourns) {
	
	// show results of recent games
	// $num_tourns var sets how many games to show
	$conn = db_connect();
	// if $num_tourns = 0 show all players
	if ($num_tourns == 0) {
		$limit = " ";
	}else {
		$limit = "LIMIT ".$num_tourns;
	}
	$today = date("Y-m-d");
	$result = $conn->query("SELECT * FROM `tournaments` 
							WHERE `tournament` < '".$today."' 
							ORDER BY `tournament` DESC ".$limit);
							
	foreach ($result as $cashes) {

		// tournament date and notes and anyone who cashed/bountied
		$tourney = $cashes['id']	; 	
		echo '<a href="ind_tournament.php?tourney='.$tourney.'">'.$cashes['tournament'].'</a><br>';
		echo $cashes['notes']."<br>";
		$result = $conn->query("SELECT `tournaments`.*, `players`.`player`, `results`.`cash_out`, `results`.`bounty_won` 
								FROM `tournaments` 
								LEFT JOIN `results` ON `results`.`tournament_id` = `tournaments`.`id` 
								LEFT JOIN `players` ON `results`.`player_id` = `players`.`id` 
								WHERE ((`tournaments`.`id` = ".$tourney.") AND ((`results`.`cash_out` > 0) OR (`results`.`bounty_won` > 0))) 
								ORDER BY `results`.`cash_out` DESC, `results`.`bounty_won` DESC ");
		if(!$result) {
			throw new Exception('No recent tournaments');	
			}
		if ($result->num_rows==0)	 {
			echo "Results for this tournament are not yet available.<br><hr>"; 
		} else {
					
		echo '<table class="table">';
		echo '<tr><th>Player</th><th>Cash</th><th>Bounty</th></tr>';

		foreach ($result as $tournament) {
			echo '<tr><td>'.$tournament['player'].'</td><td>';		
			echo $tournament['cash_out'].'</td><td>';
			echo $tournament['bounty_won'].'</td></tr>';		
		}
		echo '</table>';
		}
	}	
}

function overall_results($num_show) {	
	$conn = db_connect();
	
	if ($num_show == 0) {
		$limit = '';
	} else {
		$limit = 'LIMIT '.$num_show;
	}
	
	// list all players with results
	$result = $conn->query("SELECT `id`,`players`.`player` 
	FROM `players` 
	WHERE id IN(SELECT DISTINCT`player_id` 
	FROM `cash_results`)" .$limit);
	if(!$result) {
		throw new Exception('Could not get players');
	}	
	// loop through
	foreach($result as $player) {		
		$pid = $player['id'];
		$pname = $player['player'];
		$net = 0;
		
			// get all results for this player
		$result = $conn->query("SELECT `cash_results`.`tournament_id` AS tid, `cash_results`.`cash_result_type` AS crt FROM `players` 
		LEFT JOIN `cash_results` ON `cash_results`.`player_id` = `players`.`id` 
		WHERE (`players`.`id` = ".$pid.")");
		if(!$result) {
			throw new Exception('Could not get player results');
		}	
		
		//assign an amount to the result
		foreach($result as $cash) {
			if(!empty($cash['tid'])) {
				
				$tid = $cash['tid'];
				$crt = $cash['crt'];
				
				// differnt result types are valued in different ways
				switch ($crt) {
					case 1: // subtract entry fees
					$result = $conn->query("SELECT `tournament_types`.`entry` FROM `tournaments` LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` WHERE (`tournaments`.`id` = ".$tid.")");
						$row = $result->fetch_row();						
						$net = $net - $row[0];
						break;
						
					case 2: // this for adjustment	
							// may be needed in the future (e.g. chops)
						break;
						
					case 4:	// this is for bounty
						$result = $conn->query("SELECT `tournament_types`.`bounty` FROM `tournaments` LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` WHERE (`tournaments`.`id` = ".$tid.")");
						$row = $result->fetch_row();						
						$net = $net - $row[0];
						break;						
						
					case 5:
						// this is for bounty
						$result = $conn->query("SELECT `tournament_types`.`bounty` FROM `tournaments` LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` WHERE (`tournaments`.`id` = ".$tid.")");
						$row = $result->fetch_row();						
						$net = $net + $row[0];
						break;
						
					default: 
										
					// add any cash winnings				
					$result=$conn->query("SELECT `payout`.* FROM `payout` WHERE `payout`.`num_players` = (SELECT count( DISTINCT `cash_results`.`player_id`) FROM `tournaments` LEFT JOIN `cash_results` ON `cash_results`.`tournament_id` = `tournaments`.`id` WHERE (`tournaments`.`id` = ".$tid.") ) ");
					$row = $result->fetch_row();
					$key = $crt -4;					
					$net = $net + $row[$key];	
				}				
			}			
		}
			// add player and net to array
		$array[$pname] = $net;		
	} 
	//reverse sort
	arsort($array);
	echo '<table class="table">';
	
		// loop through the array
	foreach($array as $name => $total){
		echo '<tr><td>'.$name.'</td><td>'.$total.'</td></tr>';
	}
	echo '</table>';
} 

function upcoming_tournies($num_tournies) {
	$today = date("Y-m-d");
	global $tourney_date;
	//if today is Friday, we want to show tonight's tourney
	$yesterday = date("Y-m-d",(strtotime ( '-1 day', strtotime($today))));	

	// show next upcoming tournament
	$conn = db_connect();
	echo "<h3>Next Tournament</h3>";
	$result = $conn->query("SELECT * FROM `tournaments` WHERE `tournament` > '".$yesterday."' ORDER BY `tournament` ASC LIMIT ".$num_tournies);
	if(!$result) {
		throw new Exception('Could not get upcoming tournies');
	}	
	foreach ($result as $upcoming) {
		$tourney_date = $upcoming['tournament'];
		$tid = $upcoming['id'];
		// links to register page
		echo '<a href="register_tourney.php?tourney='.$tid.'">'.$tourney_date.'</a><br>';		
		
		$result = $conn->query("SELECT `players`.`player`, `cash_results`.`cash_result_type`, `cash_results`.`tournament_id`, `tournament_types`.`entry` 
		FROM `cash_results` 
		LEFT JOIN `players` ON `cash_results`.`player_id` = `players`.`id` 
		LEFT JOIN `tournaments` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
		LEFT JOIN `tournament_types` O
		N `tournaments`.`tournament_type_id` = `tournament_types`.`id` 
		WHERE ((`cash_results`.`cash_result_type` = 1) AND (`cash_results`.`tournament_id` = ".$tid."))");	
		
		$num_registered = $result->num_rows;		
		echo '<table class="table"><tr><th>'.$num_registered.' Registered Players</th><th>Amount Paid</th></tr>';	
		foreach  ($result as $registered_player) {
			echo '<tr><td>'.$registered_player['player'].'</td><td>'.$registered_player['entry'].'</td></tr>';			
		}
		echo '</table>';	
	}	
}

function tournament_details($tourney) {
	$conn = db_connect();

	// get tournament details
	$result = $conn->query("SELECT `tournaments`.`tournament`, `tournaments`.`notes`, `players`.`player`, `results`.`cash_out` 
							FROM `tournaments` 
							LEFT JOIN `results` ON `results`.`tournament_id` = `tournaments`.`id` 
							LEFT JOIN `players` ON `results`.`player_id` = `players`.`id` 
							WHERE (`tournaments`.`id` = ".$tourney.") 
							ORDER BY `results`.`cash_out` DESC ");
	if(!$result) {
		throw new Exception('No recent tournaments');	
	}

	$count = 1;
	foreach ($result as $tournament) {
		if ($count == 1) {
			do_html_header($tournament['tournament']);
			echo '<div class="row"><div class="col">';
			echo $tournament['notes']."<br>";
			echo '<table class="table">';
		}

		echo '<tr><td>'.$tournament['player'].'</td><td>';
		if ($tournament['cash_out'] > 0) {
			echo '$ '.$tournament['cash_out'];
		}
		echo '</td></tr>';
		$count = $count + 1;
	}
}

function tournament_results($tid) {
	$conn = db_connect();
	
	// show results individual tourney, count number of players		
	$result = $conn->query("SELECT `tournaments`.`tournament`,  `tournament_types`.`name`, `tournament_types`.`entry`, `tournament_types`.`bounty`, COUNT( DISTINCT `cash_results`.`player_id`) as num_players , `tournaments`.`notes` FROM `tournaments` 
	LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` 
	LEFT JOIN `cash_results` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
	WHERE (`tournaments`.`id` = ".$tid.")");
	
	if(!$result) {
		throw new Exception('No tournament results');	
	}
	$row = $result->fetch_row();
		
	// tournament date type and buyin amount 
	$date = $row[0];
	$type = $row[1];
	$buyin = $row[2];
	$bounty = $row[3];
	$num_players = $row[4];
	$notes = $row[5];
	 echo "<h2>".$date."</h2>";
	 echo "<h3>".$type."</h3>";
	 echo "<h3> $".$buyin." Buyin</h3>";
	 if ($row[3] > 0) {
		 echo "<h3> $".$bounty." Bounty</h3>";
	 }
	 echo "<h3>".$num_players." Players</h3>";
	  if (!empty($row[5]) ) {
		 echo "<h4>".$notes."</h4>";
	 }
	 
	 // get payout based on number of players
	 $result = $conn->query("SELECT * FROM `payout` WHERE `num_players` = ".$num_players);
	 if(!$result) {
		 throw new Exception('Could not get payout info.');
	 }
	 $row = $result->fetch_row();
	 
	// get payout results for all players
	$result = $conn->query("SELECT `players`.`player`, `cash_results_type`.`name`, `cash_results_type`.`sort` FROM `players` 
	LEFT JOIN `cash_results` ON `cash_results`.`player_id` = `players`.`id` 
	LEFT JOIN `tournaments` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
	LEFT JOIN `cash_results_type` ON `cash_results`.`cash_result_type` = `cash_results_type`.`id` 
	WHERE (`tournaments`.`id` = ".$tid.") 
	ORDER BY `cash_results_type`.`sort` ,`players`.`player` ");
	if(!$result) {
		 throw new Exception('Could not get payout info.');
	}
	$n = 2;
	echo'<table class="table"><tr>';
	while($row2 = $result->fetch_row()) {
		 
		// show results
		echo '<td>'.$row2[0].'</td><td>'.$row2[1].'</td>';
		
		switch ($row2[2]) {
			case 15: // entry fees
				echo '<td>-$'.$buyin.'</td></tr>';
				break;
				
			case 11: // bounty won
				echo '<td>+$'.$bounty.'</td></tr>';
				break;
				
			case 12: //bounty lost
				echo '<td>-$'.$bounty.'</td></tr>';
				break;
						
			default: // show cash winnings				
				echo '<td>+$'.$row[$n].'</td></tr>';
				$n++;
		}			
	}
	echo'</table>';
}

function get_tourney_count($tid) {
	$conn = db_connect();
	
	// show results individual tourney, count number of players		
	$result = $conn->query("SELECT `tournaments`.`tournament`, `tournament_types`.`name`, `tournament_types`.`entry`, `tournament_types`.`bounty`, COUNT( DISTINCT `cash_results`.`player_id`) as num_players FROM `tournaments` 
		LEFT JOIN `tournament_types` ON `tournaments`.`tournament_type_id` = `tournament_types`.`id` 
		LEFT JOIN `cash_results` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
		WHERE (`tournaments`.`id` = ".$tid.")");
		if(!$result) {
			throw new Exception('No tournament results');	
		}
		
		$row = $result->fetch_row();
		
		// tournament date type and buyin amount 
		$date = $row[0];
		$type = $row[1];
		$buyin = $row[2];
		$bounty = $row[3];
		$num_players = $row[4];
		return $row;
}

?>







