function storeItemBuildForCopy() {
	var championSlugs = new Array();
	$('div#champion-list ul li.champion').each(function(){
		championSlugs.push($(this).attr('id'));
	});

	var gameMode = $('div.game-mode-container div.game-mode').first().attr('data-game-mode');
	
	var itemSlugs = new Array();
	$('div.item-container.view-item-container div.elements-grid').each(function () {
		var blockName = $(this).find('h3').html();
		if(! (blockName in itemSlugs)) {
			var blockArray = new Array();
			$(this).find('ul li.item div.portrait').each(function() {
				blockArray.push($(this).data('slug'));
			});
			if (blockArray.length > 0) {
				itemSlugs.push({name:blockName, items:blockArray});
			}
		}
	});

	var buildName = '';
	
	saveItemInLS('storedItemBuild', 'true');
	saveItemInLS('storedChampionSlugs', championSlugs);
	saveItemInLS('storedGameMode', gameMode);
	saveItemInLS('storedItemSlugs', JSON.stringify(itemSlugs));
	saveItemInLS('storedBuildName', buildName);
}

$(function() {
	initPopoverItem($('div.item-container'));
	
	$('li.champion').tooltip();
	
	$('a.download-action.start-dl').click();
	
	$('a.copy-action').click(function(e){
		e.preventDefault();
		storeItemBuildForCopy();
		window.location = Routing.generate('item_builder_create', {_locale: locale});
	});	
});