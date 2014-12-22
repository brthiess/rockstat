<?php

	echo '	
        <!-- pie chart  canvas element -->
		<div class="row">
			<div class="col-sm-1">
			</div>
			<div class="col-sm-11 back-button-container">
				<a href="#search-container"><img class="back-button-img" src="tiles/back-button.png"></a>
				<a href="search-container"><img class="back-button-selected-img" src="tiles/back-button-selected.png"></a>
			</div>
		</div>
		<div class="row row-centered">
			<div class="col-sm-6 big-tile game-wins col-centered">
				<div class="num-of-games">560 Games</div>
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
			<div class="col-sm-3 big-square-tile col-centered">
			
			</div>
			<div class="col-sm-3 big-square-tile col-centered">
			
			</div>
			<div class="col-sm-3 big-square-tile col-centered">
			
			</div>
			<div class="col-sm-3 big-square-tile col-centered">
			
			</div>
		</div>
		
        <script>
            // pie chart data
            var pieData = [
                {
                    value: 20,
                    color:"#878BB6"
                },
                {
                    value : 40,
                    color : "#4ACAB4"
                },
                {
                    value : 10,
                    color : "#FF8153"
                },
                {
                    value : 30,
                    color : "#FFEA88"
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
          
        </script>';
?>
