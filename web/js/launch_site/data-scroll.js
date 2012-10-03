/**
* Permet de faire coulisser le contenu actuel du container #champion-comparison
* pour faire apparaitre à sa place le contenu ciblé par le lien fourni en paramètre
*/
jQuery(function(){
	
	var container = $('#champion-comparison'),
		liToShow = [],
		liToShowCount = 0;	
	
	function loadData(data){
		//On remplace le contenu
		container.html(data);

		for (i = 0; i < liToShowCount; i++) {
			liToShow[i].removeClass('force-hide');
		}

		liToShow = [];
		liToShowCount = 0;

		var $isotope = $('#isotope-list');

		if($isotope.size() > 0){						
			initIsotope($isotope);
			initTypeahead($isotope);
			initFilterList($isotope);
		}
	}
	
	//On affecte aux liens qui n'ont pas la classe disabled la fonction de scroll lors du clic
	 $('#wrapper').on('click', '.data-pagination:not(.disabled)', function() {
		var $this = $(this);
		// On cache les liens selon la page sur laquelle l'utilisateur se rend
		$('div.actions-bar ul.action-buttons li.action').each(function()
		{
			if ($(this).data('page-display') == $this.data('page-display')) {
				liToShow[liToShowCount++] = $(this);
			}
			else {
				$(this).addClass('force-hide');
			}
		});

		//On récupère le lien
		var href = $(this).attr('data-target');
		//Si le lien a l attribut rel et que c est next on coulisse vers la gauche sinon a droite
		var pos = this.rel == 'next' ? '-150%' : '150%';
		if (Modernizr.history) {
			history.pushState(location.pathname, '', href);
		}
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
					loadData(data);
				},
				'html'
			);
		});
		return false;
	});
	
	var initialPath = location.pathname;
    	
	$(window).bind('popstate', function() {
		if (location.pathname == initialPath) {
			initialPath = null;
			return;
		}
		container.find('div.data-scrollable').hide();
		//On affiche le chargement
		container.addClass('loading');
		$.get(location.pathname, {
			format: 'html'
		}, function(data){
			//On stop le chargement
			container.removeClass('loading');
			loadData(data);
		}, 'html');
	});
});