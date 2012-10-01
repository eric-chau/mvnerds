$(function(){
	
	var options = getIsotopeOptions();
	
	$.ajax({
		type: 'GET',
		url:  Routing.generate('champion_handler_front_get_champions_name'),
		dataType: 'html'
	}).done(function(data){
		$('#filter-value').attr('data-source', data);
	});

	$('#filter-value').keyup(function(){
		filter();
	});
	$('#filter-value').change(function(){
		filter();
	});
	
	function filter(){
		var filterValue = $('#filter-value').val();
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
});