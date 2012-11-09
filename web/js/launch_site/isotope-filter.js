//Permet de gérer le filtre d isotope

(function($)
{		
	filterGameModes = ['classic', 'dominion', 'aram', 'twisted-treeline'];
	
	//Raffraichit le filtre
	$.fn.refreshFilter = function() {
		var tags = '';
		for (var i=0; i<this.filters.tags.length; i++) {
			tags += this.filters.tags[i];
		}
		if (this.filters.championSpecificFilter != undefined) {
			tags += this.filters.championSpecificFilter;
		}
		
		if (this.filters.gameMode != undefined) {
			if (this.filters.championItemsFilter != undefined) {
				this.options['filter'] = '.shared'+tags+this.filters.name+':not(.champion-specific), .'+this.filters.gameMode+tags+this.filters.name+':not(.champion-specific), '+this.filters.championItemsFilter+tags+this.filters.name;
			} else {
				this.options['filter'] = '.shared'+tags+this.filters.name+', .'+this.filters.gameMode+tags+this.filters.name;
			}
		} else {
			this.options['filter'] = tags+this.filters.name;
		}
		this.isotope(this.options);

		return this;
	}
	
	//Appelé lorsqu'un changement survient dans l'input typeahead
	//Si filter value n est pas null on change la valeur du filtre [data-name]
	$.fn.setNameFilter = function(filterValue) {
		if(filterValue != '') {
			//On enregistre la nouvelle valeur dans la variable destinée à cet effet dans l objet $isotope
			this.filters.name = "[data-name*='" + filterValue.toLowerCase()+"']";
		} else {
			this.filters.name='';
		}
		this.refreshFilter();
		
		return this;
	}
	
	//Appelé lorsqu'un changement survient dans le game mode
	$.fn.setGameModeFilter = function(gameMode) {
		if(gameMode != '' && filterGameModes.indexOf(gameMode)>=0) {
			this.filters.gameMode = gameMode;
			this.refreshFilter();
		}	
		
		return this;
	}	

	//Permet d ajouter un filtre a la liste
	//La value peut être une classe dans le cas du clic sur un tag
	//Ou au filtre [data-name] si le typeahead a été modifié
	$.fn.addFilterValue = function (value) {
		this.filters.tags.push(value);
		this.refreshFilter();
		
		return this;
	}

	//Permet de retirer un filtre de la liste
	//Uniquement appellé lors du clic sur un tag déjà sélectionné
	$.fn.removeFilterValue = function (value) {
		if(value != undefined && value != ''){
			this.filters.tags.splice(this.filters.tags.indexOf(value), 1);
			//On retire le filtre de la liste en le remplaçant par une chaine vide
			this.refreshFilter();
		}
		
		return this;
	}

	//Permet de nettoyer completement le filtre
	$.fn.cleanFilter = function ($filterInput) {
		//On vide le typeahead
		$filterInput.val('');
		//Et on vide la liste des filtres
		this.filters.tags = [];
		this.filters.name = '';
		this.refreshFilter();
		
		return this;
	}
	
	//Permet d'initialiser les evenements onchange et onkeyup de l inpt typeahead
	//Et permet de préparer l autocompletion en récupérant les noms des isotopes.
	$.fn.initTypeahead = function ($filterInput, route, $cleanFilter) {
		$filterInput.off('keyup change');
		var that = this;
		//lorsqu'un  changement survient dans le typeahead
		$filterInput.on('keyup change',function(){
			that.setNameFilter($filterInput.val());
			activateButton($cleanFilter);
		});
		//Chargement de l autocompletion du champ de recherche
		if (route != undefined) {
			$.ajax({
				type: 'POST',
				url:  route, 
				dataType: 'html'
			}).done(function(data){
				$filterInput.attr('data-source', data);
			});
		}
		
		return this;
	}
	
	$.fn.hideChampionSpecificItems = function() {
		this.filters.championSpecificFilter = ':not(.champion-specific)';
		this.filters.championItemsFilter = undefined;
		this.refreshFilter();
		
		return this;
	}
	$.fn.showChampionSpecificItems = function($championSelectedSlug) {
		if ($championSelectedSlug != undefined ) {
			this.filters.championItemsFilter = '[data-champion="'+$championSelectedSlug+'"]';
		} else {
			this.filters.championItemsFilter = undefined;
		}
		this.filters.championSpecificFilter = undefined;
		
		this.refreshFilter();
		
		return this;
	}
})(jQuery);