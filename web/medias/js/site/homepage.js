$(document).ready(function()
{
	$('ul#myTab a').click(function(event) {
		event.preventDefault();
		$(this).tab('show');
	});
});
