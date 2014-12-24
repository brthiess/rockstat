<?php
$tile_name = filter_input(INPUT_POST, 'tile_name');
$pieces = explode("-", $tile_name);

try {
	$con = new PDO('mysql:host=localhost;dbname=rockstat', "root", "asdfasdf");
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$player_results = array();
	$stmt = $con->prepare("SELECT ID FROM Team Where SkipFirst = :first_name AND SkipLast = :last_name OR
													ThirdFirst = :first_name AND ThirdLast = :last_name OR
													SecondFirst = :first_name AND SecondLast = :last_name OR
													LeadFirst = :first_name AND LeadLast = :last_name");
	$stmt->bindParam(':first_name', $pieces[0]);
	$stmt->bindParam(':last_name', $pieces[1]);
	$stmt->execute();
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$stmt = $con->prepare("SELECT * FROM Game Where HammerTeamID = :ID OR OtherTeamID = :ID");
	$stmt->bindParam(':ID', $id);
	$results = array();
	for($i = 0; $i < count($result); $i++) {
		$id = $result[$i]['ID'];
		$stmt->execute();
		$resultss = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$results = array_merge($results, $resultss);
	}
	$numGames = count($results);

}
catch(PDOException $e){
	echo 'ERROR:' . $e->getMessage();
}

$output  = <<<HERE
        <!-- pie chart  canvas element -->
		<div class="row">
			<div class="col-sm-1">
			</div>
			<div class="col-sm-1 back-button-container">
				<img class="back-button-img" src="tiles/back-button.png">
				<img class="back-button-selected-img" src="tiles/back-button-selected.png">
			</div>
			<div class="col-sm-10 title-name">
				Team: $pieces[0] $pieces[1]
			</div>
		</div>
		<div class="row row-centered">
			<div class="col-sm-6 big-tile game-wins col-centered">
				<div class="num-of-games">$numGames Games</div>
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
						Rank
					</div>
					<div class="scoring-frequency-title-img">
						<img src="tiles/hammer-icon.png">
					</div>
				</div>
				<div class="scoring-frequency-table">
					<div class="table-entry">
						<p>11.4%</p>
						<div class="scoring-indicator three">
							3+
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>19.4%</p>
						<div class="scoring-indicator two">
							2
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>39.9%</p>
						<div class="scoring-indicator one">
							1
						</div>
						<div class="table-entry-rank-container">
							<p>17<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>12.0%</p>
						<div class="scoring-indicator blank">
							0
						</div>
						<div class="table-entry-rank-container">
							<p>5<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>11.4%</p>
						<div class="scoring-indicator minus-one">
							-1
						</div>
						<div class="table-entry-rank-container">
							<p>2<sup>nd</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>6.9%</p>
						<div class="scoring-indicator minus-two">
							-2+
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>+.83</p>
						<div class="scoring-indicator net">
							<p>Net</p>
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
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
						Rank
					</div>
				</div>
				<div class="scoring-frequency-table">
										<div class="table-entry">
						<p>11.4%</p>
						<div class="scoring-indicator three">
							2+
						</div>
						<div class="table-entry-rank-container">
							<p>99<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>19.4%</p>
						<div class="scoring-indicator two">
							1
						</div>
						<div class="table-entry-rank-container">
							<p>21<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>39.9%</p>
						<div class="scoring-indicator one">
							0
						</div>
						<div class="table-entry-rank-container">
							<p>5<sup>th</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>12.0%</p>
						<div class="scoring-indicator blank">
							-1
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>11.4%</p>
						<div class="scoring-indicator minus-one">
							-2
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>6.9%</p>
						<div class="scoring-indicator minus-three">
							-3+
						</div>
						<div class="table-entry-rank-container">
							<p>1<sup>st</sup></p>
						</div>
					</div>
					<div class="table-entry">
						<p>+.83</p>
						<div class="scoring-indicator net">
							<p>Net<p>
						</div>
						<div class="table-entry-rank-container">
							<p>2<sup>nd</sup></p>
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
            var buyers = document.getElementById('buyers').getContext('2d');
            // draw line chart
            new Chart(buyers).Line(buyerData, {
			scaleShowGridLines: false,
			scaleFontColor: "#fff"
			});
			
			// get line chart canvas
            var buyers2 = document.getElementById('buyers-2').getContext('2d');
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
HERE;

echo $output;
?>
