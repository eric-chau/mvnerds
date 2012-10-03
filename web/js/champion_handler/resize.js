/**
 * Permet de gérer l'affichage du détail des champions lors du clic sur leurs miniatures
 */

var options = getIsotopeOptions();
	
$isotope = $('#isotope-list');
	
function initIsotope($isotope){
	$isotope.imagesLoaded( function(){
		$isotope.isotope(options);
	});
	
	//Bloquage du drag sur les champions agrandis
	$isotope.on('mouseover', 'li.champion-maxi', function(){
		$(this).draggable('disable');
	});
	
	//Lors du clic sur un champion miniature
	var timeout, dblClic = false, that;
	$isotope.on('click', 'li.champion:not(.champion-maxi)', function(e){console.log('click');
		that = this;
		
		if(!$(that).hasClass('animating')){
			e.preventDefault();
			timeout = setTimeout(function() {
				if (!dblClic){
					timeout = null;
					maximizeChampion($(that), $isotope);
				}
				else {
					dblClic = false;
				}
			}, 200);
		}
	}).on('dblclick', function(){
		if(!$(that).hasClass('champion-maxi') && !$(that).hasClass('animating')){
			clearTimeout(timeout);
			timeout = null;
			dblClic = true;
			addChampionToList($(that).attr('id'));
		}
	});
	//Lors du clic sur le bouton close d un champion maximisé
	$isotope.on('click', 'li.champion-maxi div.preview-header', function(){
		return minimizeChampion($('#'+$(this).attr('data-dissmiss')), $isotope);
	});
}
	
function maximizeChampion($champ, $isotope){
	$champ.addClass('animating');
	
	//Si on trouve un autre champion déjà maximisé on le referme
	var $maxiChampion = $isotope.find('li.champion-maxi');
	if($maxiChampion != undefined){
		minimizeChampion($maxiChampion , $isotope);
	}
	
	$champ.find('div.portrait').fadeOut(250);
	$champ.addClass('champion-maxi');
	setTimeout(function() 
	{
		$champ.find('div.preview').fadeIn(250);
		$isotope.isotope( 'reLayout', function(){
			setTimeout(function(){
				scrollToChampion($('#'+$champ.attr('id')))
				$champ.removeClass('animating');
				},
				100
			);
		});
	},
	320);

	return false;
}

function minimizeChampion($champ, $isotope){
	$champ.addClass('animating');
	$champ.find('div.preview').fadeOut(150);
	setTimeout(function() 
	{
		$champ.find('div.portrait').fadeIn(300);
		
		$champ.toggleClass('champion-portrait champion-maxi');
		setTimeout(function() 
		{
			$isotope.isotope( 'reLayout');
			$champ.removeClass('champion-portrait');
			$champ.removeClass('animating');
		},
		320);
	},
	150);

	$champ.draggable('enable');

	return false;
}

function scrollToChampion($champ){
	var position = $champ.position().top + $('#champion-comparison-center').position().top - 200;
	$('body,html').animate({scrollTop:position},500);
}

jQuery(function($) {
	initIsotope($isotope);
});
