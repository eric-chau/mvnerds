$(document).ready(function() {
	
	//Lors d un report simple (sans description)
	$('a.report-action').on('click', function() {
		var $this = $(this);
		
		data = {object_slug: $this.data('slug'), object_type: $this.data('type')};
		
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
	$('.show-report-action').on('click', function () {
		$(this).parent().find('div.report-motive').toggle(300, 'linear');
	});
	
	//Lors de la soumission du report détaillé
	$('.report-form-action').on('click', function() {
		var $this = $(this);
		
		var descriptionIndex = $this.parent().find('input[name=report-motive]:checked').val();
		
		data = {object_slug: $this.data('slug'), object_type: $this.data('type'), description_index: descriptionIndex};
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('report_report', {'_locale': locale}),
			data: data,
			dataType: 'json'
		}).done(function(){
			$this.parent().parent().parent().remove();
			displayMessage('Votre signalement a bien été pris en compte.', SUCCESS_ALERT);
		}).fail(function(data){
			displayMessage(data.responseText, ERROR_ALERT);
		});
		
		return false;
	});
});