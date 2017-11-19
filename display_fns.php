<?php

function do_html_header($title) {
	//print an HTML header	
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo $title;?></title>
    <!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
  </head>
  <body>
  <div class="container">
  <?php	
}

function do_html_footer() {	
// print HTML footer
?>
	</div>	
	<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>     
	<script src="bootstrap-datepicker.min.js"></script>
	
	<script>
	$('.datepicker').datepicker({
    format: 'yyyy-mm-dd',
    daysOfWeekHighlighted: '5'
	});
	</script>
	
	</body>
	</html>
	<?php
}

function do_html_heading($heading) {
	
//print heading as H1
?>
<h1><?php echo $heading; ?></h1>
<?php
}

function display_all_players() {
	// list all players in db_connect

	$conn = db_connect();
	$result = $conn->query("SELECT * FROM `players` ORDER BY `player` ASC ");
							
	if(!$result) {
		throw new Exception('Could not get player list.');
	}

	echo '<ul class="list-group">';
	foreach ($result as $player) {
		
		// gray out archived players
		if($player['player_type_id'] == 3) {
			echo '<li class="list-group-item disabled">'.$player['player'].'</li>';
		} else {
			echo '<li class="list-group-item ">'.$player['player'].'</li>';
		}
	}
	echo "</ul>";
}

function display_insert_player_form() {
	
// add new player form
?>	
	<form method="post" action="insert_player.php">
		<div class="form-group">
			<h2>Add A Player To The Database</h2>
			<p><label for="player">Player:</label>
			<input type="text" class="form-control" name="player" id="player" /></p>			
			<button class="btn btn-primary" type="submit">Add To Database</button>			
		</div>
	</form>
<?php
}

function  display_toggle_archive_form(){

// toggles player status between 'archived' and 'standard'
?>	
	<form method="post" action="toggle_archive.php">
		<div class="form-group">
			<h2>Archive/Unarchive Player</h2>
			<p><label for="player">Player:</label>
			<input type="text" class="form-control" name="player" id="player" /></p>			
			<button class="btn btn-primary" type="submit">Toggle Archive/Unarchive</button>			
		</div>
	</form>
<?php  
}

function display_insert_date_form($url) {
$conn = db_connect();		
?>	
	<form method="post" action="<?php echo $url; ?>">
		<div class="form-group">
			<h3>Add Results</h3>
			<p><label for="date">Date:</label>
			<select  name="tid" id="tid" />
			<?php
			$result = $conn->query("SELECT `tournaments`.`id`, `tournaments`.`tournament`, `cash_results`.`cash_result_type` 
			FROM `cash_results` 
			LEFT JOIN `tournaments` ON `cash_results`.`tournament_id` = `tournaments`.`id` 
			WHERE (`cash_results`.`cash_result_type` = 1) 
			GROUP BY `tournaments`.`id` ORDER BY `tournaments`.`tournament` ");
			foreach ($result as $no_result) {									
				echo '<option value="'.$no_result['id'].'">'.$no_result['tournament'].'</option>';				
			}
?>
			</select><br>
		<button class="btn btn-primary" type="submit">Add Results</button>
		</div>
	</form>
<?php
}
	
function display_remove_date_form() {
?>	
	<form method="post" action="remove_date.php">
		<div class="form-group">
			<h2>Remove A Date From The Database</h2>
			<p><label for="date">Date:</label>
			<select class="form-control" name="date" id="date" />			
<?php
	foreach( $result as $game) {
		echo '<option value="'.$game["id"].'">'.$game["tournament"].'</option>';			
	}
?>		
			</select></p>			
			<button class="btn btn-primary" type="submit">Remove Date</button>			
		</div>
	</form>	
<?php
}		
	
function display_accept_entry() {
	
	// register players for tourney
	$conn = db_connect();	
?>	
<form method="post" action="accept_entry.php">
	<div class="form-group">
		<h2>Register Players</h2>	
		<label>Date: </label> 
		<select name="date">
		<option selected disabled value="">-- select date --</option>';
		<?php
		$result = $conn->query("SELECT `tournaments`.`id`, `tournaments`.`tournament` 
		FROM `tournaments` 
		WHERE `tournaments`.`id` NOT IN (SELECT `tournament_id` 
											FROM `cash_results` 
											WHERE `cash_result_type` = 6 )
		ORDER BY `tournaments`.`tournament` ASC");
		while($row = $result->fetch_row()) {
			print_r($row);
			echo '<option value="'.$row[0].'" >'.$row[1].'</option>';
		}	
?>
		</select>
	<div class="row">	
<?php

	// all players except archived
	$result = $conn->query("SELECT * FROM `players` WHERE `player_type_id` < 3 ORDER BY `player` ASC");
	foreach( $result as $player) {
		
		// show players with checkbox
		echo '<div class="col-sm-4"><input type="checkbox" name="player[]" class="checkbox-rounded" value="'.$player['id'].'"> <label > '.$player['player'].' </label> </div>';
	}
	echo '</div><button class="btn btn-primary" type="submit">Select</button>&nbsp;';
	echo '<button class="btn btn-primary" type="reset" >Reset</button></div></form>';
}
	
?>