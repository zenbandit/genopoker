<?php
require_once('../includes.php');

do_html_header('Add Results');

$conn = db_connect();
$tid = $_POST['tid'];
$num_paid = $_POST['num_paid'];
$player_arr = $_POST['player'];
$bounty = $_POST['bounty'];
$num_players = $_POST['num_players'];

// make sure all players are only in the array once
if ($player_arr !== array_unique($player_arr)) {
	throw new Exception ('Players can only be assigned to one place.');
}

// check that results have not already been entered for this tournament
$result = $conn->query("SELECT * FROM `cash_results` WHERE `tournament_id` = ".$tid." AND `cash_result_type` > 1 ");
if($result->num_rows <> 0) {	
	throw new Exception('This tournament already has results.');
} 

$place_array = array('First', 'Second', 'Third', 'Fourth', 'Fifth', 'Sixth', 'Seventh');
$n = 6;

//add cashes
foreach ($player_arr as $place) {	
	$result = $conn->query("INSERT INTO `cash_results` (`id`, `tournament_id`, `player_id`, `cash_result_type`) VALUES (NULL, '".$tid."', '".$place."', '".$n."')");	
	$n += 1;	
}

// if this is a bounty game add bounties lost and bounties taken
if ($bounty > 0) {
	
	// all players lose one bounty except the first place winner
	$result = $conn->query("SELECT `players`.`id`, `players`.`player` 
	FROM `cash_results` 
	LEFT JOIN `players` ON `cash_results`.`player_id` = `players`.`id` 
	WHERE ((`cash_results`.`tournament_id` =".$tid.") AND (`cash_results`.`cash_result_type` = 1) AND (`players`.`id` NOT IN 
		(SELECT `player_id` FROM `cash_results` 
		WHERE `tournament_id` = ".$tid." AND `cash_result_type` = 6 )) ) 
		ORDER BY `players`.`player` ASC ");		
		
	while($row = $result->fetch_row()) {
		// insert bounty lost
		$insert = $conn->query("INSERT INTO `cash_results` (`id`, `tournament_id`, `player_id`, `cash_result_type`) VALUES (NULL, '".$tid."', '".$row[0]."', '4')");
	}
	
	// each player will get a bounty taken result for the number of bounties they earned
	$result = $conn->query("SELECT `players`.`id`, `players`.`player` 
	FROM `cash_results` 
	LEFT JOIN `players` ON `cash_results`.`player_id` = `players`.`id` 
	WHERE ((`cash_results`.`tournament_id` =".$tid.") AND (`cash_results`.`cash_result_type` = 1)) 
		ORDER BY `players`.`player` ASC ");
	
	while($row = $result->fetch_row()) {	
		// post bounties won
		$b_num = $_POST[$row[0]];
		if ($b_num > 0) {
			// players may take multiple bounties in a game
			foreach(range(1,$b_num) as $index) {
				$insert = $conn->query("INSERT INTO `cash_results` (`id`, `tournament_id`, `player_id`, `cash_result_type`) VALUES (NULL, '".$tid."', '".$row[0]."', '5')");
			}
		} 
		
	}
	
}
echo 'Results were added.<br><a class="btn btn-primary" href="admin.php" role="button">&laquo; Back</a>';		
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	