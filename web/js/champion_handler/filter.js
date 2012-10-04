var options, typeaheadValue, $isotope = $('#isotope-list'), $filterValue = $('#filter-value');

function initTypeahead($isotope){
	options =  getIsotopeOptions();
	$filterValue.off('keyup change');
	$filterValue.keyup(function(){
		filter($isotope);
	});
	$filterValue.change(function(){
		filter($isotope);
	});
}
function initFilterList($isotope){
	$('#filters-list  li').off('click', ' a.filter-link:not(.selected)');
	$('#filters-list li').off('click', 'a.selected');
	
	$('#filters-list  li').on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		activateCompareFilteredButton();
		addFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		return false;
	});
	$('#filters-list li').on('click', 'a.selected',function(){
		$(this).removeClass('selected');
		removeFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		if($('#filters-list li a.selected').size() <= 0)
		{
			deactivateCompareFilteredButton();
		}
		return false;
	});
	$('#li-clean-filter').on('click', '#btn-clean-fitler', cleanFilter);
	$('#li-compare-filtered').on('click', '#btn-compare-filtered', addFilteredChampions);
}

function setTypeaheadChampionsName(){
	$.ajax({
		type: 'POST',
		url:  Routing.generate('champion_handler_front_get_champions_name'),
		dataType: 'html'
	}).done(function(data){
		$filterValue.attr('data-source', data);
	});
}

function filter($isotope){
	var filterValue = $filterValue.val();
	if(filterValue != '')
	{
		setTypeaheadValue($isotope, "[data-name*='" + filterValue.toLowerCase()+"']");
	}
	else
	{
		removeFilterValue($isotope, typeaheadValue);
		$isotope.isotope(options);
	}
}
function addFilterValue($isotope, value){
	options['filter'] = options['filter'] + value;
	activateCleanFilterButton();
	$isotope.isotope(options);
}
function removeFilterValue($isotope, value){
	var oldOptions = options['filter'];
	var newOptions;
	if(value != undefined && value != '')
	{
		var splitedOptions = oldOptions.split(value);
		newOptions = splitedOptions[0].concat(splitedOptions[1]);
	}
	else
	{
		newOptions = oldOptions;
	}
	typeaheadValue = undefined;
	if(newOptions == undefined || newOptions == '')
	{
		deactivateCleanFilterButton();
	}
	options['filter'] = newOptions;
	$isotope.isotope(options);
}
function setTypeaheadValue($isotope, value){
	removeFilterValue($isotope, typeaheadValue);
	typeaheadValue = value;
	addFilterValue($isotope,typeaheadValue);
	$isotope.isotope(options);
}

//Permet de vider le filtre
function cleanFilter(){
	$filterValue.val('');
	options['filter'] = '';
	$('ul#filters-list ul.tags-group a.filter-link.selected').each(function(){
		$(this).removeClass('selected');
	});
	deactivateCleanFilterButton();
	deactivateCompareFilteredButton();
	$isotope.isotope(options);
	return false;
}
function activateCleanFilterButton(){
	$('#li-clean-filter').removeClass('disabled hide');
	$('#li-clean-filter').find('a').removeClass('disabled');
}
function deactivateCleanFilterButton(){
	$('#li-clean-filter').addClass('disabled hide');
	$('#li-clean-filter').find('a').addClass('disabled');
}

//Permet d ajouter tous les champions filtrés à la liste
function addFilteredChampions(){
	$('#isotope-list li.isotope-item.champion:not(.isotope-hidden)').each(function(){
		addChampionToList($(this).attr('id'));
	});
	return false;
}
function activateCompareFilteredButton(){
	$('#li-compare-filtered').removeClass('disabled hide');
	$('#li-compare-filtered').find('a').removeClass('disabled');
}
function deactivateCompareFilteredButton(){
	$('#li-compare-filtered').addClass('disabled hide');
	$('#li-compare-filtered').find('a').addClass('disabled');
}

$(function(){	
	setTypeaheadChampionsName();
	initTypeahead($isotope);
	initFilterList($isotope);
});