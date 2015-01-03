<?php

$PLAYER = 0;
$TEAM = 1;
$EVENT = 2;
$GENDER = 3;


$input = filter_input(INPUT_POST, 'input');
$pieces = explode(" ", $input);

try {
	$con = new PDO('mysql:host=localhost;dbname=rockstat', "root", "jikipol");
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	
	/**********************
	Get Player Results
	**********************/
	$player_results = array();
	$stmt = $con->prepare("SELECT * FROM Player Where FirstName LIKE :name OR LastName LIKE :name");
    $stmt->bindParam(':name', $name);
	for ($i = 0; $i < count($pieces); $i++) {	
		$name = $pieces[$i];
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
	
	
	/**********************
	Get Team Results
	**********************/
	//Get Teams that match search result
	$stmt = $con->prepare("SELECT TeamID FROM PlayerTeam Where PlayerID = :ID");
	$team_results = array();
	$stmt->bindParam(':ID', $id);
	$stmt->execute();
	//Iterate through each player ID and grab all potential team ids
	$allTeamIds = array();
	for($i = 0; $i < count($player_results); $i++) {
		$id = $player_results[$i]["ID"];
		#Get Team Ids for current player
		$stmt->execute();
		$teamIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
		for($j = 0; $j < count($teamIds); $j++){
			$teamIds[$j]["Similarity"] = $player_results[$i]["Similarity"];
		}
		$allTeamIds = array_merge($allTeamIds, $teamIds);	
	}

	$team_results = array();
	//Get Teams from team IDs
	for($i = 0; $i < count($allTeamIds); $i++){
		$stmt = $con->prepare("SELECT * FROM PlayerTeam, Player WHERE TeamID = :TeamsID AND Player.ID = PlayerTeam.PlayerID ORDER BY Position ASC");
		$teamID = $allTeamIds[$i]["TeamID"];
		$stmt->bindParam(':TeamsID', $teamID);
		$stmt->execute();
		$team = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$team["Type"] = $TEAM;
		$team["Similarity"] = $allTeamIds[$i]["Similarity"];
		array_push($team_results, $team);
	}
	
	
	/************************
	Merge all search results
	*************************/
	$all_results = array_merge($player_results, $team_results);
	$similarity = array();
	foreach ($all_results as $key => $row)
	{
		$similarity[$key] = $row["Similarity"];
	}
	
	array_multisort($similarity, SORT_ASC, $all_results);
	$all_results = array_unique($all_results, SORT_REGULAR);
	$all_results = array_unique($all_results, SORT_REGULAR);
	echo "<div class='row row-centered results-container'>";
	$i = 0;
	foreach($all_results as $row){
		//Display Max 12 results
		if ($i > 11) {
			break;
		}
		if ($row["Type"] == $PLAYER){
			if ($row["Similarity"] <= 2) {
				echo "<div class='col-sm-2 col-centered tile player " .  $row['FirstName'] . "-" . $row["LastName"] . " id-" . $row["ID"] . "'>
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
				echo "<div class='col-sm-2 col-centered tile team " . $row[3]["FirstName"] . "-" . $row[3]["LastName"] . " id-" . $row[3]["TeamID"] . "'>
					<a class='tile-link' href='#results-container'></a>
						<div class='tile-logo'>
							<img src='tiles/team-icon.png'>
						</div>
						<div class='team-name'>
							<p>"  . $row[3]["FirstName"] . " " . $row[3]["LastName"] . " | " . $row[2]["FirstName"] . " " . $row[2]["LastName"] . " | " . $row[1]["FirstName"] . " " . $row[1]["LastName"] . " | " . $row[0]["FirstName"] . " " . $row[0]["LastName"] . "</p>
						</div>
						<div class='tile-category'>
							<p>Team</p>
						</div>
						<div class='team-last-name'>
							" . $row[3]["LastName"] . "
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


 