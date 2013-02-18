$(document).ready(function()
{
	var $commentBtn = $('a.btn-send-comment'),
		$commentMsg = $('textarea.comment-msg'),
		$commentsList = $('div.comments-container div.comments-list'),
		$loader = $('form.leave-comment-form div.comment-actions i.icon-spinner.icon-spin');
		$commentCount = $('span.comment-count');

	// Activation de l'event de click sur le bouton "Commenter"
	$('div.comments-container').on('click', 'a.btn-send-comment', function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
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
				window.scrollTo($('div.comments-container').position().left, $('div.comments-container').position().top - 80);
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
				$parent.parent().find('p span.msg').html($.nl2br(response.content));
				$parent.hide();
				$parent.parent().find('div.comment-actions div.last-edition-date').html(response.last_edition_date);
				$parent.parent().find('div.comment-main-content').show();
				$parent.parent().find('div.comment-actions').show();
			}
		});
	});

	/*****************************************************************************/
	/********************************* FIN EVENT *********************************/
	/*****************************************************************************/

	// Activation de l'event de click sur le bouton "Voir tous les commentaires"
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
	});

	/*****************************************************************************/
	/************* EVENT EN RAPPORT AVEC L'EDITION D'UN COMMENTAIRE **************/
	/*****************************************************************************/

	// Déclaration des variables
	var commentStr = 'Comment',
		replyStr = 'Answer to',
		replyStrSecondPart = ', comment ',
		commentPlaceholderStr = 'Write a comment',
		replyPlaceholderStr = 'Reply to a comment',
		currentCommentReplyID = 0;
	if (locale == 'fr') {
		commentStr = 'Commenter';
		replyStr = 'Répondre à';
		replyStrSecondPart = ', commentaire ';
		commentPlaceholderStr = 'Écrire un commentaire';
		replyPlaceholderStr = 'Répondre à un commentaire';
	}

	// Activation de l'event de click sur le bouton répondre de chaque commentaire
	$('div.comments-list').on('click', 'a.reply-action', function(event) {
		event.preventDefault();

		if ($(this).hasClass('active')) {
			return false;
		}

		$('div.comments-list').find('a.reply-action').removeClass('active');
		var $this = $(this),
			$form = $('form.leave-comment-form'),
			commentOwner = $this.data('username'),
			commentNumber = $this.parent().parent().parent().find('div.comment-number').html();
		currentCommentReplyID = $this.parent().parent().parent().data('comment-id');
		
		window.scrollTo($('form.leave-comment-form').position().left, $('form.leave-comment-form').position().top);
		$form.addClass('reply-mode');
		$form.find('a.btn-send-comment, a.btn-reply-comment').removeClass('green btn-send-comment').addClass('red btn-reply-comment').html(replyStr + ' <strong>' + commentOwner + replyStrSecondPart + commentNumber + '</strong>');
		$form.find('a.btn-cancel-reply-mode').removeClass('hide');
		$this.addClass('active');
		$form.find('textarea').attr('placeholder', replyPlaceholderStr);
	});

	// Activation du click sur le bouton "Annuler" lorsque l'on est en mode réponse
	$('form.leave-comment-form').on('click', 'a.btn-cancel-reply-mode', function(event) {
		event.preventDefault();

		if ($(this).hasClass('hide')) {
			return false;
		}

		$('div.comments-list').find('a.reply-action').removeClass('active');
		var $this = $(this),
			$form = $('form.leave-comment-form');
		$form.removeClass('reply-mode');
		$this.addClass('hide');
		$form.find('a.btn-send-comment, a.btn-reply-comment').removeClass('red btn-reply-comment').addClass('green btn-send-comment').html(commentStr);
		$form.find('textarea').attr('placeholder', commentPlaceholderStr);
	});

	// Activation de l'event de click sur le bouton de réponse
	$('div.comments-container').on('click', 'a.btn-reply-comment', function(event) {
		event.preventDefault();
		
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var currentCommentID = currentCommentReplyID,
			$this = $(this);

		$loader.removeClass('hide');
		$commentBtn.addClass('disabled');
		$commentMsg.attr('disabled', 'disabled');

		$.ajax({
			url: Routing.generate('comment_reply', {'_locale': locale}),
			data: {
				'user_slug': userSlug,
				'comment_id': currentCommentID,
				'reply_msg': $('textarea.comment-msg').val()
			},
			type: 'POST',
			dataType: 'html',
			success: function(response) {
				var $content = $('<div class="hide"></div>').html(response),
					$block = $('div#comment-' + currentCommentID);
				$content.appendTo($block.find('div.responses-list'));
				$content.slideDown();
				$loader.addClass('hide');
				$commentMsg.removeAttr('disabled');
				$commentMsg.val('');
				window.scrollTo($block.position().left, $block.position().top - 40);
			}
		});
	});

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
