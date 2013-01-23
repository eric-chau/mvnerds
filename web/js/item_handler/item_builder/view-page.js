function storeItemBuildForCopy() {
	var championSlugs = new Array();
	$('div#champion-list ul li.champion').each(function(){
		championSlugs.push($(this).attr('id'));
	});

	var gameMode = $('div.game-mode-container div.game-mode').first().attr('data-game-mode');
	
	var itemSlugs = new Array();
	$('div.item-container.view-item-container div.elements-grid').each(function () {
		var blockName = $(this).find('h3').html();
		var blockDescription = $(this).children('p').html();
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
				itemSlugs.push({name:blockName, items:blockArray, description: blockDescription});
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

$(function() {
	initPopoverItem($('div.item-container'));
	
	$('li.champion').tooltip();
	
	$('a.download-action.start-dl').click();
	
	$('a.copy-action').click(function(e){
		e.preventDefault();
		storeItemBuildForCopy();
		window.location = Routing.generate('pmri_create', {_locale: locale});
	});	
	//Clic sur un item
	initModalItem($('.item-container'));
});