jQuery(function($) {
	$(document).ready(function() {
		$('a.btn-submit-form').click(function(event) {
			event.preventDefault();
			$('input[type="submit"].submit-form').trigger('click');
		});

		$('a.bootstrap-modal').click(function(){
			$('.bootstrap-modal-name').html($(this).data('name'));
			$('#modal-btn-confirm-delete').attr('data-slug', $(this).data('slug'));
			$('#modal-btn-confirm-delete').attr('data-href', $(this).data('href'));
			$('#modal-btn-confirm-delete').attr('data-id', $(this).data('id'));
			$('#bootstrap-delete-modal').modal('show');
		});

		$('a#modal-btn-confirm-delete').click(function(event) {			
			event.preventDefault();
			var $loader = $(this).parent().find('img'), $this = $(this);
			$loader.removeClass('hide');
			var slug = $(this).data('slug');
			var id = $(this).attr('data-id');
			var href = $(this).data('href');
			
			href = href.replace('__SLUG__', slug);
			href = href.replace('__ID__', id);
			
			$.ajax({
				url: href,
				type: 'GET',
				datetype: 'json',
				success: function(response) {
					if (response) {
						// On remonte à la modal pour la cachée
						$('#bootstrap-delete-modal').modal('hide');
						// Objet jQuery portant sur la ligne qui concerne l'utilisateur
						var $row;console.log(slug);
						if (slug == undefined || slug == '') {
							$row = $('#' + id);
						} else {
							$row = $('#'+slug);
						}
						$loader.addClass('hide');
						$row.slideUp('slow');
						$row.remove();
					}
				}
			});
		})
	});


});