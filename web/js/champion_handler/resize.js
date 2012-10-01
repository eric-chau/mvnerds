/**
 * Permet de gérer l'affichage du détail des champions lors du clic sur leurs miniatures
 */
jQuery(function($) {
		
	var options = {
		itemSelector: '.champion',
		transformsEnabled: false,
		animationEngine: 'jquery',
		masonry: {
			columnWidth: 124
		}
	};
	$isotope = $('#isotope-list');
	
	$isotope.imagesLoaded( function(){
		$isotope.isotope(options);
	});
	
	//Bloquage du drag sur les champions agrandis
	$isotope.on('mouseover', 'li.champion-maxi', function(){
		$(this).draggable('disable');
	});
	
	//Lors du clic sur un champion miniature
	$isotope.on('click', 'li.champion:not(.champion-maxi)', function(){
		return maximizeChampion($(this), $isotope);
	});
	//Lors du clic sur un champion maximisé
	$isotope.on('click', 'li.champion-maxi', function(){
		return minimizeChampion($(this), $isotope);
	});
	
	function maximizeChampion($champ, $isotope){
		/*var $loader = $champ.find('div.loader');
		$loader.show();*/
		$champ.find('div.portrait').fadeOut(250);
		$champ.addClass('champion-maxi');
		setTimeout(function() 
		{
			$champ.find('div.preview').fadeIn(250);
			$isotope.isotope( 'reLayout');
		},
		320);

		return false;
	}
	function minimizeChampion($champ, $isotope){
		$champ.find('div.preview').fadeOut(150);
		setTimeout(function() 
		{
			$champ.find('div.portrait').fadeIn(300);
			$champ.toggleClass('champion-portrait champion-maxi');
			setTimeout(function() 
			{
				$isotope.isotope( 'reLayout');
				$champ.removeClass('champion-portrait');
			},
			320);
		},
		150);
		
		$champ.draggable('enable');

		return false;
	}
});