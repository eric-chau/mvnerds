$(document).ready(function()
{
	// Activation de l'event de clic sur le header d'un bloc de généalogie pour l'agrandir ou le rétrécir
	$('div.block.geneology div.header').on('click', function() {
		$(this).find('i').toggleClass('icon-resize-small icon-resize-full');
		$(this).parent().find('div.body').slideToggle();
	});
});
