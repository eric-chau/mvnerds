$(document).ready(function()
{
	var $commentBtn = $('a.btn-send-comment'),
		$commentMsg = $('textarea.comment-msg'),
		$loader = $commentMsg.parent().find('img.loader'),
		$commentCharCount = $('span.char-count');

	$commentBtn.on('click', function(event)
	{
		event.stopPropagation();
		if ($commentBtn.hasClass('disabled')) {
			return false;
		}

		$loader.removeClass('hide');	
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