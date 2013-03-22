$(document).ready(function()
{
	$('ul#myTab a, ul#video-tab a').click(function(event) {
		event.preventDefault();
		$(this).tab('show');
	});
});
