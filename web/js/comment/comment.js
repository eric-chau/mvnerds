$(document).ready(function()
{
	var $commentBtn = $('a.btn-send-comment'),
		$commentMsg = $('textarea.comment-msg'),
		$loader = $commentMsg.parent().find('img.loader'),
		$commentCharCount = $('span.char-count'),
		$commentsList = $('div.comments-container div.comments-list'),
		$commentCount = $('span.comment-count');

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
		$.ajax({
			url: Routing.generate(leaveCommentRoute, {'_locale': locale}),
			data: {
				'object_slug': objectSlug,
				'user_slug': userSlug,
				'comment_msg': $('textarea.comment-msg').val(),
				'last_comment_id': $('div.comments-list div.span8.comment-block:first').data('comment-id')
			},
			type: 'POST',
			dataType: 'html',
			success: function(response)
			{
				var $content = $('<div class="hide"></div>').html(response),
					$commentMsg = $('textarea.comment-msg');
				$content.prependTo($commentsList);
				$commentsList.find('div.no-comment').remove();
				$content.slideDown();
				$commentMsg.parent().find('img.loader').addClass('hide');
				$commentMsg.removeAttr('disabled');
				$commentMsg.val('');
				$commentCount.html(parseInt($commentCount.html()) + $content.find('div.span8.comment-block').length);
			}
		});
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

	$('div.comments-list').on('click', 'a.report', function(event)
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
		var $parent = $(this).parent().parent(); // = div.span10 qui est le container du message + de div.posted-by
		$parent.find('p.hide').slideDown('fast');
		$parent.find('span.report-container a.hide').show();
		$(this).addClass('hide');

	});

	var $win = $(window),
		$doc = $(document),
		$moreCommentsLoader = $('div.row-fluid.more-comments-loader')
		isLoading = false,
		pageCount = 1,
		reachMaxLoad = false;

	$win.on('scroll', function()
	{
		if (($win.height() + $win.scrollTop()) > $doc.height() * .8) {
			if (!reachMaxLoad && !isLoading) {
				isLoading = true;
				$moreCommentsLoader.show();

				$.ajax({
					url: Routing.generate(loadMoreCommentRoute, {'_locale': locale}),
					data: {
						'object_slug': objectSlug,
						'page': pageCount
					},
					type: 'POST',
					dataType: 'html',
					success: function(response)
					{
						if (response == '') {
							reachMaxLoad = true;
						}

						var $content = $('<div class="hide"></div>').html(response);
						$content.appendTo($commentsList);
						$content.slideDown();
						pageCount++;
						isLoading = false;
						$moreCommentsLoader.hide();
					}
				});
			}
		}
	});
});
