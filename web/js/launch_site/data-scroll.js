/**
* Permet de faire coulisser le contenu actuel du container #champion-comparison
* pour faire apparaitre à sa place le contenu ciblé par le lien fourni en paramètre
*/
jQuery(function(){
	
	var container = $('#champion-comparison');	
	
	//On affecte aux liens qui n'ont pas la classe disabled la fonction de scroll lors du clic
	 $('#wrapper').on('click', '.data-pagination:not(.disabled)', function(){
		
		//On récupère le lien
		var href = this.href;
		//Si le lien a l attribut rel et que c est next on coulisse vers la gauche sinon a droite
		var pos = this.rel == 'next' ? '-150%' : '150%';
		
		//On récupère le contenu a faire glisser
		container.find('div.data-scrollable').animate({
			left: pos,
			opacity: 0
		}, 'slow', function(){
			//On affiche le chargement
			container.addClass('loading');
			
			//Une fois que le précédent contenu a disparu on fait une requete ajax pour récupérer
			//le contenu ciblé par le lien
			$.get(
				href,
				{format: 'html' },
				function(data){
					//On stop le chargement
					container.removeClass('loading');
					//On remplace le contenu
					container.html(data);
					
					var $isotope = $('#isotope-list');
					
					if($isotope.size() > 0){
						$isotope.imagesLoaded( function(){
							$isotope.isotope({
								itemSelector: '.champion',
								transformsEnabled: false,
								animationEngine: 'jquery',
								masonry: {
									columnWidth: 124
								}
							});
						});
						
						//Bloquage du drag sur les champions agrandis
						$isotope.on('mouseover', 'li.champion-maxi', function(){
							$(this).draggable('disable');
						});
						
						$isotope.on('click', 'li.champion:not(.champion-maxi)', function(){
							$(this).toggleClass('champion-maxi');
							$isotope.isotope( 'reLayout');
							return false;
						});
						//Lors du clic sur un champion maximisé
						$isotope.on('click', 'li.champion-maxi', function(){
							$(this).removeClass('champion-maxi');
							$isotope.isotope( 'reLayout');
							$(this).draggable('enable');
							return false;
						});
					}
				},
				'html'
			);
		});
		return false;
	});
});