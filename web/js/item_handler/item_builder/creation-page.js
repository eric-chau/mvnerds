
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

//Permet de generer le build
function generateRecItemBuilder() {
	$champions = $('div.champion-container li.champion.active');
	$items = $('#item-topbar li.rec-item.full');
	gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
	buildName = $('input#build-name').val();
	
	if($champions.length >= 1 && $items.length == 6 && gameMode.length > 0 && buildName.length > 0) {
		championsSlugs = new Array();
		$champions.each(function(){
			championsSlugs.push($(this).attr('id'));
		});
		
		itemsSlugs = new Array();
		$items.each(function(){
			itemsSlugs.push($(this).find('div.portrait').data('slug'));
		});
		
		$.ajax({
			type: 'POST',
			url:  Routing.generate('item_builder_generate_rec_item_file', {_locale: locale}),
			data: {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName},
			dataType: 'json'
		}).done(function(data){
			window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
		}).fail(function(data){
			console.log(data);
		});
	}
	else {
		console.log('error : not enough champions or rec items, or no game mode or buildName set');
	}
	
	return false;
}

/************************************************************************** FILTER ***************************************************************/
var itemTypeaheadValue, $itemFilterInput;

function initTypeahead($isotope, $filterInput, route) {
	//lorsqu'un  changement survient dans le typeahead
	$filterInput.on('keyup change',function(){
		$isotope.setNameFilter($filterInput.val());
	});
	//Chargement de l autocompletion du champ de recherche
	if (route != undefined) {
		$.ajax({
			type: 'POST',
			url:  route, 
			dataType: 'html'
		}).done(function(data){
			$filterInput.attr('data-source', data);
		});
	}
}

function initItemFilterList($isotope, $filterButtons) {
	//Clic sur un bouton de filtrage non selectionné
	$filterButtons.on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		$(this).parent('a.dropdown-toggle').addClass('active');
		activateButton($('li#item-li-clean-filter'));
		$isotope.addFilterValue('.'+$(this).attr('data-option-value'));
		return false;
	});
	//Clic sur un bouton de filtrage sélectionné
	$filterButtons.on('click', 'a.filter-link.selected',function() {
		$(this).removeClass('selected');
		$isotope.removeFilterValue('.'+$(this).attr('data-option-value'));
		if($(this).parent().find('a.filter-link.selected').size() <= 0)
		{
			$(this).parent('a.dropdown-toggle').removeClass('active');
			if($isotope.options == undefined || $isotope.options == ''){
				deactivateButton($('li#item-li-clean-filter'));
			}
		}
		return false;
	});	
}

function initItemCleanAction($isotope, $filterInput) {
	//Lors du clic sur le bouton de nettoyage du filtre
	$('#item-li-clean-filter').on('click', '#btn-clean-filter', function(){
		$isotope.cleanFilter($filterInput);
		$('a#drop-filter-list').removeClass('active');
		$('ul#item-filters-list ul.tags-group a.filter-link.selected').removeClass('selected');
		deactivateButton($('li#btn-clean-filter'));
		return false;
	});
}

//TODO a generaliser ailleurs. Plugin ?
function activateButton($buttonLi){
	$buttonLi.removeClass('disabled hide');
	$buttonLi.find('a').removeClass('disabled');
}
function deactivateButton($buttonLi){
	$buttonLi.addClass('disabled hide');
	$buttonLi.find('a').addClass('disabled');
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
	$('div.game-mode-container div.game-mode').on('click', function() {
		var $this = $(this);
		$('div.game-mode-container div.game-mode').each(function() {
			$(this).removeClass('active');
		});

		$this.addClass('active');
	});
	
	//Lors du clic ou du double clic sur un item
	var timeout, dblClic = false, that;
	$('ul#item-isotope-list').on('click', 'li.item:not(.item-maxi)', function(e) {
		that = this;
		
		if(!$(that).hasClass('animating')) {
			e.preventDefault();
			timeout = setTimeout(function() {
				if (!dblClic) {
					timeout = null;
					console.log('maximize item');
				}
				else {
					dblClic = false;
				}
			}, 200);
		}
	}).on('dblclick', function() {
		if(!$(that).hasClass('item-maxi') && !$(that).hasClass('animating')) {
			clearTimeout(timeout);
			timeout = null;
			dblClic = true;
			addItemToList($(that).attr('id'));
		}
	});
	
	//On rends chaque item draggable
	$itemIsotopeList.on('mouseover', 'li.item', function() {
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
		over: function() {
			$(this).css('border-color', 'red');
		},
		out: function() {
			$(this).css('border-color', '#FB9701');
		},
		drop: function( event, ui ) {
			setRecItem(ui.draggable.context.id, $(this).attr('id'));
			$(this).css('border-color', 'rgba(255, 255, 255, .2)')
		}
	});
	
	//Lors du clic sur un objet recommendé rempli
	$recItemList.on('click', 'li.rec-item.full', function() {
		$(this).removeClass('full');
		$(this).addClass('free');
		$(this).html('<div><i class="icon-question-sign icon-white"></i></div>');
	});
	
	 $('#rec-item-sortable').sortable({
		placeholder: 'rec-item hold-rec-item-place'
	});
	
	//Lors du clic sur un champion
	$championContainer.on('click', 'li.champion', function() {
		//On rend actif le champion
		$(this).toggleClass('active');
	});
	
	//Bouton de generation du build
	$('#only-generate-build').click(generateRecItemBuilder);
	
	//Activation des tooltips
	$('#champion-isotope-list li.champion').tooltip();
	$('#item-isotope-list li.item').tooltip();
	
	//Activation des filtres
	$itemFilterInput = $('#item-filter-input');
	//On set les options sur l objet isotope pour y acceder plus facilement
	$itemIsotopeList.options = itemIsotopeOptions;
	initTypeahead($itemIsotopeList, $itemFilterInput, Routing.generate('item_builder_get_items_name',{_locale: locale}));
	initItemFilterList($itemIsotopeList, $('ul#item-filters-list li'));
	initItemCleanAction($itemIsotopeList, $itemFilterInput);
});