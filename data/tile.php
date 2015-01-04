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
//Number of Games for Player or Team
$num_games = 0;
//All the games of the selected team(s)
$gameResults = array();
//Scoring frequencies of selected team(s)
$frequencies = array();

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
			$teamStats = getTeamStats($team_ids[$i]["TeamID"];
			array_push($allTeamStats, $teamStats);
		}

	}
	else if ($tile_type == 'team') {
		$tile_type_string = 'Team';
		
		//Get Teams Stats
		getTeamStats($tile_id);
	}



}
catch(PDOException $e){
	echo 'ERROR:' . $e->getMessage();
}

/**
Given a Team ID and returns an associative array with relevant stats
Returns
Array{numGames, wins, losses, ... , netScoringWith, netScoringWithout, LeadFirstName, LeadLastName, ... , EBEAvgScoringWith[], EBEAvgScoringWithout[], ScoringFrequencyWith[], ScoringFrequencyWithout[]} 
*/
function getTeamStats($team_id) {
	
	$teamStats = array();

	$getMainStats = $con->prepare("SELECT * FROM Team WHERE ID = :ID");
	$getMainStats->bindParam(':ID', $team_id);
	$getMainStats->execute();
	$mainStats = $getMainStats->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addMainStats($teamStats, $mainStats);
	
	$getPlayerNames = $con->prepare("SELECT * FROM PlayerTeam, PlayerWHERE Player.ID = PlayerTeam.PlayerID AND TeamID = :ID ORDER BY Position ASC");
	$getPlayerNames->bindParam(':ID', $team_id);
	$getPlayerNames->execute();
	$playerNames = $getPlayerNames->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addPlayerNames($teamStats, $playerNames);
	
	$getEBEAvgScoring = $con->prepare("SELECT * FROM EndByEndAvgScoring WHERE TeamID = :ID");
	$getEBEAvgScoring->bindParam(':ID', $team_id);
	$getEBEAvgScoring->execute();
	$EBEAvgScoring = $getEBEAvgScoring->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addEBE($teamStats, $EBEAvgScoring);
	
	$getfrequencies = $con->prepare("SELECT * FROM ScoringFrequency Where TeamID = :ID");
	$getfrequencies->bindParam(':ID', $id);
	$getfrequencies->execute();
	$frequencies = $getfrequencies->fetchAll(PDO::FETCH_ASSOC);
	
	$teamStats = addFrequencies($teamStats, $Frequencies);
	
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
	$teamStats["eventsWon"] = $mainStats[0]["eventsWon"];
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
	return $teamStats;
}

function addFrequencies($teamStats, $frequencies) {
	$hammerFrequencies = array();
	$nonHammerFrequencies = array();
	
	for($i = 0; $i < count($frequencies); $i++){
		if ($frequencies[$i]["Hammer"] = True) {
			array_push($hammerFrequencies, $frequencies[$i]["Rate"]);
		}
		else {
			array_push($nonHammerFrequencies, $frequencies[$i]["Rate"]);
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
	}
	
	$teamStats["playerNames"] = $nameArray;
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
				<div class="num-of-games">' . $numGames . ' Games</div>
				<div class="num-stats">423 Wins | 177 Losses | 78% Win Percentage</div>
				<div class="points-per-game">6.5 Points For / Game | 3.4 Points Against / Game</div>
				<div class="events-played">67 Events Played | 7 Events Won</div>
			</div>
			<div class="col-sm-6 big-tile game-stats col-centered">
				<div class="pie-chart-container">
					<canvas id="countries" width="230" height="230"></canvas>
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
					<div class="scoring-frequency-title-rank">
						Rank <p>(All Time)</p>
					</div>
					<div class="scoring-frequency-title-img">
						<img src="tiles/hammer-icon.png">
					</div>
				</div>
				<div class="scoring-frequency-table">
					<div class="table-entry">
						<p>' . round(($hammerFrequencies[16]['rate'] +
								$hammerFrequencies[15]['rate'] +
								$hammerFrequencies[14]['rate'] +
								$hammerFrequencies[13]['rate'] + 
								$hammerFrequencies[12]['rate'] +
								$hammerFrequencies[11]['rate'])*100,1) . '%</p>
						<div class="scoring-indicator three">
							3+
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[11]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($hammerFrequencies[10]['rate']*100,1) . '%</p>
						<div class="scoring-indicator two">
							2
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[10]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($hammerFrequencies[9]['rate']*100,1) . '%</p>
						<div class="scoring-indicator one">
							1
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[9]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($hammerFrequencies[8]['rate']*100,1) . '%</p>
						<div class="scoring-indicator blank">
							0
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[8]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($hammerFrequencies[7]['rate']*100,1) . '%</p>
						<div class="scoring-indicator minus-one">
							-1
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[7]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round(($hammerFrequencies[6]['rate'] +
								$hammerFrequencies[5]['rate'] +
								$hammerFrequencies[4]['rate'] +
								$hammerFrequencies[3]['rate'] + 
								$hammerFrequencies[2]['rate'] +
								$hammerFrequencies[1]['rate'] +
								$hammerFrequencies[0]['rate'])*100,1) . '%</p>
						<div class="scoring-indicator minus-two">
							-2+
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[6]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($netScoringWithAvg, 1) . '</p>
						<div class="scoring-indicator net">
							<p>Net</p>
						</div>
						<div class="table-entry-rank-container">
							<p>' . $netScoringWith[0]["Rank"] . '<sup>th</sup></p>
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
					<div class="scoring-frequency-title-rank">
						Rank <p>(All Time)</p>
					</div>
				</div>
				<div class="scoring-frequency-table">
										<div class="table-entry">
						<p>' . round(($nonHammerFrequencies[16]['rate'] +
								$nonHammerFrequencies[15]['rate'] +
								$nonHammerFrequencies[14]['rate'] +
								$nonHammerFrequencies[13]['rate'] + 
								$nonHammerFrequencies[12]['rate'] +
								$nonHammerFrequencies[11]['rate'] +
								$nonHammerFrequencies[10]['rate'])*100,1) . '%</p>
						<div class="scoring-indicator three">
							2+
						</div>
						<div class="table-entry-rank-container">
							<p>' . $nonHammerFrequencies[10]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($nonHammerFrequencies[9]['rate']*100,1) . '%</p>
						<div class="scoring-indicator two">
							1
						</div>
						<div class="table-entry-rank-container">
							<p>' . $nonHammerFrequencies[9]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($nonHammerFrequencies[8]['rate']*100,1) . '%</p>
						<div class="scoring-indicator one">
							0
						</div>
						<div class="table-entry-rank-container">
							<p>' . $nonHammerFrequencies[8]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($nonHammerFrequencies[7]['rate']*100,1) . '%</p>
						<div class="scoring-indicator blank">
							-1
						</div>
						<div class="table-entry-rank-container">
							<p>' . $nonHammerFrequencies[7]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($nonHammerFrequencies[6]['rate']*100,1) . '%</p>
						<div class="scoring-indicator minus-one">
							-2
						</div>
						<div class="table-entry-rank-container">
							<p>' . $nonHammerFrequencies[6]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round(($nonHammerFrequencies[5]['rate'] +
								$nonHammerFrequencies[4]['rate'] +
								$nonHammerFrequencies[3]['rate'] + 
								$nonHammerFrequencies[2]['rate'] +
								$nonHammerFrequencies[1]['rate'] +
								$nonHammerFrequencies[0]['rate'])*100,1) . '%</p>
						<div class="scoring-indicator minus-three">
							-3+
						</div>
						<div class="table-entry-rank-container">
							<p>' . $hammerFrequencies[5]['TeamRank'] . '<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>' . round($netScoringWithoutAvg, 2) . '</p>
						<div class="scoring-indicator net">
							<p>Net<p>
						</div>
						<div class="table-entry-rank-container">
							<p>' . $netScoringWithout[0]["Rank"] . '<sup>th</sup></p>
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
						
						<canvas id="buyers" width="500" height="250"></canvas>
					</div>
				</div>
				<div class="winning-percentage-chart">
					<div class="line-chart-title">
						Winning Percentage Over Time
					</div>
					<div class="winning-percentage-chart-container">
						<canvas id="buyers-2" width="500" height="150"></canvas>
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
		
		// line chart data
            var buyerData = {
                labels : ["1","2","3","4","5","6","7","8"],
                datasets : [
                {
					label: "Hammer",
                    fillColor : "rgba(172,194,132,0.4)",
                    strokeColor : "#ACC26D",
                    pointColor : "#fff",
                    pointStrokeColor : "#9DB86D",
                    data : [2,2,3,4,3,2,1,3]
                }
            ]
            }
            // get line chart canvas
            var buyers = document.getElementById("buyers").getContext("2d");
            // draw line chart
            new Chart(buyers).Line(buyerData, {
			scaleShowGridLines: false,
			scaleFontColor: "#fff"
			});
			
			// get line chart canvas
            var buyers2 = document.getElementById("buyers-2").getContext("2d");
            // draw line chart
            new Chart(buyers2).Line(buyerData, {
			scaleShowGridLines: false,
			scaleFontColor: "#fff"
			});

		
            // pie chart data
            var pieData = [
                {
                    value: 20,
                    color:"#EE1111"
                },
                {
                    value : 40,
                    color : "#16A085"
                },
                {
                    value : 10,
                    color : "#2ECC71"
                },
                {
                    value : 30,
                    color : "#DA532C"
                }
            ];
            // pie chart options
            var pieOptions = {
                 segmentShowStroke : false,
                 animateScale : true
            }
            // get pie chart canvas
            var countries= document.getElementById("countries").getContext("2d");
            // draw pie chart
            new Chart(countries).Pie(pieData, pieOptions);
          
        </script>

'
?>
