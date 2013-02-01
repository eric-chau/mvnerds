$(document).ready(function()
{
	// Activation de l'event de clic sur le header d'un skill pour l'agrandir ou le rétrécir
	$('div.block.skill div.header').on('click', function() {
		$(this).find('i').toggleClass('icon-resize-small icon-resize-full');
		$(this).parent().find('div.body').slideToggle();
	});

	// Activation de l'event de clic sur la flèche pour voir plus pour l'histoire d'un champion
	$('a.read-more').on('click', function(event) {
		event.preventDefault();
		$(this).find('i').toggleClass('icon-double-angle-down icon-double-angle-up');
		$(this).parent().find('p').toggleClass('active');
	});

	// Activation d'isotope pour les skins
	/*$('#skin-list').isotope({
	  // options
	  itemSelector : '.skin',
	  layoutMode : 'fitRows'
	});*/

	var $skinsList = $('div#skin-list');
	$('div.skin').on('click', function() {
		if ($(this).hasClass('active')) {
			return false;
		}

		$skinsList.find('div.skin').removeClass('active');
		$skinsList.prepend($(this));
		$(this).addClass('active');
	});
});
