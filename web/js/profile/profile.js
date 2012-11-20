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

		$(this).parent().find('input[type="text"]').val('');
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
				console.log(response == true);
				if (response) {
					$loader.toggleClass('hide');
					$this.addClass('disabled');
				}
			}
		});
	});
});