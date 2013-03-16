$(document).ready(function()
{
	$('.bootstrap-popover').popover();

	$('.tooltip-anchor').tooltip();

	$('a.reset-field').each(function()
	{
		if ($(this).parent().find('input[type="text"]').val() == '') {
			$(this).addClass('disabled');
		}
	});

	$('a.reset-field, a.save-field').on('click', function(event)
	{
		event.preventDefault();
	});

	$('a.reset-field').on('click', function()
	{
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $parent = $(this).parent();
		$parent.find('input[type="text"]').val('');
		$parent.find('a.save-field').removeClass('disabled');
		$(this).addClass('disabled');
	});

	$('input[type="text"]').on('keyup', function()
	{
		$(this).parent().find('a.save-field').removeClass('disabled');
		if ($(this).val() != '') {
			$(this).parent().find('a.reset-field').removeClass('disabled');
		}
		else {
			$(this).parent().find('a.reset-field').addClass('disabled');
		}
	});

	$('a.save-field').on('click', function()
	{
		var $this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}

		var $parent = $this.parent(),
			$field = $parent.find('input[type="text"]'),
			$resetFieldIcon = $parent.find('a.reset-field i');
		$field.attr('disabled', 'disabled');
		$this.addClass('disabled');
		$resetFieldIcon.removeClass('icon-remove-sign');
		$resetFieldIcon.addClass('icon-spin icon-spinner loader');
		
		$.ajax({
			url: Routing.generate('summoner_profile_save_preference'),
			type: 'POST',
			data: {
				'preference_unique_name': $this.data('preference'),
				'preference_value': $field.val()
			},
			dataType: 'json',
			success: function(response)
			{
				if (response) {
					$resetFieldIcon.removeClass('icon-spin icon-spinner loader');
					$resetFieldIcon.addClass('icon-remove-sign');
					$field.removeAttr('disabled');
					
				}
			}
		});
	});


	var $saveAvatarBtn = $('a.btn-save-avatar'),
		currentAvatarName = $saveAvatarBtn.data('current-avatar-name');

	$('img.avatar-choice').on('click', function()
	{
		if ($(this).hasClass('selected') || $(this).hasClass('disabled')) {
			return false;
		}

		$('img.avatar-choice.selected').removeClass('selected');
		$(this).addClass('selected');

		$saveAvatarBtn.removeClass('disabled');
	});

	$saveAvatarBtn.on('click', function()
	{
		var $this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}

		var newAvatarName = $('img.avatar-choice.selected').data('avatar-name'),
			$loader = $('div#change-avatar-modal').find('i.loader');
		$loader.removeClass('hide');
		$this.addClass('disabled');
		$.ajax({
			url: Routing.generate('summoner_profile_save_avatar'),
			type: 'POST',
			data: {
				'new_avatar_name': newAvatarName
			},
			dataType: 'json',
			success: function(response)
			{
				$loader.addClass('hide');
				if (response) {
					$('img.user-current-avatar').attr('src', '/medias/images/avatar/'+ newAvatarName +'.jpg');
					$('div#topbar li.user-container img').attr('src', '/medias/images/avatar/'+ newAvatarName +'.jpg');
					$('form.leave-comment-form img.user-avatar').attr('src', '/medias/images/avatar/'+ newAvatarName +'.jpg');
					$('div#change-avatar-modal div.modal-body div.avatars-container').find('img').removeClass('disabled');
					$('div#change-avatar-modal div.modal-body div.avatars-container').find('img.selected').toggleClass('selected disabled');
					$('div#change-avatar-modal').modal('hide');
				}
			}
		});
	});
	
	// Activation du click sur le bouton "Changer mon mot de passe"
	$('a.btn-change-password').on('click', function()
	{
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $this = $(this),
			$loader = $this.find('i.loader');
		$loader.removeClass('hide');
		$this.find('span.msg.initial').addClass('hide');
		$this.find('span.msg.wip').removeClass('hide');
		$this.addClass('disabled');

		$.ajax({
			url: Routing.generate('summoner_profile_change_password'),
			type: 'GET',
			dataType: 'json',
			success: function(response)
			{
				$loader.addClass('hide');
				if (response) {
					$this.find('span.msg.wip').addClass('hide');
					$this.find('span.msg.success').removeClass('hide');
				}
				else {
					$this.removeClass('disabled');
					$this.find('span.msg.wip').addClass('hide');
					$this.find('span.msg.initial').removeClass('hide');					
				}
			},
			error: function() {
				$loader.addClass('hide');
				$this.removeClass('disabled');
				$this.find('span.msg.wip').addClass('hide');
				$this.find('span.msg.initial').removeClass('hide');	
			}

		})
	});

	/***************************************************************************************
		GESTION DE LA LIAISON D'UN COMPTE MVNERDS ET UN COMPTE LEAGUE OF LEGENDS
	***************************************************************************************/

	var $checkSummonerAccountButton = $('button.btn-check-lol-account-existence');

	// Event d'écoute sur le change du texte sur le champ de texte #lol-summoner-name
	$('input#lol-summoner-name').on('click keyup change', function(event)
	{

		if (event.which == 13) {
			checkSummonerAccountButton.trigger('click');
		}

		if ($.trim($(this).val()) != '') {
			$checkSummonerAccountButton.removeClass('disabled');
		}
		else {
			$checkSummonerAccountButton.addClass('disabled');
		}
	});

	// Activation de l'event du click sur le bouton "Vérifier" pour vérifier l'exitence d'un compte
	$checkSummonerAccountButton.on('click', function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $this = $(this),
			$form = $this.parent(),
			$summonerNameInput = $form.find('input#lol-summoner-name'),
			$loader = $form.find('i.loader'),
			$pError = $('div.modal p.error');
		
		$this.addClass('disabled');
		$loader.removeClass('hide');
		$pError.slideUp();
		
		$.ajax({
			url: Routing.generate('profile_check_lol_account_existence', {'_locale': locale}),
			data: {
				'region': $form.find('select#region-selector').val(),
				'summoner_name': $.trim($summonerNameInput.val())
			},
			type: 'POST',
			dataType: 'html',
			success: function(response) {
				$loader.addClass('hide');
				$('div.modal div.step').html(response);
			},
			error: function(response) {
				$loader.addClass('hide');
				$summonerNameInput.val('').focus();
				$pError.html(response.responseText);
				$pError.slideDown();
			}
		});
	});

	// Activation de l'event du click sur le bouton "Terminer la liaison"
	$('div.modal div.step').on('click', 'a.link-account-last-check', function(event) {
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}

		var $this = $(this),
			$parent = $this.parent(),
			$loader = $parent.find('i.loader'),
			$pError = $('div.modal p.error');

		$this.addClass('disabled');
		$loader.removeClass('hide');
		$pError.slideUp();

		$.ajax({
			url: Routing.generate('profile_end_of_link_account_process', {'_locale': locale}),
			type: 'GET',
			dataType: 'html',
			success: function(response) {
				document.location.reload(true);
			},
			error: function(response) {
				$loader.addClass('hide');
				$this.removeClass('disabled');
				$pError.html(response.responseText);
				$pError.slideDown();
			}
		});
	});

	// Activation de l'event de click sur le bouton "Renseigner mon compte de jeu"
	$('a.launch-link-account-process-btn').on('click', function() {
		$('div#link-lol-account-modal').modal('show');
		return false;
	});

	// Activation de l'event de click sur le bouton "Annuler la procédure"
	$('div.game-account-container').on('click', 'a.cancel-link-account-process-btn', function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}

		$(this).parent().find('a').each(function() {
			$(this).addClass('disabled');
		});
		$(this).parent().find('i.loader').removeClass('hide');
		$.ajax({
			url: Routing.generate('profile_cancel_link_account_process', {'_locale': locale}),
			type: 'get',
			dataType: 'json',
			success: function(response) {
				document.location.reload(true);
			}
		});
	});

	// Activation de la modal de changement d'avatar
	$('a.change-avatar-btn').on('click', function() {
		$('div#change-avatar-modal').modal('show');
		return false;
	});

	// Toggle des préférences utilisateurs
	$('a.user-preference-toggle').on('click', function(event) {
		event.preventDefault();
		$(this).find('span').toggleClass('disabled');
		$('div#' + $(this).data('block-id')).slideToggle();
	});
});