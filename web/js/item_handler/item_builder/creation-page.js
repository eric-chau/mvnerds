
var	
	$window,
	$recItemList,
	$recItems,
	recItemsTop,
	isRecItemsFixed = false,
	$championContainer,
	$itemIsotopeList,
	itemIsotopeOptions
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
	$items = $('#item-topbar li.rec-item.full');
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

/************************************************************************** FILTER ***************************************************************/
var itemTypeaheadValue, $itemFilterInput;

function initTypeahead($itemIsotopeList) {
	$itemFilterInput.off('keyup change');
	$itemFilterInput.keyup(function(){
		itemFilter($itemIsotopeList);
	});
	$itemFilterInput.change(function(){
		itemFilter($itemIsotopeList);
	});
}
function initFilterList($itemIsotopeList) {
	$('#item-filters-list  li').off('click', ' a.filter-link:not(.selected)');
	$('#item-filters-list li').off('click', 'a.selected');
	
	$('#item-filters-list  li').on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		$('a#drop-filter-list').addClass('active');
		addItemFilterValue($itemIsotopeList, '.'+$(this).attr('data-option-value'));
		return false;
	});
	$('#item-filters-list li').on('click', 'a.selected',function() {
		$(this).removeClass('selected');
		removeItemFilterValue($itemIsotopeList, '.'+$(this).attr('data-option-value'));
		if($('#item-filters-list li a.selected').size() <= 0)
		{
			$('a#item-drop-filter-list').removeClass('active');
		}
		return false;
	});
	$('#item-li-clean-filter').on('click', '#btn-clean-filter', cleanItemFilter);
}

function setTypeaheadItemsName() {
	$.ajax({
		type: 'POST',
		url:  Routing.generate('item_builder_get_items_name',{_locale: locale}), 
		dataType: 'html'
	}).done(function(data){
		$itemFilterInput.attr('data-source', data);
	});
}

function itemFilter($itemIsotopeList) {
	var filterValue = $itemFilterInput.val();
	if(filterValue != '')
	{
		setItemTypeaheadValue($itemIsotopeList, "[data-name*='" + filterValue.toLowerCase()+"']");
	}
	else
	{
		removeItemFilterValue($itemIsotopeList, itemTypeaheadValue);
		$itemIsotopeList.isotope(itemIsotopeOptions);
	}
}
function addItemFilterValue($itemIsotopeList, value) {
	itemIsotopeOptions['filter'] = itemIsotopeOptions['filter'] + value;
	activateItemCleanFilterButton();
	$itemIsotopeList.isotope(itemIsotopeOptions);
}
function removeItemFilterValue($itemIsotopeList, value) {
	var oldOptions = itemIsotopeOptions['filter'];
	var newOptions;
	if(value != undefined && value != '')
	{
		var splitedOptions = oldOptions.split(value);
		newOptions = splitedOptions[0].concat(splitedOptions[1]);
	}
	else
	{
		newOptions = oldOptions;
	}
	itemTypeaheadValue = undefined;
	if(newOptions == undefined || newOptions == '')
	{
		deactivateItemCleanFilterButton();
	}
	itemIsotopeOptions['filter'] = newOptions;
	$itemIsotopeList.isotope(itemIsotopeOptions);
}
function setItemTypeaheadValue($itemIsotopeList, value){
	removeItemFilterValue($itemIsotopeList, itemTypeaheadValue);
	itemTypeaheadValue = value;
	addItemFilterValue($itemIsotopeList,itemTypeaheadValue);
	$itemIsotopeList.isotope(itemIsotopeOptions);
}

//Permet de vider le filtre
function cleanItemFilter() {
	$('a#drop-filter-list').removeClass('active');
	$itemFilterInput.val('');
	itemIsotopeOptions['filter'] = '';
	$('ul#filters-list ul.tags-group a.filter-link.selected').each(function(){
		$(this).removeClass('selected');
	});
	deactivateItemCleanFilterButton();
	$itemIsotopeList.isotope(itemIsotopeOptions);
	return false;
}

function activateItemCleanFilterButton(){
	$('#li-clean-filter').removeClass('disabled hide');
	$('#li-clean-filter').find('a').removeClass('disabled');
}
function deactivateItemCleanFilterButton(){
	$('#item-li-clean-filter').addClass('disabled hide');
	$('#item-li-clean-filter').find('a').addClass('disabled');
}

$(document).ready(function()
{
	$window = $(window);
	$recItemList = $('#item-topbar');
	$itemIsotopeList = $('#item-isotope-list');
	$recItems = $('li.rec-item');
	recItemsTop = $recItemList.length && $recItemList.offset().top;
	$championContainer = $('div.champion-container');
	
	processScrollRecItems();
	
	$window.on('scroll', processScrollRecItems)
	
	itemIsotopeOptions = {
			itemSelector: '.item',
			animationEngine: 'jquery',
			masonry: {
				columnWidth: 30
			},
			animationOptions: {
				duration: 400,
				queue: false,
				opacity: 1
			},
			filter: '',
			containerStyle: {
				position: 'relative',
				overflow: 'visible'
			}
		};
	
	$itemIsotopeList.imagesLoaded( function(){
		$itemIsotopeList.isotope(itemIsotopeOptions);
	});
	
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
	$itemIsotopeList.on('mouseover', 'li.item', function(){
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
	
	//Bouton de generation du build
	$('#only-generate-build').click(generateRecItemBuilder);
	
	//Activation des tooltips
	$('#champion-isotope-list li.champion').tooltip();
	$('#item-isotope-list li.item').tooltip();
	
	$itemFilterInput = $('#item-filter-input');
	setTypeaheadItemsName();
	initTypeahead($itemIsotopeList);
	initFilterList($itemIsotopeList);
});