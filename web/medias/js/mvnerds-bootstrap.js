$(function() {
	// Activation des dropdowns dont celui de la topbar
	$('.dropdown-toggle').dropdown();
	// Activation des popovers
	$('.bootstrap-popover').popover();
	
	//Blocage du clic des liens désactivés
	$('a.disabled').on('click', function(){
		if ($(this).hasClass('disabled')) {
			return false;
		}
		return true;
	});
	
	//Ajout de l'auto-completion du champ de recherche des champions du header
	$.ajax({
		type: 'POST',
		url:  Routing.generate('champion_benchmark_get_champions_name',{_locale: locale}), 
		dataType: 'html'
	}).done(function(data){
		$('#header-champion-search-input').attr('data-source', data);
	});
	$('#header-champion-search-input').on('keyup change', function() {
		var val = $(this).val();
		var champs = $(this).data('source');
		
		if (champs.indexOf(val) >= 0) {
			console.log('in');
			window.location = Routing.generate('champion_detail_by_name', {_locale: locale, name: val});
		} else {
			console.log('not in');
		}
	});
});

