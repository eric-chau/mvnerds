$(document).ready(function()
{
	// Ajustement de la font-size en fonction de la longueur du nom d'utilisateur
	var $linkToResize = $('div.stats strong a'),
		maxLinkWidth = 98,
		fontOriginalSize = 18;
		
	for (fontOriginalSize--; $linkToResize.width() > maxLinkWidth; fontOriginalSize--) {
		console.log(fontOriginalSize);
		console.log( $linkToResize.width());
		$linkToResize.css('font-size', fontOriginalSize);
	}
});