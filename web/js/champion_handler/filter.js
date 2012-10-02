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


function initFilterList($isotope){
	$('#filters-list  li').off('click', ' a.filter-link:not(.selected)');
	$('#filters-list li').off('click', 'a.selected');
	
	$('#filters-list  li').on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		addFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		return false;
	});
	$('#filters-list li').on('click', 'a.selected',function(){
		$(this).removeClass('selected');
		removeFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		return false;
	});
}
function addFilterValue($isotope, value){
	options['filter'] = options['filter'] + value;
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
	options['filter'] = newOptions;
	$isotope.isotope(options);
}
function setTypeaheadValue($isotope, value){
	removeFilterValue($isotope, typeaheadValue);
	typeaheadValue = value;
	addFilterValue($isotope,typeaheadValue);
	$isotope.isotope(options);
}

$(function(){	
	setTypeaheadChampionsName();
	initTypeahead($isotope);
	initFilterList($isotope);
});