<?php

// posted tournament id
$tid = $_POST['tid'];
require_once('../includes.php');
do_html_header('Add Results');

$conn = db_connect();
echo '<div class="row"><div class="col-sm">';
	
// get names & number of  players in tourney
get_tourney_count($tid);
$result = $conn->query("SELECT `tournaments`.`tournament`, `tournament_types`.`name`, `tournament_types`.`entry`, `tournament_types`.`bounty`, COUNT( DISTINCT `cash_results`.`player_id`) as num_players 
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

// get number of payouts
$result = $conn->query("SELECT `places_paid` FROM `payout` WHERE `num_players` = ".$num_players);

// list first, second, etc. in form , each with players dropdown.
$row = $result->fetch_row();
$x = $row[0];
?>
<form method="post" action="accept_results.php">
	<div class="form-group">
	<h3>Add Cashes</h3>	
			
<?php			
			
$n = 3;
while($n <= ($x+2)) {
				
	echo $n - 2 .' Place ';;
	echo ' <select name="player[]" ><option selected disabled value="">-- select a player --</option>';
	$result = $conn->query("SELECT `players`.`id`, `players`.`player`
	FROM `cash_results`
    LEFT JOIN `players` ON `cash_results`.`player_id` = `players`.`id`
    LEFT JOIN `tournaments` ON `cash_results`.`tournament_id` = `tournaments`.`id`
	WHERE ((`tournaments`.`id`  = ".$tid.") AND (`cash_results`.`cash_result_type` = 1)) ORDER BY player ASC");
	while ($row = $result->fetch_assoc()) {
	
	// dropdown with all players registered for this tourney	
	echo '<option value="'.$row['id'].'">'.$row['player'].'</option>';				
	} 
	echo '</select><br><br>';
	$n += 1;
}
			
// allow for bounties
if($bounty > 0) {
	
// get all players, list with text input
	$result = $conn->query("SELECT `players`.`id`, `players`.`player` 
	FROM `cash_results` 
	LEFT JOIN `players` ON `cash_results`.`player_id` = `players`.`id` 
	WHERE ((`cash_results`.`tournament_id` =".$tid.") AND (`cash_results`.`cash_result_type` = 1))
ORDER BY `players`.`player` ASC	");
	
	while($row = $result->fetch_row()) {
		//print_r($row);
		echo '<label>'.$row[1] .' <input type="text" name="'.$row[0].'" value="0" ></label><br>';
	}				
}				

?>		
			
	<input type="hidden" name="tid" value="<?php echo $tid; ?>">
	<input type="hidden" name="num_paid" value="<?php echo $x; ?>">
	<input type="hidden" name="num_players" value="<?php echo $num_players; ?>">
	<input type="hidden" name="bounty" value="<?php echo $bounty; ?>">
	<button class="btn btn-primary" type="submit">Add Results</button>&nbsp;
	<button class="btn btn-primary" type="reset">Reset</button>
	</div></form>
	
<?php
echo '</div><div class="col-sm">';
echo '</div></div>';
do_html_footer(); 























?>	