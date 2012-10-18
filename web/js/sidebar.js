$(document).ready(function()
{
	var $sidebar = $('div#sidebar');

	$('div#sidebar ul a').on('click', function()
	{
		return false;
	});

	$sidebar.on('click', function() 
	{
		$sidebar.toggleClass('active');
	});
});

