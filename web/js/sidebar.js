$(document).ready(function()
{
	var $sidebar = $('div#sidebar'),
		$toggleSidebar = $('div#sidebar div.toggle-sidebar'),
		$sidebarTooltipAnchor = $('.sidebar-tooltip-anchor'),
		$rememberMeElements = $('div#user-container form label, input#remember_me');

	$sidebarTooltipAnchor.tooltip();

	$('div#sidebar ul a').on('click', function(event)
	{
		event.stopPropagation();
	});

	$sidebar.on('click', function() 
	{
		if ($sidebar.hasClass('active')) {
			return false;
		}

		$sidebar.addClass('active');
		$sidebarTooltipAnchor.tooltip('disable');
	});

	$toggleSidebar.on('click', function(event)
	{
		event.stopPropagation();
		if ($sidebar.hasClass('active')) {
			$sidebar.removeClass('active');
			$sidebarTooltipAnchor.tooltip('enable');
		}
	});

	$rememberMeElements.on('click', function(event)
	{
		event.stopPropagation();
	});
});

