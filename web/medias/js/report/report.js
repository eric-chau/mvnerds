$(document).ready(function() {
	
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
});