$(document).ready(function() {
	
	//Lors d un report simple (sans description)
	$('a.report-action').on('click', function() {
		var $this = $(this);
		
		var objectSlug = $this.data('slug')
		var objectId = $this.data('id')
		
		if (objectSlug != undefined && objectSlug != '') {
			data = {object_slug: objectSlug, object_type: $this.data('type')};
		} else if (objectId != undefined && objectId != '') {
			data = {object_id: objectId, object_type: $this.data('type')};
		}
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('report_report', {'_locale': locale}),
			data: data,
			dataType: 'json'
		}).done(function(){
			$this.remove();
			displayMessage('Votre signalement a bien été pris en compte.', SUCCESS_ALERT);
		}).fail(function(data){
			displayMessage(data.responseText, ERROR_ALERT);
		});
		
		return false;
	});
	
	//Toggle de la zone de report détaillée lors du clic sur le bouton show-report-action
	$('.show-report-action').on('click', function(event) {
		event.preventDefault();
		$('div#report-modal').modal('show');
	});
	
	//Lors de la soumission du report détaillé
	$('.report-form-action').on('click', function() {
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $this = $(this),
			$loader = $this.parent().find('i.loader');
		$loader.removeClass('hide');
		$this.addClass('disabled');
		var descriptionIndex = $('div#report-modal form.report-form').find('input[name=report-motive]:checked').val();
		
		data = {object_slug: $this.data('slug'), object_type: $this.data('type'), description_index: descriptionIndex};
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('report_report'),
			data: data,
			dataType: 'json'
		}).done(function(){
			$('div#report-modal').modal('hide');
			if (locale == 'fr') {
				$('a.show-report-action').parent().html('Déjà signalé').css('font-size', 13);
			}
			else {
				$('a.show-report-action').parent().html('Done');
			}
			displayMessage('Votre signalement a bien été pris en compte.', SUCCESS_ALERT);
		}).fail(function(data){
			$('div#report-modal').modal('hide');
			displayMessage(data.responseText, ERROR_ALERT);
		});
		
		return false;
	});
});