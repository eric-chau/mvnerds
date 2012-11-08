$(function(){
	$('a.download-action').click(function() {
		$('#modal-build-name').html($(this).data('name'));
		$('#modal-dl-build').modal('show');
		$('#modal-btn-download').attr('data-target', $(this).data('slug'));
		$('#modal-btn-download').attr('data-dl-count', $(this).parent().parent().children('td:eq(4)').children('span').data('id'));
	});
	$('#modal-btn-download').on('click', function(e) {
		e.preventDefault();
		var dlCount = $('span[data-id='+$(this).data('dl-count')+']');
		var target = $(this).attr('data-target');
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('item_builder_generate_rec_item_file_from_slug', {_locale: locale}),
			data: {itemBuildSlug : target, path: $('#modal-lol-path').val()},
			dataType: 'json'
		}).done(function(data){console.log(data);
			dlCount.children('span').html(dlCount.children('span').html() * 1 + 1);
			window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
		}).fail(function(data){
			console.log(data.responseText);
			displayMessage('Impossible de créer le build.', 'error');
		});
	});
});