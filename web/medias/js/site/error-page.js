$(document).ready(function()
{
	$('a.hug-amumu').on('click', function(event) {
		event.preventDefault();
		$('div.amumu-message').removeClass('hide');
	});
});
