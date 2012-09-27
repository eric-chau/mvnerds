/**
 * Permet de gérer l'affichage du détail des champions lors du clic sur leurs miniatures
 */
jQuery(function($) {
		
	var options = {
		itemSelector: '.champion-isotope',
		layoutMode: 'masonry',
		itemPositionDataEnabled: true,
		masonry: {
			columnWidth: 10
		}
	};
	return;
	$('#champion-comparison').isotope(options);
	
	//Lors du clic sur un champion miniature
	$('#champion-comparison').on('click', 'li.champion-mini', function(){
		
		$(this).toggle(
		'slow',
		function(){
			$(this).removeClass('champion-mini');
			$(this).addClass('champion-maxi');

			$('#champion-comparison').isotope( 'reLayout')

			$(this).toggle('slow');
		});
		
		return false;
	});
	//Lors du clic sur un champion maximisé
	$('#champion-comparison').on('click', 'li.champion-maxi', function(){
		
		$(this).toggle(500, function(){
			$(this).removeClass('champion-maxi');
			$(this).addClass('champion-mini');

			$('#champion-comparison').isotope( 'reLayout')

			$(this).toggle('slow');
		});		
		
		return false;
	});
});