$(document).ready(function(){
  $("#search-button").click(function(){
    var target = document.getElementById('spinner');
  var spinner = new Spinner(opts).spin(target);
  $(".search-bar").animate({marginTop:"+=400px"});
  });
  $("#men-leaderboard-heading").click(function() {
	 $.get("js/leaderboard.php", {"accordion": "men_leaderboard"}, processResult);
  });
  $("#women-leaderboard-heading").click(function() {
	 $.get("js/leaderboard.php", {"accordion": "women_leaderboard"}, processResult);
  });
  $("#men-scores-heading").click(function() {
	 $.get("js/scores.php", {"accordion": "men_scores"}, processResult);
  });
  $("#women-scores-heading").click(function() {
	 $.get("js/scores.php", {"accordion": "women_scores"}, processResult);
  });
  $("#men-schedule-heading").click(function() {
	 $.get("js/schedule.php", {"accordion": "men_schedule"}, processResult);
  });
  $("#women-schedule-heading").click(function() {
	 $.get("js/schedule.php", {"accordion": "women_schedule"}, processResult);
  });
});

function processResult(data, textStatus) {
	console.log(data);
	$("#leaderboard-table-container-men").html(data);
}

$(document).ready(function() { 
        $("#leaderboard-table-mens").tablesorter({sortList: [[1,0]]}); 
		$("#leaderboard-table-womens").tablesorter({sortList: [[1,0]]}); 
		$("#accordion").accordion({active: false, collapsible: true, animate: true, heightStyle: "content"});
    }
); 



var opts = {
  lines: 13, // The number of lines to draw
  length: 0, // The length of each line
  width: 11, // The line thickness
  radius: 35, // The radius of the inner circle
  corners: 1, // Corner roundness (0..1)
  rotate: 0, // The rotation offset
  direction: 1, // 1: clockwise, -1: counterclockwise
  color: '#999', // #rgb or #rrggbb or array of colors
  speed: 1.2, // Rounds per second
  trail: 60, // Afterglow percentage
  shadow: false, // Whether to render a shadow
  hwaccel: false, // Whether to use hardware acceleration
  className: 'spinner', // The CSS class to assign to the spinner
  zIndex: 2e9, // The z-index (defaults to 2000000000)
  top: '50%', // Top position relative to parent
  left: '50%' // Left position relative to parent
};
