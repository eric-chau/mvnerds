$(document).ready(function()
{
	// Écoute sur l'événement d'un clique sur un des modes de jeu
	$('div.game-mode-container div.game-mode').on('click', function()
	{
		var $this = $(this);
		$('div.game-mode-container div.game-mode').each(function()
		{
			$(this).removeClass('active');
		});

		$this.addClass('active');
	});
});