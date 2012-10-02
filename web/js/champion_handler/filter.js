var options, $isotope = $('#isotope-list'), $filterValue = $('#filter-value');

function initTypeahead($isotope){
	options =  getIsotopeOptions();
	setTypeaheadChampionsName();
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
		options['filter']="[data-name*='" + filterValue.toLowerCase()+"']";
		$isotope.isotope(options);
	}
	else
	{
		options['filter']="";
		$isotope.isotope(options);
	}
}


function initFilterList($isotope){
	$('#filters-list  li').off('click', ' a.filter-link:not(.selected)');
	$('#filters-list li').off('click', 'a.selected');
	
	$('#filters-list  li').on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		addFilterValue($isotope, $(this).attr('data-option-value'));
		return false;
	});
	$('#filters-list li').on('click', 'a.selected',function(){
		$(this).removeClass('selected');
		removeFilterValue($isotope, $(this).attr('data-option-value'));
		return false;
	});
}
function addFilterValue($isotope, value){
	options['filter'] = options['filter'] + '.' + value;
	$isotope.isotope(options);
}
function removeFilterValue($isotope, value){
	var oldOptions = options['filter'];
	var splitedOptions = oldOptions.split('.'+value);
	var newOption = splitedOptions[0].concat(splitedOptions[1]);
	
	options['filter'] = newOption;
	$isotope.isotope(options);
}

$(function(){	
	initTypeahead($isotope);
	initFilterList($isotope);
});