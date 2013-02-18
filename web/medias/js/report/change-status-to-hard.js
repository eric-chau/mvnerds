$(document).ready(function() {
	
	$('a.status-to-hard-action').on('click', function() {
		var $this = $(this);
		
		var r = confirm("Changer le statut de cet objet en HARD ?");
		if (r==true) {
			data = {object_id: $this.data('id'), object_namespace: $this.data('namespace')};
		
			$.ajax({
				type: 'POST',
				url:  Routing.generate('admin_reports_change_status_to_hard', {'_locale': locale}),
				data: data,
				dataType: 'json'
			}).done(function(){
				$this.parent().parent().remove();
			}).fail(function(data){
				alert(data.responseText);
			});
		}		
		
		return false;
	});
});