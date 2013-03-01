$(function() {
	// Activation des dropdowns dont celui de la topbar
	if ( $('.dropdown-toggle').dropdown != undefined) {
		$('.dropdown-toggle').dropdown();
	}
	// Activation des popovers
	if ( $('.bootstrap-popover').popover != undefined) {
		$('.bootstrap-popover').popover();
	}
	
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
		url:  Routing.generate('champions_names',{_locale: locale}), 
		dataType: 'html'
	}).done(function(data){
		$('#header-champion-search-input').attr('data-source', data);
	});
	$('#header-champion-search-input').on('keyup change', function() {
		var val = $(this).val();
		var champs = $(this).data('source');
		
		if (champs.indexOf(val) >= 0) {
			window.location = Routing.generate('champion_detail_by_name', {_locale: locale, name: val});
		} 
		else { }
	});
});

