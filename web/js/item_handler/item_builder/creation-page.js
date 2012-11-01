var	
	$window,
	$recItemList,
	$recItems,
	recItemsTop,
	isRecItemsFixed = false,
	$championContainer,
	$itemIsotopeList,
	$championIsotopeList,
	itemIsotopeOptions,
	gameModesArray = ['dominion', 'classic', 'aram', 'twisted-treeline'],
	itemIsotopeFilters,
	itemTypeaheadValue, 
	$itemFilterInput,
	championTypeaheadValue, 
	$championFilterInput
;

function setRecItem(slug, recItemId) {
	$recItem = $('#'+recItemId);
	$portrait = $('#'+slug).find('div.portrait').clone();
	$recItem.html($portrait.css('display', 'inline-block')).removeClass('free').addClass('full');
}
//Ajoute un item grace a son slug au premier emplacement d objets recommandés libre
function addItemToList(slug) {
	setRecItem(slug,$('li.rec-item.free').first().attr('id'));
}

 //Fixation de la liste d objets recommandés
function processScrollRecItems() {
	var scrollTop = $window.scrollTop();

	if (scrollTop >= recItemsTop - 9.85 && ! isRecItemsFixed) {
		isRecItemsFixed = true;
		$recItemList.addClass('fixed');
		$recItemList.parent('.item-container').addClass('fixed');
	} else if (scrollTop <= recItemsTop - 9.85 && isRecItemsFixed) {
		isRecItemsFixed = false;
		$recItemList.removeClass('fixed');
		$recItemList.parent('.item-container').removeClass('fixed');
	}
}

//Permet de generer le build
function generateRecItemBuilder(saveBuild) {
	
	saveBuild = saveBuild == undefined ? false : saveBuild;
	
	$champions = $('div.champion-container li.champion.active');
	$items = $('#item-topbar li.rec-item.full');
	gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
	buildName = $('input#build-name').val();
	path = $('#modal-lol-path').val();
	
	if($champions.length >= 1) {
		if(gameModesArray.indexOf(gameMode) >=0 ) {
			if ( $items.length == 6 ) {
				if (buildName.length > 0) {
					championsSlugs = new Array();
					$champions.each(function(){
						championsSlugs.push($(this).attr('id'));
					});

					itemsSlugs = new Array();
					$items.each(function(){
						itemsSlugs.push($(this).find('div.portrait').data('slug'));
					});

					var data
					if (saveBuild) {
						data =  {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName, path: path, saveBuild: 'true'};
					} else {
						data =  {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName, path: path};
					}

					$.ajax({
						type: 'POST',
						url:  Routing.generate('item_builder_generate_rec_item_file', {_locale: locale}),
						data: data,
						dataType: 'json'
					}).done(function(data){
						window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
					}).fail(function(data){
						console.log(data.responseText);
						$('#footer').html(data.responseText);
						displayMessage('Impossible de créer le build.', 'error');
					})
				} else {
					displayMessage('Veuillez saisir un nom pour votre build.', 'error');
				}
			} else {
				displayMessage('Il faut sélectionner vos 6 objets recommandés !', 'error');
			}
		} else {
			displayMessage('Le mode de jeu sélectionné est incorrect', 'error');
		}
	} else {
		displayMessage('Pas assez de champions sélectionnés !', 'error');
	}
	
	return false;
}

/************************************************************************** FILTER ***************************************************************/

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
		if($(this).parent().find('a.filter-link.selected').size() <= 0) {
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
	$('#item-li-clean-filter').on('click', 'a', function(){
		$isotope.cleanFilter($filterInput);
		$(this).parents('li.dropdown').find('a.dropdown-toggle').removeClass('active');
		$(this).parents('ul.dropdown-menu').find('a.filter-link.selected').removeClass('selected');
		deactivateButton($('li#item-li-clean-filter'));
		
		return false;
	});
}

function initChampionFilterList($isotope, $filterButtons) {
	//Clic sur un bouton de filtrage non selectionné
	$filterButtons.on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		$(this).parent('a.dropdown-toggle').addClass('active');
		activateButton($('li#li-clean-filter'));
		$isotope.addFilterValue('.'+$(this).attr('data-option-value'));
		
		return false;
	});
	//Clic sur un bouton de filtrage sélectionné
	$filterButtons.on('click', 'a.filter-link.selected',function() {
		$(this).removeClass('selected');
		$isotope.removeFilterValue('.'+$(this).attr('data-option-value'));
		if($(this).parent().find('a.filter-link.selected').size() <= 0) {
			$(this).parent('a.dropdown-toggle').removeClass('active');
			if($isotope.options == undefined || $isotope.options == ''){
				deactivateButton($('li#li-clean-filter'));
			}
		}
		
		return false;
	});	
}
function initChampionCleanAction($isotope, $filterInput) {
	//Lors du clic sur le bouton de nettoyage du filtre
	$('#li-clean-filter').on('click', 'a', function(){
		$isotope.cleanFilter($filterInput);
		$(this).parents('li.dropdown').find('a.dropdown-toggle').removeClass('active');
		$(this).parents('ul.dropdown-menu').find('a.filter-link.selected').removeClass('selected');
		deactivateButton($('li#li-clean-filter'));
		
		return false;
	});
}
function initChampionAddFilteredAction($isotope) {
	//Lors du clic sur le bouton de nettoyage du filtre
	$('#li-compare-filtered').on('click', 'a', function(){
		$championIsotopeList.find('li.champion:not(.isotope-hidden):not(.active)').addClass('active');
		
		return false;
	});
}

function checkRecItemsByMode($gameMode) {
	$('ul#rec-item-sortable li.rec-item div.portrait').each( function() {
		itemGameModes = $(this).data('game-modes');
		if ( itemGameModes.indexOf($gameMode) < 0 && itemGameModes.indexOf('shared') < 0 ) {
			$(this).parent('li.rec-item').removeClass('full').addClass('free').html('<div><i class="icon-question-sign icon-white"></i></div>');
		}
	});
}

//Retire tous les objets spécifiques a des champions des objets recommandés
function checkRecItemsByChampionSpecific() {
	$('ul#rec-item-sortable li.rec-item div.portrait[data-champion!=""]').parent('li.rec-item').removeClass('full').addClass('free').html('<div><i class="icon-question-sign icon-white"></i></div>');
}

/******************************************************* Maximisation isotope *************************************************/
function maximizeItem($item, $isotope){	
	//Si on trouve un autre champion déjà maximisé on le referme
	var $maxiItem = $isotope.find('li.item-maxi');
	if($maxiItem != undefined){
		minimizeItem($maxiItem, $isotope);
	}
	
	$item.find('div.portrait').fadeOut(250);
	$item.addClass('item-maxi');
	setTimeout(function() {
		$item.find('div.preview').fadeIn(250);
		$item.find('div.item-portrait').fadeIn(250);
		
		$isotope.isotope( 'reLayout');
	},
	320);

	return false;
}

function minimizeItem($item, $isotope){
	$item.find('div.item-portrait').fadeOut(150);
	$item.find('div.preview').fadeOut(150);
	setTimeout(function() {
		$item.find('div.portrait').fadeIn(300);
		
		$item.toggleClass('animate-item-portrait item-maxi');
		setTimeout(function() {
			$isotope.isotope( 'reLayout');
			$item.removeClass('animate-item-portrait');
		},
		320);
	},
	150);

	return false;
}

$(document).ready(function()
{
	$window = $(window);
	$recItemList = $('#item-topbar');
	$itemIsotopeList = $('#item-isotope-list');
	$championIsotopeList = $('#champion-isotope-list');
	$recItems = $('li.rec-item');
	recItemsTop = $recItemList.length && $recItemList.offset().top;
	$championContainer = $('div.champion-container');
	
	processScrollRecItems();
	
	$window.on('scroll', processScrollRecItems);
	
	// Écoute sur l'événement d'un clique sur un des modes de jeu
	$('div.game-mode-container').on('click', 'div.game-mode', function() {
		$('div.game-mode-container div.game-mode').removeClass('active');
		$(this).addClass('active');
		$itemIsotopeList.setGameModeFilter($(this).data('game-mode'));
		checkRecItemsByMode($itemIsotopeList.filters.gameMode);
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
					maximizeItem($(that), $itemIsotopeList);
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
	//Lors du clic sur le bouton close d un champion maximisé
	$itemIsotopeList.on('click', 'li.item-maxi div.preview-header', function(){
		return minimizeItem($('#'+$(this).attr('data-dissmiss')), $itemIsotopeList);
	});
	$itemIsotopeList.on('click', 'li.item-maxi a.btn-add-to-list', function(){
		addItemToList($(this).data('item-slug'));
		return false;
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
		$(this).removeClass('full').addClass('free').html('<div><i class="icon-question-sign icon-white"></i></div>');
	});
	
	 $('#rec-item-sortable').sortable({
		placeholder: 'rec-item hold-rec-item-place'
	});
	
	//Lors du clic sur un champion
	$championContainer.on('click', 'li.champion', function() {
		//On rend actif le champion
		$(this).toggleClass('active');
		
		var activeChampions = $('ul#champion-isotope-list li.champion.active');
		//s il y a plus d un champion selectionné on ne peut pas laisser les items dedies a des champions particuliers
		if (activeChampions.length > 1) {
			$itemIsotopeList.hideChampionSpecificItems();
			checkRecItemsByChampionSpecific();
		}else if(activeChampions.length > 0) {
			var championSlug = activeChampions.first().data('name');
			var relatedItems = $('ul#item-isotope-list li.item div.portrait[data-champion="'+championSlug+'"]');
			if (relatedItems != undefined && relatedItems.length > 0) {
				$itemIsotopeList.showChampionSpecificItems(championSlug);
			} else {
				$itemIsotopeList.hideChampionSpecificItems();
			}
		} else {
			$itemIsotopeList.showChampionSpecificItems();
		}
	});
	
	//Bouton de generation du build
	$('#only-generate-build').click(function(e){
		e.preventDefault();
		$('#modal-btn-download').attr('data-save-build', 'false');
		$('#modal-dl-build').modal('show');
	});
	$('#save-and-generate-build').click(function(e) {
		e.preventDefault();
		$('#modal-btn-download').attr('data-save-build', 'true');
		$('#modal-dl-build').modal('show');
	});
	$('#modal-btn-download').click(function(e) {
		e.preventDefault();
		generateRecItemBuilder($(this).data('save-build'));
	});
	
	//Activation des tooltips
	$('#champion-isotope-list li.champion').tooltip();
	$('#item-isotope-list li.item').tooltip();
	
	
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
		filter: '.classic, .shared',
		containerStyle: {
			position: 'relative',
			overflow: 'visible'
		}
	};
	itemIsotopeFilters = {
		tags : [],
		name: '',
		gameMode: 'classic'
	};
	championIsotopeOptions = {
		itemSelector: '.champion',
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
	championIsotopeFilters = {
		tags : [],
		name: ''
	};
	
	$itemIsotopeList.imagesLoaded( function(){
		$itemIsotopeList.isotope(itemIsotopeOptions);
	});
	$championIsotopeList.isotope(championIsotopeOptions);
	
	//Activation des filtres
	$itemFilterInput = $('#item-filter-input');
	//On set les options sur l objet isotope pour y acceder plus facilement
	$itemIsotopeList.options = itemIsotopeOptions;
	$itemIsotopeList.filters = itemIsotopeFilters;
	$itemIsotopeList.initTypeahead($itemFilterInput, Routing.generate('item_builder_get_items_name',{_locale: locale}));
	initItemFilterList($itemIsotopeList, $('ul#item-filters-list li'));
	initItemCleanAction($itemIsotopeList, $itemFilterInput);
	
	
	$championFilterInput = $('#champion-filter-input');
	
	$championIsotopeList.options = championIsotopeOptions;
	$championIsotopeList.filters = championIsotopeFilters;
	$championIsotopeList.initTypeahead($championFilterInput, Routing.generate('champion_handler_front_get_champions_name',{_locale: locale}));
	initChampionFilterList($championIsotopeList, $('ul#filters-list li'));
	initChampionCleanAction($championIsotopeList, $championFilterInput);
	initChampionAddFilteredAction($championIsotopeList);
});