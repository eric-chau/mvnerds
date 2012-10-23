function initTypeahead($isotope, $filterInput) {
	$filterInput.off('keyup change');
	//lorsqu'un  changement survient dans le typeahead
	$filterInput.on('keyup change',function(){
		filter($isotope, $filterInput.val());
	});
}

function initFilterList($isotope, $filterButtons) {
	$filterButtons.off('click', ' a.filter-link:not(.selected), a.selected');
	
	//Clic sur un bouton de filtrage non selectionné
	$filterButtons.on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		$(this).parent('a.dropdown-toggle').addClass('active');
		activateCompareFilteredButton();
		addFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		return false;
	});
	//Clic sur un bouton de filtrage sélectionné
	$filterButtons.on('click', 'a.filter-link.selected',function() {
		$(this).removeClass('selected');
		removeFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		if($(this).parent().find('a.filter-link.selected').size() <= 0)
		{
			$(this).parent('a.dropdown-toggle').removeClass('active');
			deactivateCompareFilteredButton();
		}
		return false;
	});	
}

function initCleanAction() {
	//Lors du clic sur le bouton de nettoyage du filtre
	$('#li-clean-filter').on('click', '#btn-clean-fitler', cleanFilter);
}
function initAddFilteredAction() {
	$('#li-compare-filtered').on('click', '#btn-compare-filtered', addFilteredChampions);
}

function initTypeaheadAutocomplete(route, $filterInput) {
	// Routing.generate('champion_handler_front_get_champions_name',{_locale: locale})
	//Initialise l auto completion du typeahead
	$.ajax({
		type: 'POST',
		url:  route, 
		dataType: 'html'
	}).done(function(data){
		$filterInput.attr('data-source', data);
	});
}

function filter($isotope, filterValue) {
	if(filterValue != '') {
		setFilterValue($isotope, "[data-name*='" + filterValue.toLowerCase()+"']");
	}
}

function setFilterValue($isotope, value){
	removeFilterValue($isotope, typeaheadValue);
	typeaheadValue = value;
	addFilterValue($isotope,typeaheadValue);
	$isotope.isotope(options);
}

function addFilterValue($isotope, options, value) {
	options['filter'] = options['filter'] + value;
	activateCleanFilterButton();
	$isotope.isotope(options);
}

function removeFilterValue($isotope, options, value) {
	var oldOptions = options['filter'];
	var newOptions = oldOptions;
	if(value != undefined && value != ''){
		var splitedOptions = oldOptions.split(value);
		newOptions = splitedOptions[0].concat(splitedOptions[1]);
	}
	
	typeaheadValue = undefined;
	if(newOptions == undefined || newOptions == ''){
		deactivateCleanFilterButton();
	}
	options['filter'] = newOptions;
	$isotope.isotope(options);
}

function cleanFilter($filterInput, options) {
	$('a#drop-filter-list').removeClass('active');
	$filterInput.val('');
	options['filter'] = '';
	$('ul#filters-list ul.tags-group a.filter-link.selected').each(function(){
		$(this).removeClass('selected');
	});
	deactivateCleanFilterButton();
	deactivateCompareFilteredButton();
	$isotope.isotope(options);
	return false;
}

//Permet d ajouter tous les champions filtrés à la liste
function addFilteredChampions(){
	var championsSlug = new Array();
	$('#isotope-list li.isotope-item.champion:not(.isotope-hidden)').each(function(){
		championsSlug.push($(this).attr('id'));
	});
	addManyChampionsToList(championsSlug);
	return false;
}
function activateButton($buttonLi){
	$buttonLi.removeClass('disabled hide');
	$buttonLi.find('a').removeClass('disabled');
}
function deactivateButton($buttonLi){
	$buttonLi.addClass('disabled hide');
	$buttonLi.find('a').addClass('disabled');
}