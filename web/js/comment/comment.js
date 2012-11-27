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

	$commentMsg.on('keyup click', function()
	{
		$commentCharCount.html($commentMsg.val().length);
		
		if ($.trim($commentMsg.val()) != '') {
			$commentBtn.removeClass('disabled');
		}
		else {
			$commentBtn.addClass('disabled');
		}
	});
});
