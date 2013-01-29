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
});

