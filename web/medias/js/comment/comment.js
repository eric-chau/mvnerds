$(document).ready(function()
{
	var $commentBtn = $('a.btn-send-comment'),
		$commentMsg = $('textarea.comment-msg'),
		$commentsList = $('div.comments-container div.comments-list'),
		$loader = $('form.leave-comment-form div.comment-actions i.icon-spinner.icon-spin');
		$commentCount = $('span.comment-count');

	// Activation de l'event de click sur le bouton "Commenter"
	$commentBtn.on('click', function(event)
	{
		event.preventDefault();
		if ($commentBtn.hasClass('disabled')) {
			return false;
		}

		$loader.removeClass('hide');
		$commentBtn.addClass('disabled');
		$commentMsg.attr('disabled', 'disabled');
		
		$.ajax({
			url: Routing.generate('leave_comment', {'_locale': locale}),
			data: {
				'object_slug': objectSlug,
				'object_type': objectType,
				'user_slug': userSlug,
				'comment_msg': $('textarea.comment-msg').val(),
				'last_comment_id': $('div.comments-list div.comment-block:first').data('comment-id')
			},
			type: 'POST',
			dataType: 'html',
			success: function(response)
			{
				var $content = $('<div class="hide"></div>').html(response);
				$content.prependTo($commentsList);
				$commentsList.find('div.no-comment').remove();
				$content.slideDown();
				$loader.addClass('hide');
				$commentMsg.removeAttr('disabled');
				$commentMsg.val('');
				$commentCount.html(parseInt($commentCount.html()) + $content.find('div.comment-block').length);
			}
		});
	});

	$commentMsg.on('keyup click change', function()
	{
		if ($.trim($commentMsg.val()) != '') {
			$commentBtn.removeClass('disabled');
		}
		else {
			$commentBtn.addClass('disabled');
		}
	});

	/*****************************************************************************/
	/************* EVENT EN RAPPORT AVEC L'EDITION D'UN COMMENTAIRE **************/
	/*****************************************************************************/

	var $currentCommentRow;
	// Activation de l'event d'édition d'un commentaire
	$('div.comments-list').on('click', 'a.edit-comment-action', function(event) {
		event.preventDefault();
		var commentID = $(this).parent().parent().data('comment-id');
		$currentCommentRow = $('div.comment-block#comment-' + commentID);

		$currentCommentRow.find('div.comment-actions').hide();
		$currentCommentRow.find('div.comment-main-content').hide();
		$currentCommentRow.find('div.comment-edition-mode').show();
	});

	// Event qui permet de vérifier si l'édition d'un commentaire est valide et peut être soumis à un enregistrement
	$('div.comments-list').on('keyup click change', 'div.comment-edition-mode textarea', function() {
		var $saveButton = $(this).parent().find('a.save-comment-edition');
		if ($.trim($(this).val()) != $.trim($(this).parent().parent().find('div.comment-main-content p span.msg').html()) && $.trim($(this).val()) != '') {
			$saveButton.removeClass('disabled');
		}
		else {
			$saveButton.addClass('disabled');
		}
	});

	// Activation de l'event de click sur le bouton d'annulation d'édition
	$('div.comments-list').on('click', 'a.cancel-comment-edition', function(event) {
		event.preventDefault();
		$(this).parent().find('a.save-comment-edition').addClass('disabled');
		$(this).parent().find('textarea').val($(this).parent().parent().find('div.comment-main-content p span.msg').html());
		$currentCommentRow.find('div.comment-edition-mode').hide();
		$currentCommentRow.find('div.comment-main-content').show();
		$currentCommentRow.find('div.comment-actions').show();		
	});

	// Activation de l'event de click sur le bouton d'enregistrement de l'édition d'un commentaire
	$('div.comments-list').on('click', 'a.save-comment-edition', function(event) {
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $parent = $(this).parent();
		$parent.find('i.loader').removeClass('hide');
		$parent.find('textarea').attr('disabled', 'disabled');
		$(this).addClass('disabled');

		$.ajax({
			url: Routing.generate('comment_edit', {'_locale': locale}),
			data: {
				'comment_id': $currentCommentRow.data('comment-id'),
				'user_slug': userSlug,
				'comment_msg': $.trim($parent.find('textarea').val())
			},
			type: 'POST',
			dataType: 'json',
			success: function(response) {
				$parent.find('i.loader').addClass('hide');
				$parent.find('textarea').removeAttr('disabled');
				$parent.parent().find('p span.msg').html(response.content);
				$parent.hide();
				$parent.parent().find('div.comment-main-content').show();
				$parent.parent().find('div.comment-actions').show();
			}
		});
	});

	//
	$('div.load-more-comments a.load-comments').on('click', function(event) {
		event.preventDefault();
		var $this = $(this);
		$this.parent().find('i.loader').removeClass('hide');

		$.ajax({
			url: Routing.generate('comment_load_more', {'_locale': locale}),
			data: {
				'object_slug': objectSlug,
				'object_type': objectType,
				'first_comment_id': $('div.comments-list div.comment-block:last').data('comment-id')
			},
			type: 'POST',
			dataType: 'html',
			success: function(response)
			{
				var $content = $('<div class="hide"></div>').html(response);
				$content.appendTo($commentsList);
				$content.slideDown();
				$this.parent().slideUp().remove();
			}
		});
	})

	/*****************************************************************************/
	/********************************* FIN EVENT *********************************/
	/*****************************************************************************/

	/*

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
*/
});
