//Permet de gérer le filtre d isotope

(function($)
{	
	//Appelé lorsqu'un changement survient dans l'input typeahead
	//Si filter value n est pas null on change la valeur du filtre [data-name]
	$.fn.setNameFilter = function(filterValue) {
		if(filterValue != '') {
			//On enleve la precedente valeur du filtre de nom
			this.removeFilterValue(this.typeaheadValue);
			//On enregistre la nouvelle valeur dans la variable destinée à cet effet dans l objet $isotope
			this.typeaheadValue = "[data-name*='" + filterValue.toLowerCase()+"']";
			//On ajoute la nouvelle valeur du filtre
			this.addFilterValue(this.typeaheadValue);
		} else {
			this.removeFilterValue(this.typeaheadValue);
		}
		
		return this;
	}

	//Permet d ajouter un filtre a la liste
	//La value peut être une classe dans le cas du clic sur un tag
	//Ou au filtre [data-name] si le typeahead a été modifié
	$.fn.addFilterValue = function (value) {
		//On ajoute la nouvelle classe a la liste des filtres
		this.options['filter'] += value;
		this.isotope(this.options);
		
		return this;
	}

	//Permet de retirer un filtre de la liste
	//Uniquement appellé lors du clic sur un tag déjà sélectionné
	$.fn.removeFilterValue = function (value) {
		if(value != undefined && value != ''){
			//On retire le filtre de la liste en le remplaçant par une chaine vide
			this.options['filter'] =  this.options['filter'].replace(value, '');
			this.isotope(this.options);
		}
		
		return this;
	}

	//Permet de nettoyer completement le filtre
	$.fn.cleanFilter = function ($filterInput) {
		//On vide le typeahead
		$filterInput.val('');
		//Et on vide la liste des filtres
		this.options['filter'] = '';
		this.isotope(this.options);
		
		return this;
	}
})(jQuery);