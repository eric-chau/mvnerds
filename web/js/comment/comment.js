$(document).ready(function()
{
	var $commentBtn = $('a.btn-send-comment'),
		$commentMsg = $('textarea.comment-msg'),
		$loader = $commentMsg.parent().find('img.loader'),
		$commentCharCount = $('span.char-count');

	$commentBtn.on('click', function(event)
	{
		event.preventDefault();
		if ($commentBtn.hasClass('disabled')) {
			return false;
		}

		$loader.removeClass('hide');
		$commentBtn.addClass('disabled');
		$commentMsg.attr('disabled', 'disabled');
		
		// Méthode que l'on doit implémenter manuellement notamment dû à l'ID de l'utilisateur et de l'objet
		addCommentCustomMethod();
	});

	$commentMsg.on('keyup click change', function()
	{
		$commentCharCount.html($commentMsg.val().length);
		
		if ($.trim($commentMsg.val()) != '') {
			$commentBtn.removeClass('disabled');
		}
		else {
			$commentBtn.addClass('disabled');
		}
	});

	$commentMsg.on('keyup change', function()
	{
		$(this).val($(this).val().slice(0, 500));
	});

	$('a.report').on('click', function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('wip')) {
			return false;
		}

		$(this).addClass('wip');
		var $reportContainer = $(this).parent();
		$.ajax({
			url: Routing.generate('comment_report'),
			data: {
				'comment_id': $(this).data('comment-id')
			},
			type: 'POST',
			dataType: 'html',
			success: function(response)
			{
				$reportContainer.html(response);
			}
		});
	});

	$('p.reported-comment a').on('click', function(event)
	{
		event.preventDefault();
		$(this).parent().parent().find('p.hide').slideDown();
		$(this).addClass('hide');
	});
});
