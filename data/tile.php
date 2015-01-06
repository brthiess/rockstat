<?php

$tile_name = filter_input(INPUT_POST, 'tile_name');
//Tile Name
$tile_player_team_name = explode("-", $tile_name);
//Tile Type
$tile_type = filter_input(INPUT_POST, 'tile_type');
//Tile ID
$tile_id = filter_input(INPUT_POST, 'tile_id');
$tile_id = explode("-", $tile_id)[1];

$tile_type_string;
//$teamStats array
$teamStats;


try {
	/***********************************
	Connect To DB
	***********************************/
	$con = new PDO('mysql:host=localhost;dbname=rockstat', "root", "jikipol");
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$player_results = array();
	$team_ids = array();

	/******************
	Check For Tile Type
	******************/
	if ($tile_type == 'player') {
		$tile_type_string = 'Player';
		
		//Get Teams that the specified player has played on
		$stmt = $con->prepare("SELECT TeamID FROM PlayerTeam Where PlayerID = :tile_id");
		$stmt->bindParam(':tile_id', $tile_id);
		$stmt->execute();
		$team_ids = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//For Each Team that the player has played on, get its team stats
		$allTeamStats = array();
		for($i = 0; $i < count($team_ids); $i++) {
			$teamStats = getTeamStats($team_ids[$i]["TeamID"]);
			array_push($allTeamStats, $teamStats);
		}
		$teamStats = calibrateStats($allTeamStats);
	}
	else if ($tile_type == 'team') {
		$tile_type_string = 'Team';
		
		//Get Teams Stats
		$teamStats = getTeamStats($tile_id, $con);
	}



}
catch(PDOException $e){
	echo 'ERROR:' . $e->getMessage();
}

/**
Given a Team ID and returns an associative array with relevant stats
Returns
Array{numGames, wins, losses, ... , netScoringWith, netScoringWithout, playerNames[Position][First or Last Name], ... , EBEAvgScoringWith[], EBEAvgScoringWithout[], ScoringFrequencyWith[], ScoringFrequencyWithout[], WPOT[]} 
*/
function getTeamStats($team_id, $con) {
	
	$teamStats = array();

	$getMainStats = $con->prepare("SELECT * FROM Team WHERE ID = :ID");
	$getMainStats->bindParam(':ID', $team_id);
	$getMainStats->execute();
	$mainStats = $getMainStats->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addMainStats($teamStats, $mainStats);
	
	$getPlayerNames = $con->prepare("SELECT * FROM PlayerTeam, Player WHERE Player.ID = PlayerTeam.PlayerID AND TeamID = :ID ORDER BY Position ASC");
	$getPlayerNames->bindParam(':ID', $team_id);
	$getPlayerNames->execute();
	$playerNames = $getPlayerNames->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addPlayerNames($teamStats, $playerNames);

	$getEBEAvgScoring = $con->prepare("SELECT * FROM EndByEndAvgScoring WHERE TeamID = :ID ORDER BY Hammer, EndNumber ASC");
	$getEBEAvgScoring->bindParam(':ID', $team_id);
	$getEBEAvgScoring->execute();
	$EBEAvgScoring = $getEBEAvgScoring->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addEBE($teamStats, $EBEAvgScoring);
	
	$getfrequencies = $con->prepare("SELECT * FROM ScoringFrequency WHERE TeamID = :ID ORDER BY Score, Hammer ASC");
	$getfrequencies->bindParam(':ID', $team_id);
	$getfrequencies->execute();
	$frequencies = $getfrequencies->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addFrequencies($teamStats, $frequencies);
	
	$getWPOT = $con->prepare("SELECT * FROM WPOT WHERE TeamID = :ID ORDER BY MonthNumber");
	$getWPOT->bindParam(":ID", $team_id);
	$getWPOT->execute();
	$WPOT = $getWPOT->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addWPOT($teamStats, $WPOT);

	return $teamStats;
}
/**
Given An Associative Array representing the Team Table
*/
function addMainStats($teamStats, $mainStats){
	$teamStats["numGames"] = $mainStats[0]["Games"];
	$teamStats["wins"] = $mainStats[0]["Wins"];
	$teamStats["losses"] = $mainStats[0]["Losses"];
	$teamStats["winPercentage"] = $mainStats[0]["WinPercentage"];
	$teamStats["pfg"] = $mainStats[0]["PFG"];
	$teamStats["pag"] = $mainStats[0]["PAG"];
	$teamStats["eventsPlayed"] = $mainStats[0]["EventsPlayed"];
	$teamStats["eventsWon"] = $mainStats[0]["EventsWon"];
	$teamStats["winsWith"] = $mainStats[0]["WinsWith"];
	$teamStats["winsWithout"] = $mainStats[0]["WinsWithout"];
	$teamStats["lossesWith"] = $mainStats[0]["LossesWithout"];
	$teamStats["lossesWith"] = $mainStats[0]["LossesWith"];
	$teamStats["lossesWithout"] = $mainStats[0]["LossesWithout"];
	$teamStats["netScoringWithout"] = $mainStats[0]["NetScoringWithout"];
	$teamStats["netScoringWith"] = $mainStats[0]["NetScoringWith"];
	
	return $teamStats;
	
}

function addEBE($teamStats, $EBE){
	$EBEAvgScoringWith = array();
	$EBEAvgScoringWithout = array();
	
	for($i = 0; $i < count($EBE); $i++){
		if($EBE[$i]["Hammer"] == 1) {
			$EBEAvgScoringWith[$EBE[$i]["EndNumber"]] = $EBE[$i]["Average"];
		}
		else {
			$EBEAvgScoringWithout[$EBE[$i]["EndNumber"]] = $EBE[$i]["Average"];
		}
	}
	$teamStats["EBEAvgScoringWith"] = $EBEAvgScoringWith;
	$teamStats["EBEAvgScoringWithout"] = $EBEAvgScoringWithout;
	return $teamStats;
}

function addFrequencies($teamStats, $frequencies) {
	$hammerFrequencies = array();
	$nonHammerFrequencies = array();
	
	$h = -8;
	$nh = -8;
	
	for($i = 0; $i < count($frequencies); $i++){
		if ($frequencies[$i]["Hammer"] == True) {
			$hammerFrequencies[$h] = $frequencies[$i]["rate"];
			$h += 1;
		}
		else {
			$nonHammerFrequencies[$h] = $frequencies[$i]["rate"];
			$nh += 1;
		}
	}
	$teamStats["hammerFrequencies"] = $hammerFrequencies;
	$teamStats["nonHammerFrequencies"] = $nonHammerFrequencies;
	
	return $teamStats;
}

function addPlayerNames($teamStats, $playerNames) {
	
	$teamNamesArray = array();
	//Iterate through each player, adding their name array to the teamNamesArray
	for($i = 0; $i<4; $i++) {
		$nameArray = array();
		array_push($nameArray, $playerNames[$i]["FirstName"]);
		array_push($nameArray, $playerNames[$i]["LastName"]);
		array_push($teamNamesArray, $playerNames);
	}
	$teamStats["names"] = $teamNamesArray;
	return $teamStats;
}

/**
Returns Assoc Array for each month and associated winning percentage
*/
function addWPOT($teamStats, $WPOT){
	$WMonth = array();
	
	for($i = 0; $i < count($WPOT); $i++) {
		$WMonth[$WPOT[$i]["MonthNumber"]] = round($WPOT[$i]["WinningPercentage"]*100, 1);
	}
	
	$teamStats["WPOT"] = $WMonth;
	return $teamStats;
}

echo '
        <!-- pie chart  canvas element -->
		<div class="row">
			<div class="col-sm-1">
			</div>
			<div class="col-sm-1 back-button-container">
				<img class="back-button-img" src="tiles/back-button.png">
				<img class="back-button-selected-img" src="tiles/back-button-selected.png">
			</div>
			<div class="col-sm-10 title-name">
				' . $tile_type_string . ': ' . $tile_player_team_name[0] . ' ' . $tile_player_team_name[1] . '
			</div>
		</div>
		<div class="row row-centered">
			<div class="col-sm-6 big-tile game-wins col-centered">
				<div class="num-of-games">' . $teamStats["numGames"] . ' Games</div>
				<div class="num-stats">' . $teamStats["wins"] . ' Wins | ' . $teamStats["losses"] . ' Losses | ' . round($teamStats["winPercentage"]*100,0) . '% Win Percentage</div>
				<div class="points-per-game">' . round($teamStats["pfg"],2) . ' Points For / Game | ' . round($teamStats["pag"],2) . ' Points Against / Game</div>
				<div class="events-played">' . $teamStats["eventsPlayed"] . ' Events Played | ' . $teamStats["eventsWon"] . ' Events Won</div>
			</div>
			<div class="col-sm-6 big-tile game-stats col-centered">
				<div class="pie-chart-container">
					<canvas id="teamWins" width="230" height="230"></canvas>
				</div>
				<div class="vertical-divider"><img src="tiles/vertical_divider.png"></div>
				<div class="legend-pie-title">Game Stats</div>
				<div class="legend-square win-with"></div><div class="legend-text-win-with">Wins With</div>
				<div class="legend-square win-without"></div><div class="legend-text-win-without">Wins Without</div>
				<div class="legend-square loss-with"></div><div class="legend-text-loss-with">Losses With</div>
				<div class="legend-square loss-without"></div><div class="legend-text-loss-without">Losses Without</div>
			</div>
		</div>
		<div class="row row-centered">
			<div class="col-sm-3 big-tall-tile scoring-frequency col-centered">
				<div class="scoring-frequency-title">
					<div class="scoring-frequency-title-text">
						Scoring Frequency
					</div>
					<div class="scoring-frequency-title-img">
						<img src="tiles/hammer-icon.png">
					</div>
				</div>
				<div class="scoring-frequency-table">
					<div class="table-entry">
						<p>' . round($teamStats["hammerFrequencies"][8]*100 +
								$teamStats["hammerFrequencies"][7]*100 +
								$teamStats["hammerFrequencies"][6]*100 +
								$teamStats["hammerFrequencies"][5]*100 +
								$teamStats["hammerFrequencies"][4]*100 +
								$teamStats["hammerFrequencies"][3]*100,1) . '%</p>
						<div class="scoring-indicator three">
							3+
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["hammerFrequencies"][2]*100,1) . '%</p>
						<div class="scoring-indicator two">
							2
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["hammerFrequencies"][1]*100,1) . '%</p>
						<div class="scoring-indicator one">
							1
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][0]*100,1) . '%</p>
						<div class="scoring-indicator blank">
							0
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][-1]*100,1) . '%</p>
						<div class="scoring-indicator minus-one">
							-1
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["hammerFrequencies"][-2]*100 +
								$teamStats["hammerFrequencies"][-3]*100 + 
								$teamStats["hammerFrequencies"][-4]*100 + 
								$teamStats["hammerFrequencies"][-5]*100 + 
								$teamStats["hammerFrequencies"][-6]*100 + 
								$teamStats["hammerFrequencies"][-7]*100 + 
								$teamStats["hammerFrequencies"][-8]*100,1) . '%</p>
						<div class="scoring-indicator minus-two">
							-2+
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["netScoringWith"], 2) . '</p>
						<div class="scoring-indicator net">
							<p>Net</p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-3 big-tall-tile scoring-frequency col-centered">
			<div class="scoring-frequency-title">
					<div class="scoring-frequency-title-text">
						Scoring Frequency
					</div>
					<div class="scoring-frequency-title-img">
						<img src="tiles/hammer-icon-not.png">
					</div>
				</div>
				<div class="scoring-frequency-table">
										<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][8]*100 +
								$teamStats["nonHammerFrequencies"][7]*100  +
								$teamStats["nonHammerFrequencies"][6]*100 +
								$teamStats["nonHammerFrequencies"][5]*100 +
								$teamStats["nonHammerFrequencies"][4]*100 +
								$teamStats["nonHammerFrequencies"][3]*100 +
								$teamStats["nonHammerFrequencies"][2]*100,1) . '%</p>
						<div class="scoring-indicator three">
							2+
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][1]*100,1) . '%</p>
						<div class="scoring-indicator two">
							1
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][0]*100,1) . '%</p>
						<div class="scoring-indicator one">
							0
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][-1]*100,1) . '%</p>
						<div class="scoring-indicator blank">
							-1
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][-2]*100,1) . '%</p>
						<div class="scoring-indicator minus-one">
							-2
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["nonHammerFrequencies"][-3]*100 +
								$teamStats["nonHammerFrequencies"][-4]*100 +
								$teamStats["nonHammerFrequencies"][-5]*100 +
								$teamStats["nonHammerFrequencies"][-6]*100 +
								$teamStats["nonHammerFrequencies"][-7]*100 +
								$teamStats["nonHammerFrequencies"][-8]*100,1) . '%</p>
						<div class="scoring-indicator minus-three">
							-3+
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($teamStats["netScoringWithout"], 2) . '</p>
						<div class="scoring-indicator net">
							<p>Net<p>
						</div>
					</div>
				</div>
			
			</div>
			<div class="col-sm-6 big-double-tile col-centered">
				<div class="end-chart">
					<div class="line-chart-title">
							End By End Average Scoring
					</div>
					<div class="line-chart-legend">
						<div class="legend-circle hammer-circle"></div><div class="legend-text-without">With Hammer</div>
						<div class="legend-circle not-hammer-circle"></div><div class="legend-text-with">Without Hammer</div>
					</div>
					<div class="line-chart-container">
						
						<canvas id="ebe" width="500" height="250"></canvas>
					</div>
				</div>
				<div class="winning-percentage-chart">
					<div class="line-chart-title">
						Winning Percentage Month By Month
					</div>
					<div class="winning-percentage-chart-container">
						<canvas id="WPOT" width="500" height="150"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row row-centered">
			<div class="col-sm-6 big-double-tile extended winning-percentage-tiles col-centered">
				<div class="tile-chart-logo">
					<img src="tiles/stat-icon.png">
				</div>
				<div class="tile-chart-title" id="chart-title">
					Winning Percentages By Situation
				</div>
				<div class="winning-percentage-tiles-hammer-icon">
					<img class="default-img" src="tiles/hammer-icon.png">
					<img class="selected-img" src="tiles/hammer-icon-selected.png">
				</div>
				<div class="winning-percentage-tiles-not-hammer-icon">
					<img class="default-img" src="tiles/hammer-icon-not.png">
					<img class="selected-img" src="tiles/hammer-icon-not-selected.png">
				</div>
				<div class="winning-percentage-tiles-container hammer-tiles">
					<div class="mini-tile end-playing-tile">
						<p>End #</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 4+</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 3</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 2</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">Tied</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 1</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 2</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 3</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 4</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">99</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">95</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">77</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">60</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">45</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">33</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">12</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">2</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">4</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">6</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">7</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">8</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">9</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					
				</div>
				<div class="winning-percentage-tiles-container not-hammer-tiles">
					<div class="mini-tile end-playing-tile">
						<p>End #</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 4+</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 3</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 2</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p>Up 1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">Tied</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 1</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 2</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 3</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<div>Down 4</div>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">4</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">6</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">7</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">8</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
					<div class="mini-tile end-playing-tile">
						<p class="end-number">9</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">3.1</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">4.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">5.3</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">6.5</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">7.0</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">8.2</p>
					</div>
					<div class="mini-tile">
						<p class="end-percentage">9.1</p>
					</div>
				</div>
			</div>
		</div>
		
        <script>
		
		// EBE chart data
            var ebeData = {
                labels : ["1","2","3","4","5","6","7","8","9"],
                datasets : [
                {
					label: "Hammer",
                    fillColor : "rgba(46, 204, 113,0.4)",
                    strokeColor : "#ACC26D",
                    pointColor : "#2ecc71",
                    pointStrokeColor : "#9DB86D",
                    data : [' . $teamStats["EBEAvgScoringWith"][0] . ','
							. $teamStats["EBEAvgScoringWith"][1] . ','
							. $teamStats["EBEAvgScoringWith"][2] . ','
							. $teamStats["EBEAvgScoringWith"][3] . ','
							. $teamStats["EBEAvgScoringWith"][4] . ','
							. $teamStats["EBEAvgScoringWith"][5] . ','
							. $teamStats["EBEAvgScoringWith"][6] . ','
							. $teamStats["EBEAvgScoringWith"][7] . ','
							. $teamStats["EBEAvgScoringWith"][8] . ']
                },
				{
					label: "Without Hammer",
                    fillColor : "rgba(231, 76, 60,0.4)",
                    strokeColor : "#ACC26D",
                    pointColor : "#e74c3c",
                    pointStrokeColor : "#9DB86D",
                    data : [' . $teamStats["EBEAvgScoringWithout"][0] . ','
							  . $teamStats["EBEAvgScoringWithout"][1] . ','
							  . $teamStats["EBEAvgScoringWithout"][2] . ','
							  . $teamStats["EBEAvgScoringWithout"][3] . ','
							  . $teamStats["EBEAvgScoringWithout"][4] . ','
							  . $teamStats["EBEAvgScoringWithout"][5] . ','
							  . $teamStats["EBEAvgScoringWithout"][6] . ','
							  . $teamStats["EBEAvgScoringWithout"][7] . ','
							  . $teamStats["EBEAvgScoringWithout"][8] . ']
                }
            ]
            }
            // get line chart canvas
            var ebe = document.getElementById("ebe").getContext("2d");
            // draw line chart
            new Chart(ebe).Line(ebeData, {
			scaleShowGridLines: false,
			scaleFontColor: "#fff"
			});
			
			
			var WPOTData = {
                labels : ["September","October","November","December","January","February","March","April"],
                datasets : [
                {
					label: "WPOT",
                    fillColor : "rgba(46, 204, 113,0.4)",
                    strokeColor : "#ACC26D",
                    pointColor : "#2ecc71",
                    pointStrokeColor : "#9DB86D",
                    data : [' . $teamStats["WPOT"][9] . ','
							. $teamStats["WPOT"][10] . ','
							. $teamStats["WPOT"][11] . ','
							. $teamStats["WPOT"][12] . ','
							. $teamStats["WPOT"][1] . ','
							. $teamStats["WPOT"][2] . ','
							. $teamStats["WPOT"][3] . ','
							. $teamStats["WPOT"][4] . ']
                }
            ]
            }
			
			// get line chart canvas
            var WPOT = document.getElementById("WPOT").getContext("2d");
            // draw line chart
            new Chart(WPOT).Line(WPOTData, {
			scaleShowGridLines: false,
			scaleFontColor: "#fff"
			});

		
            // pie chart data
            var pieData = [
                {
                    value: ' . $teamStats["winsWith"] . ',
                    color:"#2ECC71"
                },
                {
                    value :  ' . $teamStats["winsWithout"] . ',
                    color : "#16A085"
                },
                {
                    value : ' . $teamStats["lossesWith"] . ',
                    color : "#DA532C"
                },
                {
                    value : ' . $teamStats["lossesWithout"] . ',
                    color : "#ee1111"
                }
            ];
            // pie chart options
            var pieOptions = {
                 segmentShowStroke : false,
                 animateScale : true
            }
            // get pie chart canvas
            var teamWins= document.getElementById("teamWins").getContext("2d");
            // draw pie chart
            new Chart(teamWins).Pie(pieData, pieOptions);
          
        </script>

'
?>
