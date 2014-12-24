<?php

$PLAYER = 0;
$TEAM = 1;
$EVENT = 2;
$GENDER = 3;


$input = filter_input(INPUT_POST, 'input');
$pieces = explode(" ", $input);

try {
	$con = new PDO('mysql:host=localhost;dbname=rockstat', "root", "adsfasdf");
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$player_results = array();
	$stmt = $con->prepare("SELECT * FROM Player Where FirstName LIKE :name OR LastName LIKE :name");
    $stmt->bindParam(':name', $name);
	for ($i = 0; $i < count($pieces); $i++) {	
		$name = '%' . $pieces[$i] . '%' ;
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$player_results = array_merge($player_results, $result);
	}
	//Check for good matches.  Remove bad matches
	$duplicates = array();
	for($i = 0; $i < count($player_results); $i++){
		//Iterate through all players that matched initial search.  
		$first_name = $player_results[$i]["FirstName"];
		$last_name = $player_results[$i]["LastName"];
		$similarity = 0;
		foreach($pieces as $p){
			$similarity += min(levenshtein($p, $first_name), levenshtein($p, $last_name));
		}
		$player_results[$i]["Similarity"] = $similarity;
		$player_results[$i]["Type"] = $PLAYER;
	}
	
	$player_results = array_unique($player_results, SORT_REGULAR);
	
	$stmt = $con->prepare("SELECT * FROM Team Where SkipFirst = :name OR SkipLast = :name OR 
													ThirdFirst = :name OR ThirdLast = :name OR
													SecondFirst = :name OR SecondLast = :name OR
													LeadFirst = :name OR LeadLast = :name
													");
	$team_results = array();
	$stmt->bindParam(':name', $name);
	for($i = 0; $i < count($pieces); $i++) {
		$name = $pieces[$i];
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$team_results = array_merge($team_results, $result);	
	}
	
		//Check for good matches.  Remove bad matches
	for($i = 0; $i < count($team_results); $i++){
		//Iterate through all players that matched initial search.  
		$skip_first = $team_results[$i]["SkipFirst"];
		$third_first = $team_results[$i]["ThirdFirst"];
		$second_first = $team_results[$i]["SecondFirst"];
		$lead_first = $team_results[$i]["LeadFirst"];
		$skip_last = $team_results[$i]["SkipLast"];
		$third_last = $team_results[$i]["ThirdLast"];
		$second_last = $team_results[$i]["SecondLast"];
		$lead_last = $team_results[$i]["LeadLast"];

		$similarity = 0;
		foreach($pieces as $p){
			$similarity += min(levenshtein($p, $skip_first), levenshtein($p, $third_first), 
							   levenshtein($p, $second_first), levenshtein($p, $lead_first),
							   levenshtein($p, $skip_last), levenshtein($p, $third_last),
							   levenshtein($p, $second_last), levenshtein($p, $lead_last));
		}
		$team_results[$i]["Similarity"] = $similarity;
		$team_results[$i]["Type"] = $TEAM;
	}
	
	
	$team_results = array_unique($team_results, SORT_REGULAR);
	
	$all_results = array_merge($player_results, $team_results);
	$similarity = array();
	foreach ($all_results as $key => $row)
	{
		$similarity[$key] = $row["Similarity"];
	}
	array_multisort($similarity, SORT_ASC, $all_results);
	echo "<div class='row row-centered results-container'>";
	$i = 0;
	foreach($all_results as $row){
		//Display Max 8 results
		if ($i > 7) {
			break;
		}
		if ($row["Type"] == $PLAYER){
			if ($row["Similarity"] <= 2) {
				echo "<div class='col-sm-2 col-centered tile player " .  $row['FirstName'] . "-" . $row["LastName"] . "'>
					<a class='tile-link' href='#results-container'></a>
						<div class='tile-logo'>
							<img src='tiles/player-icon.png'>
						</div>
						<div class='player-name'>
							<p>" . $row["FirstName"] . " " . $row["LastName"] . "</p>
						</div>
						<div class='tile-category'>
							<p>Player</p>
						</div>
					</div>";
			}		
		}
		
		else if ($row["Type"] == $TEAM) {
			if ($row["Similarity"] <= 2){
				echo "<div class='col-sm-2 col-centered tile team " . $row["SkipFirst"] . "-" . $row["SkipLast"] . "'>
					<a class='tile-link' href='#results-container'></a>
						<div class='tile-logo'>
							<img src='tiles/team-icon.png'>
						</div>
						<div class='team-name'>
							<p>"  . $row["SkipFirst"] . " " . $row["SkipLast"] . " | " . $row["ThirdFirst"] . " " . $row["ThirdLast"] . " | " . $row["SecondFirst"] . " " . $row["SecondLast"] . " | " . $row["LeadFirst"] . " " . $row["LeadLast"] . "</p>
						</div>
						<div class='tile-category'>
							<p>Team</p>
						</div>
						<div class='team-last-name'>
							" . $row["SkipLast"] . "
						</div>
					</div>";
			}		
		}
		$i += 1;
	}
	echo "</div>";
	
}
catch(PDOException $e){
	echo 'ERROR:' . $e->getMessage();
}


 