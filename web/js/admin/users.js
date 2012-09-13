jQuery(function($) {
	$(document).ready(function() {
		$('a.btn-submit-form').click(function(event) {
			event.preventDefault();
			$('input[type="submit"].submit-form').trigger('click');
		});

		$('a.btn-confirm-delete').click(function(event) {
			event.preventDefault();
			var $loader = $(this).parent().find('img'), $this = $(this);
			$loader.removeClass('hide');
			$.ajax({
				url: $(this).attr('href'),
				type: 'GET',
				datetype: 'json',
				success: function(response) {
					if (response) {
						// On remonte à la modal pour la cachée
						$this.parent().parent().modal('hide');
						// Objet jQuery portant sur la ligne qui concerne l'utilisateur
						var $row = $this.parent().parent().parent();
						$loader.addClass('hide');
						$row.slideUp('slow');
						$row.remove();
					}
				}
			});
		})
	});


});