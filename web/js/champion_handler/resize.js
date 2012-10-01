/**
 * Permet de gérer l'affichage du détail des champions lors du clic sur leurs miniatures
 */

jQuery(function($) {
		
	var options = getIsotopeOptions();
	
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
		//Si on trouve un autre champion déjà maximisé on le referme
		var $maxiChampion = $isotope.find('li.champion-maxi');
		if($maxiChampion != undefined){
			minimizeChampion($maxiChampion , $isotope);
		}
		return maximizeChampion($(this), $isotope);
	});
	//Lors du clic sur un champion maximisé
	$isotope.on('click', 'li.champion-maxi', function(){
		return minimizeChampion($(this), $isotope);
	});
	
	function maximizeChampion($champ, $isotope){
		$champ.find('div.portrait').fadeOut(250);
		$champ.addClass('champion-maxi');
		setTimeout(function() 
		{
			$champ.find('div.preview').fadeIn(250);
			$isotope.isotope( 'reLayout', function(){
				setTimeout(function(){
					scrollToChampion($('#'+$champ.attr('id')))
					},
					100
				);
			});
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
		
	function scrollToChampion($champ){
		var position = $champ.position().top + $('#champion-comparison-center').position().top - 20;
		console.log($champ.position());
		$('body,html').animate({scrollTop:position},500);
	}
});
