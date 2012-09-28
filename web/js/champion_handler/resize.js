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
		$champ.toggleClass('champion-maxi');
		$isotope.isotope( 'reLayout');
		return false;
	}
	function minimizeChampion($champ, $isotope){
		$champ.removeClass('champion-maxi');
		$isotope.isotope( 'reLayout');
		$champ.draggable('enable');
		return false;
	}
});