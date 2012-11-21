$(document).ready(function()
{
	$('.bootstrap-popover').popover();

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
			$loader = $parent.find('img.loader');
		$field.addClass('disabled');
		$loader.toggleClass('hide');
		$this.addClass('disabled');
		
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
					$loader.toggleClass('hide');
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
				}
			}
		});
	});
});