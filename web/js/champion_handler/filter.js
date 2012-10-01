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
		type: 'GET',
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
		options['filter']="*";
		$isotope.isotope(options);
	}
}

$(function(){	
	initTypeahead($isotope);
});