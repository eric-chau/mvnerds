$(document).ready(function()
{
	var $sidebar = $('div#sidebar'),
		$toggleSidebar = $('div#sidebar div.toggle-sidebar'),
		$sidebarTooltipAnchor = $('.sidebar-tooltip-anchor'),
		$rememberMeElements = $('div#user-container form label, input#remember_me');

	// Si c'est la première fois que l'utilisateur se connecte, la sidebar doit être déroulé
	if (getItemFromLS('is-first-visit') != 'false') {
		saveItemInLS('is-first-visit', false);
		$('div#sidebar').addClass('active');
	}
	
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

	$('div#sidebar a').on('click', function(event)
	{
		event.stopPropagation();
	});

	$('a.btn-connection').on('click', function()
	{
		event.preventDefault();
		event.stopPropagation();
		$('form#sidebar-login-form').submit();
	});

	
	// Soumission du formulaire de connexion si l'utilisateur presse la touche entrée
	$('form#sidebar-login-form input').on('keypress', function(event)
	{
		if (event.which == 13) {
			$('form#sidebar-login-form').submit();
		}
	});
});

