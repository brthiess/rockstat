//Search Value
var input = "";

//Hammer Value for Tiles
var globalHammer = 1;

$(document).ready(function(){
  $("#search-button").click(function(){
	event.preventDefault();
	getInput();
	showResult();
  });
  $('#search-stats').keyup(function(e) {
	if(e.keyCode == 13) {
		getInput();
		showResult();
    }
   });
	$('body').on('click','.tile',function(){
		var tile_class = $(this).attr('class');
		console.log(tile_class);
		showTile(tile_class);
	});
	
	$('body').on('click','.back-button-selected-img',function(){
		console.log("Heello");
		showResult();
	});
	$('body').on('click','.back-button-img',function(){
		console.log("Heello");
		showResult();
	});
	$('body').on('click','.winning-percentage-tiles-hammer-icon',function(){
		console.log("Heello");
		switchHammerResults(1);
	});
	$('body').on('click','.winning-percentage-tiles-not-hammer-icon',function(){
		console.log("Heello");
		switchHammerResults(0);
	});
});

function switchHammerResults(hammer) {
	if(hammer == globalHammer) {
		return;
	}
	else {
		globalHammer = hammer;
		if (globalHammer == 1) {
			$(".hammer-tiles .end-percentage").fadeTo("slow", 1, function() {});
			$(".hammer-tiles").zIndex(10);
			$(".not-hammer-tiles .end-percentage").fadeTo("slow", 0, function() {});
			$(".not-hammer-tiles").zIndex(5);
			$(".winning-percentage-tiles-hammer-icon").css("border-bottom-color", "#2ecc71");
			$(".winning-percentage-tiles-not-hammer-icon").css("border-bottom-color", "transparent");
			$(".winning-percentage-tiles-not-hammer-icon .selected-img").css("opacity", "0");
			$(".winning-percentage-tiles-hammer-icon .selected-img").css("opacity", "1");
			$(".winning-percentage-tiles-hammer-icon .default-img").css("opacity", "0");
			$(".winning-percentage-tiles-not-hammer-icon .default-img").css("opacity", "1");
		}
		else {
			$(".hammer-tiles .end-percentage").fadeTo("slow", 0, function(){});
			$(".hammer-tiles").zIndex(5);
			$(".not-hammer-tiles .end-percentage").fadeTo("slow", 1, function(){});
			$(".not-hammer-tiles").zIndex(10);
			$(".winning-percentage-tiles-not-hammer-icon").css("border-bottom-color", "#2ecc71");
			$(".winning-percentage-tiles-hammer-icon").css("border-bottom-color", "transparent");
			$(".winning-percentage-tiles-not-hammer-icon .selected-img").css("opacity", "1");
			$(".winning-percentage-tiles-hammer-icon .selected-img").css("opacity", "0");
			$(".winning-percentage-tiles-hammer-icon .default-img").css("opacity", "1");
			$(".winning-percentage-tiles-not-hammer-icon .default-img").css("opacity", "0");
		}
	}
}

function getInput() {
	input=$("#search-stats").val();
}

function showResult() {
	if (input != ""){
		var target = document.getElementById('spinner');
		var spinner = new Spinner(opts).spin(target);
		
		//$("#search-container").animate({marginBottom:"+=400px"});
		$.ajax({
			type:"post",
            url:"data/search.php",
            data:"input="+input,
            success:function(data){				
				$("#search-container").html(data);
				$("#chart-container").html("");
				$("#search").val("");
				spinner.stop();
            }
        });
	}
	//Grab input from search
}

function showTile(tile_class){
	$("#search-container").html('');
	var target = document.getElementById('spinner');
	var spinner = new Spinner(opts).spin(target);
	var tile = tile_class.split(" ");
	var tile_type = tile[3];
	var tile_name = tile[4];
	
	$.getScript("data/Chart.min.js", function(data, textStatus, jqxhr) {
		console.log("Load was performed");	
		$.ajax({
			type:"post",
			url:"data/tile-mobile.php",
			data:{"tile_type": tile_type, "tile_name": tile_name},
			success:function(data){
				spinner.stop();
				$("#chart-container").html(data);
				}
		});
	});
}


var opts = {
  lines: 13, // The number of lines to draw
  length: 0, // The length of each line
  width: 7, // The line thickness
  radius: 15, // The radius of the inner circle
  corners: 1, // Corner roundness (0..1)
  rotate: 0, // The rotation offset
  direction: 1, // 1: clockwise, -1: counterclockwise
  color: '#fff', // #rgb or #rrggbb or array of colors
  speed: 1.2, // Rounds per second
  trail: 60, // Afterglow percentage
  shadow: false, // Whether to render a shadow
  hwaccel: false, // Whether to use hardware acceleration
  className: 'spinner', // The CSS class to assign to the spinner
  zIndex: 2e9, // The z-index (defaults to 2000000000)
  top: '80%', // Top position relative to parent
  left: '50%' // Left position relative to parent
};
