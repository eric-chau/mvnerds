/**
* Permet de faire coulisser le contenu actuel du container #champion-comparison
* pour faire apparaitre à sa place le contenu ciblé par le lien fourni en paramètre
*/
jQuery(function(){
	//On affecte aux liens qui n'ont pas la classe disabled la fonction de scroll lors du clic
	 $('#wrapper').on('click', '.data-pagination:not(.disabled)', function(){
		var container = $('#champion-comparison');	
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
				},
				'html'
			);
		});
		return false;
	});
});