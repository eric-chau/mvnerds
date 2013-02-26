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
		if ($(this).hasClass('selected')) {
			return false;
		}

		$('img.avatar-choice.selected').toggleClass('selected');
		$(this).toggleClass('selected');

		if (currentAvatarName != $(this).data('avatar-name')) {
			$saveAvatarBtn.removeClass('disabled');
		}
		else {
			$saveAvatarBtn.addClass('disabled');
		}
	});

	$saveAvatarBtn.on('click', function()
	{
		var $this = $(this);
		if ($this.hasClass('disabled')) {
			return false;
		}

		var newAvatarName = $('img.avatar-choice.selected').data('avatar-name'),
			$loader = $this.parent().find('img.loader');
		$loader.toggle('hide');
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
				$loader.toggle('hide');
				if (response) {
					$('div#user-container img, div.current-avatar img.avatar').attr('src', '/images/avatar/'+ newAvatarName +'.jpg');
					$this.attr('data-current-avatar-name', newAvatarName);
					currentAvatarName = newAvatarName;
				}
			}
		});
	});

	$('a.btn-change-password').on('click', function()
	{
		var $this = $(this),
			$loader = $this.parent().find('img.loader');
		$loader.show();

		$.ajax({
			url: Routing.generate('summoner_profile_change_password'),
			type: 'GET',
			dataType: 'json',
			success: function(response)
			{
				if (response) {
					$loader.hide();
					$this.hide();
					$this.parent().find('span').show();
					$this.remove();
				}
			}
		})
	});
});