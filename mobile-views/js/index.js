$(document).ready(function(){
  $("#search-button").click(function(){
	showResult();
  });
  $('#search-stats').keyup(function(e) {
	if(e.keyCode == 13) {
		showResult();
    }
   });
});

function showResult() {
	var input=$("#search-stats").val();
	if (input != ""){
		var target = document.getElementById('spinner');
		var spinner = new Spinner(opts).spin(target);
		$(".search-container").animate({marginBottom:"+=200px"});
		$.ajax({
			type:"post",
            url:"data/search.php",
            data:"input="+input,
            success:function(data){				
				$("#search-container").html(data);
				$("#search").val("");
				spinner.stop();
            }
        });
	}
	//Grab input from search
}



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
