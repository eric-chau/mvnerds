function storeItemBuildForCopy() {
	var championSlugs = new Array();
	$('div.champions-list div.champion').each(function(){
		championSlugs.push($(this).attr('id'));
	});

	var gameMode = $('div.map-container div.game-mode').first().attr('data-game-mode');
	
	var itemSlugs = new Array();
	$('div.item-block-container div.element').each(function () {
		var blockName = $(this).find('h3 span.block-name').html();
		if(! (blockName in itemSlugs)) {
			var blockArray = new Array();
			$(this).find('ul li.item div.portrait').each(function() {
				var $itemCount = $(this).find('span.item-count');
				var itemCount = 1;
				if ($itemCount.length > 0) {
					itemCount = $itemCount.html() *1;
					itemCount = itemCount >= 1 ? itemCount : 1;
				}
				blockArray.push({slug: $(this).data('slug'), count: itemCount});
			});
			if (blockArray.length > 0) {
				itemSlugs.push({name:blockName, items:blockArray, description: ''});
			}
		}
	});

	var buildName = '';
	var buildDescription = '';
	var isBuildPrivate = false;
	
	saveItemInLS('storedItemBuild', 'true');
	saveItemInLS('storedChampionSlugs', championSlugs);
	saveItemInLS('storedGameMode', gameMode);
	saveItemInLS('storedItemSlugs', JSON.stringify(itemSlugs));
	saveItemInLS('storedBuildName', buildName);
	saveItemInLS('storedBuildDescription', buildDescription);
	saveItemInLS('storedBuildIsPrivate', isBuildPrivate);
}

$(document).ready(function()
{
	// Activation des tooltips
	$('.tooltip-anchor').tooltip();

	// Initialisation des popover des objets
	initPopoverItem($('div.span6.element ul'));

	// Activer le toggle d'affichage des notes de l'auteur pour chaque bloc d'objets	
	$('span.description-toggle').on('click', function() {
		$(this).toggleClass('disabled');
		$(this).parent().parent().find('p.description-block').slideToggle();
	});

	// Initialisation de l'event de click sur le bouton de copie d'un build
	$('a.copy-action').click(function(e){
		e.preventDefault();
		storeItemBuildForCopy();
		window.location = Routing.generate('pmri_create', {_locale: locale});
	});	

	// Permet de forcer le téléchargement du build dans le cas où la classe start-dl est affecté au bouton
	$('a.download-action.start-dl').click();
	
	// Affiche l'item modal lors du clic sur un objet
	initModalItem($('.item-block-container'));

	// Ajustement de la font-size en fonction de la longueur du nom d'utilisateur
	var $linkToResize = $('div.stats strong a'),
		maxLinkWidth = 97,
		fontOriginalSize = 18;
		
	for (fontOriginalSize--; $linkToResize.width() > maxLinkWidth; fontOriginalSize--) {
		$linkToResize.css('font-size', fontOriginalSize);
	}
});
