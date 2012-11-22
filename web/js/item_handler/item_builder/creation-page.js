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
	$championFilterInput,
	itemPopover,
	$itemSidebarBlockDivs,
	$itemSidebarList,
	$addItemBlockModal,
	$blockNameInputs
;

function addRecItem(slug, liBlockId) {
	$liBlock = $('#'+liBlockId);
	if ($liBlock.children('div.item-sidebar-block-div').find('div.portrait[data-slug="'+slug+'"]').length == 0) {
		$portrait = $('#'+slug).find('div.portrait').clone();
		$liBlock.find('div.item-sidebar-block-div div.indication').remove();
		$liBlock.children('div.item-sidebar-block-div').append($portrait.css('display', 'inline-block'));
	} else {
		displayMessage('Cet item est déjà présent dans le bloc.', 'error');
	}
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
function generateRecItemBuilder(saveBuild, itemBuildSlug) {
	
	saveBuild = saveBuild == undefined ? false : saveBuild;
	
	$champions = $('div.champion-container li.champion.active');
	
	gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
	buildName = $('input#build-name').val();
	path = $('#modal-lol-path').val();
	
	$items = $itemSidebarList.find('li div div.portrait');
	
	if($champions.length >= 1) {
		if(gameModesArray.indexOf(gameMode) >=0 ) {
			if ( $items.length > 0 ) {
				if (buildName.length > 0) {
					championsSlugs = new Array();
					$champions.each(function(){
						championsSlugs.push($(this).attr('id'));
					});

//					itemsSlugs = new Array();
//					$items.each(function(){
//						itemsSlugs.push($(this).find('div.portrait').data('slug'));
//					});

					itemsSlugs = new Array();
					$itemSidebarList.find('li.item-sidebar-block-li').each(function () {
						var blockName = $(this).find('input.item_sidebar_block_input').val();
						if(! (blockName in itemsSlugs)) {
							var blockArray = new Array();
							$(this).find('div.item-sidebar-block-div div.portrait').each(function() {
								blockArray.push($(this).data('slug'));
							});
							if (blockArray.length > 0) {
								itemsSlugs.push({name:blockName, items:blockArray});
							}
						}
					});

					var data =  {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName, path: path};
					if (saveBuild) {
						data.saveBuild = 'true';
					}					
					if(itemBuildSlug != undefined) {
						data.itemBuildSlug = itemBuildSlug;
					}
					
					$.ajax({
						type: 'POST',
						url:  Routing.generate('item_builder_generate_rec_item_file', {_locale: locale}),
						data: data,
						dataType: 'json'
					}).done(function(data){
						if(itemBuildSlug != undefined) {
							displayMessage('Les modifications ont bien été enregistrées.', 'success');
						} else {
							window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
						}
					}).fail(function(data){
						displayMessage('Impossible de créer le build.', 'error');
						console.log(data.responseText);
						$('#footer').html(data.responseText).css('text-align', 'left');
					})
				} else {
					displayMessage('Veuillez saisir un nom pour votre build.', 'error');
				}
			} else {
				displayMessage('Il faut sélectionner au moins un objet !', 'error');
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
		$(this).parents('li.dropdown.action').children('a.dropdown-toggle.action-button-link').addClass('active');
		activateButton($('li#item-li-clean-filter'));
		$isotope.addFilterValue('.'+$(this).attr('data-option-value'));
		
		return false;
	});
	//Clic sur un bouton de filtrage sélectionné
	$filterButtons.on('click', 'a.filter-link.selected',function() {
		$(this).removeClass('selected');
		$isotope.removeFilterValue('.'+$(this).attr('data-option-value'));
		if($(this).parent().parent().find('li a.filter-link.selected').size() <= 0) {console.log('ok');
			$(this).parents('li.dropdown.action').children('a.dropdown-toggle.action-button-link').removeClass('active');
			$(this).parent('a.dropdown-toggle').removeClass('active');
			if($isotope.filters.name == undefined || $isotope.filters.name == ''){
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
		$(this).parents('li.dropdown.action').children('a.dropdown-toggle.action-button-link').addClass('active');
		activateButton($('li#li-clean-filter')); 
		activateButton($('li#li-compare-filtered'));
		$isotope.addFilterValue('.'+$(this).attr('data-option-value'));
		
		return false;
	});
	//Clic sur un bouton de filtrage sélectionné
	$filterButtons.on('click', 'a.filter-link.selected',function() {
		$(this).removeClass('selected');
		$isotope.removeFilterValue('.'+$(this).attr('data-option-value'));
		if($(this).parent().parent().find('li a.filter-link.selected').size() <= 0) {
			$(this).parents('li.dropdown.action').children('a.dropdown-toggle.action-button-link').removeClass('active');
			$(this).parent('a.dropdown-toggle').removeClass('active');
			if($isotope.filters.name == undefined || $isotope.filters.name == ''){
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
		deactivateButton($('li#li-compare-filtered'));		
		return false;
	});
}
function initChampionAddFilteredAction($isotope) {
	//Lors du clic sur le bouton de nettoyage du filtre
	$('#li-compare-filtered').on('click', 'a', function(){
		$championIsotopeList.find('li.champion:not(.isotope-hidden):not(.active)').addClass('active');
		$('#btn-clear-champions').removeClass('disabled')
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
function checkRecItemsByChampionSpecific(championSlug) {
	if (championSlug == undefined) {
		$('ul#rec-item-sortable li.rec-item div.portrait[data-champion!=""]').parent('li.rec-item').removeClass('full').addClass('free').html('<div><i class="icon-question-sign icon-white"></i></div>');
	} else {
		var champRelatedItems = $('ul#rec-item-sortable li.rec-item div.portrait[data-champion!=""]');
		champRelatedItems.each(function(){
			if ($(this).data('champion') != championSlug) {
				$(this).parent('li.rec-item').removeClass('full').addClass('free').html('<div><i class="icon-question-sign icon-white"></i></div>');
			}
		});
	}
}

function initWithStoredItemBuild() {
	
	var championSlugs = getItemFromLS('storedChampionSlugs').split(',');
	var gameMode = getItemFromLS('storedGameMode');
	var itemSlugs = getItemFromLS('storedItemSlugs').split(',');
	var buildName = getItemFromLS('storedBuildName');
	
	for(var i = 0; i < championSlugs.length; i++) {
		if (championSlugs[i] != '') {
			$('#champion-isotope-list li.champion#'+championSlugs[i]).addClass('active');
		}
	}
	
	$('div.game-mode').removeClass('active');
	$('div.game-mode[data-game-mode="'+gameMode+'"]').addClass('active');
	
	for(var i = 0; i < itemSlugs.length; i++) {
		addItemToList(itemSlugs[i]);
	}
	
	$('#build-name').val(buildName);
	
	var activeChampions = $('ul#champion-isotope-list li.champion.active');
	if (activeChampions.length > 0) {
		activateButton($('li#li-compare-filtered'));
		$('#btn-clear-champions').removeClass('disabled')
	}
	
	delete localStorage['storedItemBuild'];
	delete localStorage['storedChampionSlugs'];
	delete localStorage['storedGameMode'];
	delete localStorage['storedItemSlugs'];
	delete localStorage['storedBuildName'];
}

function storeItemBuild() {
	var championSlugs = new Array();
	$('#champion-isotope-list li.champion.active').each(function(){
		championSlugs.push($(this).attr('id'));
	});

	var gameMode = $('div.game-mode-container div.game-mode.active').first().attr('data-game-mode');

	var itemSlugs = new Array();
	$('#rec-item-sortable li.rec-item.full div.portrait').each(function(){
		itemSlugs.push($(this).attr('data-slug'));
	});

	var buildName = $('#build-name').val();
	
	saveItemInLS('storedItemBuild', 'true');
	saveItemInLS('storedChampionSlugs', championSlugs);
	saveItemInLS('storedGameMode', gameMode);
	saveItemInLS('storedItemSlugs', itemSlugs);
	saveItemInLS('storedBuildName', buildName);
}

//Permet de récupérer le contenu des popover pour les items en AJAX
function setItemPopoverContent(slug, $item) {
	$.ajax({
		type: 'POST',
		url:  Routing.generate('item_builder_get_item_popover_content', {_locale: locale}),
		data: {slug: slug},
		dataType: 'html'
	}).done(function(data){
		$item.data('popover').$tip.find(".popover-content").html(data);
		$item.data('ajax-loaded', true);
	});
}

function initItemDraggable() {
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
				$itemSidebarList.find('li div div.indication').css('border-color', '#FB9701');
			},
			stop: function(){
				$itemSidebarList.find('li div div.indication').css('border-color', '#999');
			}
		});
	});
}
function initItemDroppable($divs) {
	$divs.droppable({
		accept: '#item-isotope-list li.item',
		over: function() {
			$(this).find('div.indication').css('border-color', 'red');
		},
		out: function() {
			$(this).find('div.indication').css('border-color', '#FB9701');
		},
		drop: function( event, ui ) {
			addRecItem(ui.draggable.context.id, $(this).parent().attr('id'));
			$(this).css('border-color', '#FB9701')
		}
	});
}

//Lors du clic sur un item présent dans un block
function initItemClickInBlock() {
	$itemSidebarList.on('click', 'li div div.portrait', function() {
		$parent = $(this).parent();
		if($parent.find('div.portrait').length == 1) {
			$(this).remove();
			$parent.html('<div class="indication">Faites glissez vos items ici</div>');
		} else {
			$(this).remove();
		}
	});
}

function initItemBlocksSortable() {
	$itemSidebarList.sortable();
}

function initItemAddBlock() {
	$('#btn-add-item-block').click(function() {
		$addItemBlockModal.modal('show');
		return false;
	});
	$addItemBlockModal.find('#modal-add-item-block-save').click(function() {
		var $name = $addItemBlockModal.find('#modal-item-block-name');
		var name = $.trim($name.val());
		var $modalError = $('#modal-add-item-block-error');
		
		var regex = new RegExp("[^a-zA-Z0-9 ]");
		
		if (name != undefined && name != '' && !regex.test(name)) {
			name = name.replace(/ +/g, ' ');
			if ( isItemBlockNameFree(name) ) {
				$itemSidebarList.append('<li class="item-sidebar-block-li" id="'+name+'-item-block-li"><input type="text" class="item_sidebar_block_input span9" value="'+name+'"/> <a href="#" class="btn-delete-block-item btn btn-danger btn-small">X</a><div class="item-sidebar-block-div" id="'+name+'-item-block-div"><div class="indication">Faites glissez vos items ici</div></div></li>')
				$modalError.html('');
				$name.val('');
				initItemDroppable($itemSidebarList.find('li:last div.item-sidebar-block-div'));
				$addItemBlockModal.modal('hide');
			} else {
				$modalError.html('Ce nom est déjà utilisé');
			}
		} else {
			$modalError.html('Ce nom est invalide');
		}
		return false;
	});
}

function initItemRemoveBlock() {
	$itemSidebarList.on('click', '.btn-delete-block-item',function() {
		$(this).parent().remove();
		return false;
	});
}

function isItemBlockNameFree(name) {
	return $itemSidebarList.find('li.item-sidebar-block-li input[value="'+name+'"]').length <= 0;
}

function initGameMode() {
	$('div.game-mode-container').on('click', 'div.game-mode', function() {
		$('div.game-mode-container div.game-mode').removeClass('active');
		$(this).addClass('active');
		$itemIsotopeList.setGameModeFilter($(this).data('game-mode'));
		checkRecItemsByMode($itemIsotopeList.filters.gameMode);
	});
}

function initPopoverItem() {
	var popoverTimer;
	$('ul#item-isotope-list li.item').hover(function(e) {
		$(this).data('isHover', true);
		if(popoverTimer) {
			clearTimeout(popoverTimer);
			popoverTimer = null
		}
		if($(this).data('popover') == undefined) {
			
			var title = "<img class='tooltip-item-img pull-left' src='/images/items/" + $(this).data('code') + ".png'/>" + $(this).data('title');
			
			$(this).popover({
				trigger: 'hover',
				content:'<p style="text-align: center;"><img src="/images/commons/loader16-bg-blue.gif" alt="loading"/></p>',
				placement: 'bottom',
				delay: {show: 1, hide: 1}
			});
			$(this).data('popover').options.title = title;
			$(this).data('popover').options.placement = 'bottom';
			$(this).popover('show');
		}
		
		if($(this).data('ajax-loaded') == undefined) {
			var $that = $(this);
			popoverTimer = setTimeout(function() {
				if($that.data('isHover')) {
					setItemPopoverContent($that.attr('id'), $that);
				}
			}, 500)
		}
	}, function(){
		$(this).data('isHover', false);
	});
}

function initChampionClick() {
	$championContainer.on('click', 'li.champion', function() {
		//On rend actif le champion
		$(this).toggleClass('active');
		
		var activeChampions = $('ul#champion-isotope-list li.champion.active');
		//s il y a plus d un champion selectionné on ne peut pas laisser les items dedies a des champions particuliers
		if (activeChampions.length > 1) {
			$itemIsotopeList.hideChampionSpecificItems();
			checkRecItemsByChampionSpecific();
		}else if(activeChampions.length > 0) {
			$('#btn-clear-champions').removeClass('disabled').parent('li').removeClass('hide');
			
			var championSlug = activeChampions.first().data('name');
			var relatedItems = $('ul#item-isotope-list li.item div.portrait[data-champion="'+championSlug+'"]');
			if (relatedItems != undefined && relatedItems.length > 0) {
				$itemIsotopeList.showChampionSpecificItems(championSlug);
			} else {
				$itemIsotopeList.hideChampionSpecificItems();
			}
			
			checkRecItemsByChampionSpecific(championSlug);
		} else {
			$('#btn-clear-champions').addClass('disabled');
			$itemIsotopeList.showChampionSpecificItems();
		}
	});
}

function initBlockInputName() {
	$itemSidebarList.on('focusin', 'li input.item_sidebar_block_input', function() {
		var oldName = $.trim($(this).val());
		
		$(this).focusout(function() {
			var newName = $.trim($(this).val());
			var regex = new RegExp("[^a-zA-Z0-9 ]");
			
			if ( ! (newName != '' && !regex.test(newName))) {
				$(this).val(oldName);
				displayMessage('Le nom saisi n\'est pas valide.', 'error')
			} else {
				newName = newName.replace(/ +/g, ' ');
				oldName = newName;
				$(this).attr('value', newName)
				$(this).val(oldName);
				newName = newName.replace(/ +/g, '_');
				$(this).parent('li').attr('id', '__' + newName + '__item-block-li')
			}
		})
	});
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
	$itemSidebarBlockDivs = $('div.item-sidebar-block-div');
	$itemSidebarList = $('ul#item_sidebar_blocks_list');
	$addItemBlockModal = $('#modal-add-item-block');
	$blockNameInputs = $('input.item_sidebar_block_input');
	
	processScrollRecItems();
	
	$window.on('scroll', processScrollRecItems);
	
	//On charge le build depuis le storage s il y en a un
	if (getItemFromLS('storedItemBuild')) {
		initWithStoredItemBuild();
	}
	
	// Écoute sur l'événement d'un clique sur un des modes de jeu
	initGameMode();
	
	//Hover un item
	initPopoverItem();
	
	//On rends chaque item draggable
	initItemDraggable();
	
	//On rends la rec item list capable d accepter les items
	initItemDroppable($itemSidebarBlockDivs);
	
	//Lors du clic sur un item présent dans un block
	initItemClickInBlock();
	
	//On active le module sortable pour les blocks d items
	initItemBlocksSortable();
	
	//Lors du clic sur le bouton ajouter un block
	initItemAddBlock();
	
	//Lors du clic sur un bouton de suppression de block
	initItemRemoveBlock();
	
	//Lors du clic sur un champion
	initChampionClick();
	
	//Lors de la prise de focus par les input des block dédiés a l edition des noms des bocks
	initBlockInputName();
	
	//Bouton de generation du build uniquement
	$('#only-generate-build, #modal-btn-only-generate-build').click(function(e){
		e.preventDefault();
		$('#modal-authenticate-build').modal('hide');
		$('#modal-btn-download').attr('data-save-build', 'false');
		$('#modal-dl-build').modal('show');
	});
	$('#save-and-generate-build').click(function(e) {
		e.preventDefault();
		$('#modal-btn-download').attr('data-save-build', 'true');
		$('#modal-dl-build').modal('show');
	});
	$('#save-and-generate-build-not-authenticated').click(function(e) {
		e.preventDefault();
		$('#modal-authenticate-build').modal('show');
	});
	$('#save-build').click(function(e) {
		e.preventDefault();
		generateRecItemBuilder(true, $(this).data('slug'));
	});
	$('#modal-btn-download').click(function(e) {
		e.preventDefault();
		$('#modal-dl-build').modal('hide');
		generateRecItemBuilder($(this).data('save-build'));
	});
	
	//Si l utilisateur demande a s authentifier ou à s inscrire alors qu'il  est en train de créer un build
	$('#modal-btn-authentication, #modal-btn-inscription').click(function(e){
		e.preventDefault();
		storeItemBuild();
		window.location = this.href;
	});
	
	//Activation du bouton de vidage de la liste des champions
	$('#btn-clear-champions').on('click', function(e) {
		e.preventDefault();
		if ( ! $(this).hasClass('disabled') ) {
			$('li.champion.isotope-item').removeClass('active');
			$(this).addClass('disabled');
		}
	});
	
	//Activation des tooltips
	$('#champion-isotope-list li.champion').tooltip({delay:{show:1, hide:0}});
	$('#champion-isotope-list li.champion').data('tooltip').options.delay = 0;
	
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
	$itemIsotopeList.initTypeahead($itemFilterInput, Routing.generate('item_builder_get_items_name',{_locale: locale}), $('li#item-li-clean-filter'));
	initItemFilterList($itemIsotopeList, $('ul#item-filters-list li'));
	initItemCleanAction($itemIsotopeList, $itemFilterInput);
		
	$championFilterInput = $('#champion-filter-input');
	
	$championIsotopeList.options = championIsotopeOptions;
	$championIsotopeList.filters = championIsotopeFilters;
	$championIsotopeList.initTypeahead($championFilterInput, Routing.generate('champion_handler_front_get_champions_name',{_locale: locale}), $('li#li-clean-filter'));
	initChampionFilterList($championIsotopeList, $('ul#filters-list li'));
	initChampionCleanAction($championIsotopeList, $championFilterInput);
	initChampionAddFilteredAction($championIsotopeList);
});