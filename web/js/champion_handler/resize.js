/**
 * Permet de gérer l'affichage du détail des champions lors du clic sur leurs miniatures
 */
jQuery(function($) {
		
	var options = {
		itemSelector: '.champion-isotope',
		layoutMode: 'masonry',
		transformsEnabled: false,
		masonry: {
			columnWidth: 10
		}
	};
	var options2 = {
		itemSelector: '.champion-isotope',
		layoutMode: 'masonry',
		transformsEnabled: true,
		masonry: {
			columnWidth: 10
		}
	};
	$container = $('#champion-comparison');
	
	$container.imagesLoaded( function(){
		$container.isotope(options);
	});
	//Lors du clic sur un champion miniature
	$container.on('click', 'li.champion-mini', function(){
		//$container.isotope('option', options2);
		$(this).animate({
			width: 'hide',
			height: 'hide',
			opacity: 0
		},500, function(){
			$(this).addClass('champion-maxi');
			$(this).removeClass('champion-mini');

			$container.isotope( 'reLayout');

			$(this).animate({
				height: 'show',
				opacity: 1}, 500
			);
			//$container.isotope('option', options);
		});
		return false;
	});
	//Lors du clic sur un champion maximisé
	$container.on('click', 'li.champion-maxi', function(){
		
		$(this).animate({
			width: 'hide',
			height: 'hide',
			opacity: 0
		},500, function(){
			$(this).addClass('champion-mini');
			$(this).removeClass('champion-maxi');

			$container.isotope( 'reLayout');

			$(this).animate({
				height: 'show',
				width: 'show',
				opacity: 1}, 500
			);
			//$container.isotope('option', options);
		});
		return false;
	});
});