jQuery(function($) {
	$(document).ready(function() {
		$('a.btn-submit-form').click(function(event) {
			event.preventDefault();
			$('input[type="submit"].submit-form').trigger('click');
		});
	});
});