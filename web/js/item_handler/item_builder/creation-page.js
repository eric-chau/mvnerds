
var	
	$window,
	$recItemList,
	$recItems,
	recItemsTop,
	isRecItemsFixed = false,
	$championContainer
;

function setRecItem(slug, recItemId)
{	
	$recItem = $('#'+recItemId);
	$portrait = $('#'+slug).find('div.portrait');
	
	$recItem.html($portrait.clone());
	$recItem.removeClass('free');
	$recItem.addClass('full');
}
function addItemToList(slug)
{	
	var recItemId = $('li.rec-item.free').first().attr('id');
	
	$recItem = $('#'+recItemId);
	$portrait = $('#'+slug).find('div.portrait');
	
	$recItem.html($portrait.clone());
	$recItem.removeClass('free');
	$recItem.addClass('full');
}

 //Fixation de la liste d objets recommandés
function processScrollRecItems()
{
	var scrollTop = $window.scrollTop();

	if (scrollTop >= recItemsTop - 9.85 && !isRecItemsFixed) {
		isRecItemsFixed = true;
		$recItemList.addClass('fixed');
		$recItemList.parent('.item-container').addClass('fixed');
	} 
	else if (scrollTop <= recItemsTop - 9.85 && isRecItemsFixed) {
		isRecItemsFixed = false;
		$recItemList.removeClass('fixed');
		$recItemList.parent('.item-container').removeClass('fixed');
	}
}

function generateRecItemBuilder()
{
	$champions = $('div.champion-container li.champion.active');
	$items = $('#rec-item-list li.rec-item.full');
	gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
	buildName = $('input#build-name').val();
	
	if($champions.length >= 1 && $items.length == 6 && gameMode.length > 0 && buildName.length > 0)
	{
		championsSlugs = new Array();
		$champions.each(function(){
			championsSlugs.push($(this).attr('id'));
		});
		
		itemsSlugs = new Array();
		$items.each(function(){
			itemsSlugs.push($(this).find('div.portrait').data('slug'));
		});
		
		console.log(championsSlugs);
		console.log(itemsSlugs);
		console.log(gameMode);
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('item_builder_generate_rec_item_file', {_locale: locale}),
			data: {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName},
			dataType: 'json'
		}).done(function(data){
			console.log(data);
			window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
		}).fail(function(data){
			console.log(data);
		});
	}
	else
	{
		console.log('error : not enough champions or rec items, or no game mode or buildName set');
	}
	return false;
}

$(document).ready(function()
{
	$window = $(window);
	
	$recItemList = $('#rec-item-list');
	$recItems = $('li.rec-item');
	recItemsTop = $recItemList.length && $recItemList.offset().top;
	
	$championContainer = $('div.champion-container');
	
	processScrollRecItems();
	
	$window.on('scroll', processScrollRecItems)
	
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
	
	//Lors du clic ou du double clic sur un item
	var timeout, dblClic = false, that;
	$('ul#item-isotope-list').on('click', 'li.item:not(.item-maxi)', function(e){
		that = this;
		
		if(!$(that).hasClass('animating')){
			e.preventDefault();
			timeout = setTimeout(function() {
				if (!dblClic){
					timeout = null;
					console.log('maximize item');
				}
				else {
					dblClic = false;
				}
			}, 200);
		}
	}).on('dblclick', function(){
		if(!$(that).hasClass('item-maxi') && !$(that).hasClass('animating')){
			clearTimeout(timeout);
			timeout = null;
			dblClic = true;
			addItemToList($(that).attr('id'));
		}
	});
	
	//On rends chaque item draggable
	$('#item-isotope-list').on('mouseover', 'li.item', function(){
		$(this).draggable({
			disabled: false,
			helper: 'clone',
			revert: 'invalid',
			revertduration: 300,
			zIndex: 1100,
			opacity: 1,
			distance: 20,
			start: function(){
				$recItemList.find('li.rec-item.free').css('border-color', '#FB9701')
			},
			stop: function(){
				$recItemList.find('li.rec-item.free').css('border-color', 'rgba(255, 255, 255, .2)')
			}
		});
	});
	
	//On rends la rec item list capable d accepter les items
	$recItems.droppable({
		accept: '#item-isotope-list li.item',
		over: function(){
			$(this).css('border-color', 'red');
		},
		out: function(){
			$(this).css('border-color', '#FB9701');
		},
		drop: function( event, ui ) {
			setRecItem(ui.draggable.context.id, $(this).attr('id'));
			$(this).css('border-color', 'rgba(255, 255, 255, .2)')
		}
	});
	
	//Lors du clic sur un objet recommendé rempli
	$recItemList.on('click', 'li.rec-item.full', function(){
		$(this).removeClass('full');
		$(this).addClass('free');
		$(this).html('<div><i class="icon-question-sign icon-white"></i></div>');
	});
	
	 $('#rec-item-sortable').sortable({
		placeholder: 'rec-item hold-rec-item-place'
	});
	
	//Lors du clic sur un champion
	$championContainer.on('click', 'li.champion', function(){
		//On rend actif le champion
		$(this).toggleClass('active');
	});
	
	$('#only-generate-build').click(generateRecItemBuilder);
});