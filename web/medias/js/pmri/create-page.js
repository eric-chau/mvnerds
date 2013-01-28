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
	$blockNameInputs,
	isBuildSaved,
	saveInProgress,
	nbBlocks
;

function addRecItem(slug, liBlockId) {
	var $liBlock = $('#'+liBlockId);
	var $item = $liBlock.children('div.item-sidebar-block-div').find('div.portrait[data-slug="'+slug+'"]');
	if ($item.length == 0) {
		var $portrait = $('#'+slug).find('div.portrait').clone();
		$portrait.addClass('item');
		$liBlock.find('div.item-sidebar-block-div div.indication').remove();
		$liBlock.children('div.item-sidebar-block-div').append($portrait.css('display', 'inline-block'));
	} else {
		var $itemCount = $item.find('span.item-count');
		if ($itemCount.length == 0) {
			$item.append('<span class="item-count">2</span>')
		} else {
			$itemCount.html($itemCount.html()*1 + 1);
		}
		//displayMessage('Cet item est déjà présent dans le bloc.', 'error');
	}
}

//Utilisé pour charger le build depuis le llocal storage
function addManyRecItems(slug, liBlockId, count) {
	for (var i = 0; i < count; i++) {
		addRecItem(slug, liBlockId);
	}
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

function simulateClickDownload(linkEl) {console.log('simul');
	if (HTMLElement.prototype.click) {console.log('proto');
		// You'll want to create a new element so you don't alter the page element's
		// attributes, unless of course the target attr is already _blank
		// or you don't need to alter anything
		var linkElCopy = $.extend(true, Object.create(linkEl), linkEl);
		$(linkElCopy).attr('target', '_blank');
		console.log($(linkElCopy));
		console.log(linkElCopy);
		linkElCopy.click();
	} else {console.log('else');
		// As Daniel Doezema had said
		window.open($(linkEl).attr('href'));
	}
};

function isBuildValid() {
	var $champions = $('div.champion-container li.champion.active');
	
	var gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
	var buildName = $('input#build-name').val();
	var path = $('#modal-lol-path').val();
	
	var $items = $itemSidebarList.find('li div div.portrait');
	
	if($champions.length >= 1) {
		if(gameModesArray.indexOf(gameMode) >=0 ) {
			if ( $items.length > 0 ) {				
				if (buildName.length > 0) {
					var isValid = true;
					$('li.item-sidebar-block-li').each(function() {
						if($(this).children('input.item_sidebar_block_input').val() == '') {
							if ($(this).find('div.item-sidebar-block-div div.portrait').length > 0) {
								if (locale == 'en') {
									displayMessage('Please name all of your created blocks.', 'success');
								}else {
									displayMessage('Veuillez saisir un nom pour tous vos blocs créés.', 'error');
								}
								isValid = false;
								return false;
							}
						}
						return true;
					});
					return isValid;
				} else {
					if (locale == 'en') {
						displayMessage('Please set a name for your build.', 'success');
					}else {
						displayMessage('Veuillez saisir un nom pour votre build.', 'error');
					}
				}
			} else {
				if (locale == 'en') {
					displayMessage('You have to select at least one item!.', 'success');
				}else {
					displayMessage('Il faut sélectionner au moins un objet !', 'error');
				}
			}
		} else {
			if (locale == 'en') {
				displayMessage('The selected game mode is not valid.', 'success');
			}else {
				displayMessage('Le mode de jeu sélectionné est incorrect', 'error');
			}
		}
	} else {
		if (locale == 'en') {
			displayMessage('Not enough champions selected.', 'success');
		}else {
			displayMessage('Pas assez de champions sélectionnés !', 'error');
		}
	}
	return false;
}

//Permet de generer le build
function generateRecItemBuilder(saveBuild, itemBuildSlug) {
	if (saveInProgress == false && isBuildSaved == false) {
		saveInProgress = true;
		
		$('div.generate-button-container').prepend('<img id="loading-save-build" src="/images/commons/loader.gif" alt="loading"/>');
		
		saveBuild = saveBuild == undefined ? false : saveBuild;

		var $champions = $('div.champion-container li.champion.active');

		var gameMode = $('div.game-mode-container div.game-mode.active').first().data('game-mode');
		var buildName = $('input#build-name').val();
		var buildDescription = $('textarea#build-description').val();
		var isBuildPrivate = $('input#build-private').prop('checked');
		var path = $('#modal-lol-path').val();

		var $items = $itemSidebarList.find('li div div.portrait');

		if (isBuildValid()) {
			
			championsSlugs = new Array();
			$champions.each(function(){
				championsSlugs.push($(this).attr('id'));
			});

			var itemsSlugs = new Array();
			$itemSidebarList.find('li.item-sidebar-block-li').each(function () {
				var blockName = $(this).find('input.item_sidebar_block_input').val();
				var blockDescription = $.trim($(this).attr('data-description'));//TODO ESCAPE
				if(blockName != undefined && blockName != '' && ! (blockName in itemsSlugs)) {
					var blockArray = new Array();
					$that = $(this);
					$(this).find('div.item-sidebar-block-div div.portrait').each(function() {
						var $itemCount = $(this).find('span.item-count');
						var itemCount = 1;
						if ($itemCount.length > 0) {
							itemCount = $itemCount.html() *1;
							itemCount = itemCount >= 1 ? itemCount : 1;
						}
						blockArray.push({slug: $(this).data('slug'), count: itemCount, order: ($($that.find('.portrait')).index($(this)) + 1)});
					});
					if (blockArray.length > 0) {
						itemsSlugs.push({name:blockName, items:blockArray, description: blockDescription});
					}
				}
			});
			
			var data =  {championsSlugs : championsSlugs, itemsSlugs: itemsSlugs, gameMode: gameMode, buildName: buildName, path: path, description: buildDescription, isBuildPrivate: isBuildPrivate};
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
				//Si c'est une édition
				if(itemBuildSlug != undefined) {
					isBuildSaved = true;
					if (locale == 'en') {
						displayMessage('Modifications have been saved successfully.', 'success');
					}else {
						displayMessage('Les modifications ont bien été enregistrées.', 'success');
					}
					window.location = Routing.generate('item_builder_view', {_locale: locale, itemBuildSlug: data});
				} else {
					//si c'est un nouveau build
					
					//Si c'est une simple génération
					if (!saveBuild) {
						$('#modal-dl-build').modal('hide');
						window.location = Routing.generate('item_builder_download_file', {_locale: locale, itemBuildSlug: data});
						if (locale == 'en') {
							displayMessage('The file has been successfully generated.', 'success');
						}else {
							displayMessage('Le fichier a bien été généré.', 'success');
						}
					} else {
						isBuildSaved = true;
						//si c'est un enregistrement suivi d'un téléchargement
						window.location = Routing.generate('pmri_list_detail', {_locale: locale, itemBuildSlug: data, dl: 'dl'});
					}
					$('#loading-save-build').remove();
				}
				saveInProgress = false;
			}).fail(function(data){
				$('#loading-save-build').remove();
				if (locale == 'en') {
					displayMessage('Impossible to create the build.', 'success');
				}else {
					displayMessage('Impossible de créer le build.', 'error');
				}
				saveInProgress = false;
			})
		} else {
			$('#loading-save-build').remove();
			saveInProgress = false;
		}
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

/******************************** LOCAL STORAGE **************************************/
function initWithStoredItemBuild() {
	
	var championSlugs = getItemFromLS('storedChampionSlugs').split(',');
	var gameMode = getItemFromLS('storedGameMode');
	var itemSlugs = getItemFromLS('storedItemSlugs').split(',');
	var buildName = getItemFromLS('storedBuildName');
	var buildDescription = getItemFromLS('storedBuildDescription');
	var isBuildPrivate = getItemFromLS('storedBuildIsPrivate');
	itemSlugs = JSON.parse(getItemFromLS('storedItemSlugs'));
	for(var i = 0; i < championSlugs.length; i++) {
		if (championSlugs[i] != '') {
			$('#champion-isotope-list li.champion#'+championSlugs[i]).addClass('active');
		}
	}
	$itemSidebarList.html('');
	$('div.game-mode').removeClass('active');
	$('div.game-mode[data-game-mode="'+gameMode+'"]').addClass('active');
	
	for(i = 0; i < itemSlugs.length; i++) {
		var block = itemSlugs[i];
		var blockName = block.name;
		var blockNameEscaped = blockName.replace(/ +/g, '_');
		var items = block.items;
		var blockDescription = block.description;
		if ( blockDescription == undefined) {
			blockDescription = '';
		}
		$itemSidebarList.append('<li class="item-sidebar-block-li" id="__'+blockNameEscaped+'__item-block-li" data-description="'+blockDescription+'"><div><input type="text" class="item_sidebar_block_input" value="'+blockName+'"/><a href="#" class="describe-block btn-describe-block-item" id="__'+blockNameEscaped+'__describe-block"><i class="icon-pencil"></i></a><a href="#" class="btn-toggle-block-size"><i class="icon-resize-small"></i></a><a href="#" class="reset-field btn-delete-block-item"><i class="icon-remove"></i></a></div><div class="item-sidebar-block-div"><div class="indication">Faites glissez vos items ici</div></div></li>')
		initItemDroppable($itemSidebarList.find('li:last div.item-sidebar-block-div'));
		
		for (var j = 0; j < items.length; j++) {
			addManyRecItems(items[j]['slug'], '__'+blockNameEscaped + '__item-block-li', items[j]['count']);
		}
	}
	
	$('#build-name').val(buildName);
	$('#build-description').val(buildDescription);
	$('#build-private').prop('checked', ( isBuildPrivate == 'false' ? false : true ) );
	
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
	delete localStorage['storedBuildDescription'];
	delete localStorage['storedBuildIsPrivate'];
}

function storeItemBuild() {
	var championSlugs = new Array();
	$('#champion-isotope-list li.champion.active').each(function(){
		championSlugs.push($(this).attr('id'));
	});

	var gameMode = $('div.game-mode-container div.game-mode.active').first().attr('data-game-mode');

	var itemsSlugs = new Array();
	$itemSidebarList.find('li.item-sidebar-block-li').each(function () {
		var blockName = $(this).find('input.item_sidebar_block_input').val();
		var blockDescription = $.trim($(this).attr('data-description'));//TODO ESCAPE
		
		if(blockName != undefined && blockName != '' && ! (blockName in itemsSlugs)) {
			var blockArray = new Array();
			$that = $(this);
			$(this).find('div.item-sidebar-block-div div.portrait').each(function() {
				var $itemCount = $(this).find('span.item-count');
				var itemCount = 1;
				if ($itemCount.length > 0) {
					itemCount = $itemCount.html() *1;
					itemCount = itemCount >= 1 ? itemCount : 1;
				}
				blockArray.push({slug: $(this).data('slug'), count: itemCount, order: ($($that.find('.portrait')).index($(this)) + 1)});
			});
			if (blockArray.length > 0) {
				itemsSlugs.push({name:blockName, items:blockArray, description: blockDescription});
			}
		}
	});

	var buildName = $('#build-name').val();
	var buildDescription = $('#build-description').val();
	if ( buildDescription == undefined || buildDescription == '' ) {
		buildDescription = '';
	}
	var isBuildPrivate = $('input#build-private').prop('checked');
	
	saveItemInLS('storedItemBuild', 'true');
	saveItemInLS('storedChampionSlugs', championSlugs);
	saveItemInLS('storedGameMode', gameMode);
	saveItemInLS('storedItemSlugs', JSON.stringify(itemsSlugs));
	saveItemInLS('storedBuildName', buildName);
	saveItemInLS('storedBuildDescription', buildDescription);
	saveItemInLS('storedBuildIsPrivate', isBuildPrivate);
}
/*************** FIN LOCAL STORAGE ****************/

function initItemDraggable() {
	$itemIsotopeList.on('mouseover', 'li.item', function() {
		$(this).draggable({
			appendTo: 'div.item-container',
			disabled: false,
			helper: 'clone',
			revert: 'invalid',
			revertduration: 300,
			zIndex: 1100,
			opacity: 1,
			distance: 20,
			start: function() {
				$itemSidebarList.find('li div.item-sidebar-block-div').css('border-color', '#db4a38');
			},
			stop: function() {
				$itemSidebarList.find('li div.item-sidebar-block-div').css('border-color', '#999');
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
		var $parent = $(this).parent();
		
		var $itemCount = $(this).find('span.item-count');
		if ($itemCount.length > 0) {
			var itemCount = $itemCount.html() * 1;
			if (itemCount > 2) {
				$itemCount.html($itemCount.html()*1 -1);
			} else if (itemCount == 2){
				$itemCount.remove();
			}
		} else {
			$(this).popover('destroy');
			$(this).remove();
		}
		
		if($parent.find('div.portrait').length == 0) {
			
			var indication;
			if(locale == 'en') {
				indication = 'Drop your items here';
			} else {
				indication = 'Déposez vos objets ici';
			}
			$parent.html('<div class="indication">'+indication+'</div>');
		}
	});
}

function initItemBlocksSortable() {
	$itemSidebarList.sortable();
}
function initItemPortraitInBlocksSortable() {
	$itemSidebarList.find('div.item-sidebar-block-div').sortable({tolerance: 'intersect', items: '.portrait', cursorAt: {left:15, top: 14}, dropOnEmpty: true});
}

function initItemAddBlock() {
	$('#btn-add-item-block').click(function() {
		var indication,placeholder;
		if(locale == 'en') {
			indication = 'Drop your items here';
			placeholder = 'Block name'
		} else {
			indication = 'Déposez vos objets ici';
			placeholder = 'Nom du bloc'
		}
		$itemSidebarList.append('<li class="item-sidebar-block-li" id="__'+nbBlocks+'__item-block-li"><div><input type="text" placeholder="'+placeholder+'" class="item_sidebar_block_input" value=""/> <a href="#" class="describe-block btn-describe-block-item" id="__'+nbBlocks+'__describe-block"><i class="icon-pencil"></i></a><a href="#" class="btn-toggle-block-size"><i class="icon-resize-small"></i></a><a href="#" class="reset-field btn-delete-block-item"><i class="icon-remove"></i></a></div><div class="item-sidebar-block-div"><div class="indication">'+indication+'</div></div></li>')
		nbBlocks ++;
		$('.item-sidebar-block-li').last().children('input').focus();
		initItemDroppable($itemSidebarList.find('li:last div.item-sidebar-block-div'));
		setTimeout(function(){
				if ($itemSidebarList.children('li.item-sidebar-block-li').length >= 5) {
					$('#item_sidebar_blocks_li').mCustomScrollbar("scrollTo","bottom");
				}
			},
			300
		);
		return false;
	});
}

function initItemRemoveBlock() {
	$itemSidebarList.on('click', '.btn-delete-block-item',function() {
		$(this).parent().parent().remove();
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
		
		var blockNames = new Array();
		$itemSidebarList.find('li.item-sidebar-block-li').each(function() {
			blockNames.push($(this).find('input').val());
		});
		
		$(this).parent().find('.btn-describe-block-item').popover('hide');
		$(this).parent().find('.btn-describe-block-item').removeClass('active');
		
		$(this).focusout(function() {
			var newName = $.trim($(this).val());
			var regex = new RegExp("[^a-zA-Z0-9 ]");
			
			if (oldName == $(this).val()) {
				
			} else if ( ! (newName != '' && !regex.test(newName))) {
				$(this).val(oldName);
				if (locale == 'en') {
					displayMessage('The given name is not valid.', 'error');
				}else {
					displayMessage('Le nom saisi n\'est pas valide.', 'error')
				}
			} else if (blockNames.indexOf($(this).val()) >= 0) {
				$(this).val(oldName);
				if (locale == 'en') {
					displayMessage('This block name is already in use.', 'error');
				}else {
					displayMessage('Le nom saisi est déjà utilisé.', 'error')
				}
			} else {
				newName = newName.replace(/ +/g, ' ');
				oldName = newName;
				$(this).attr('value', newName)
				$(this).val(oldName);
				newName = newName.replace(/ +/g, '_');
				$(this).parent('li').attr('id', '__' + newName + '__item-block-li')
				var $btnDescribe = $(this).parent('li').find('a.btn-describe-block-item');
				
				$btnDescribe.popover('destroy');
				$btnDescribe.attr('id', '__' + newName + '__describe-block')
				$btnDescribe.popover({
					animation: true,
					placement: 'left',
					trigger: 'click',
					title: 'Description du bloc ' + $btnDescribe.parent().find('input').val(),
					template : '<div class="popover bloc-description-popover" data-source="#'+$btnDescribe.parent().attr('id')+'"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><textarea></textarea></div><div class="popover-footer"><a href="#" class="save-bloc-description action">Enregistrer</a><a href="#" class="cancel-save-bloc-description action">Annuler</a></div></div></div>'
				});
				$btnDescribe.data('popover').tip().find('.popover-inner .popover-content textarea').val($btnDescribe.parent().attr('data-description'));
			}
		})
	});
}

function initDescribeBlock() {
	$itemSidebarList.on('mouseover', '.btn-describe-block-item', function() {
		if($(this).data('popover') == undefined) {
			var $this = $(this);
			
			$(this).popover({
				animation: true,
				placement: 'left',
				trigger: 'click',
				title: 'Description du bloc ' + $this.parent().find('input').val(),
				content: '<textarea></textarea>',
				template: '<div class="popover bloc-description-popover" data-source="#'+$this.parents('.item-sidebar-block-li').attr('id')+'"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><textarea></textarea></div><div class="popover-footer"><a href="#" class="save-bloc-description action">Enregistrer</a><a href="#" class="cancel-save-bloc-description action">Annuler</a></div></div></div>'
			});
			
			if ( $(this).parents('.item-sidebar-block-li').data('description') != undefined) {
				$(this).data('popover').tip().find('.popover-inner .popover-content textarea').val($(this).parents('.item-sidebar-block-li').data('description'));
			}
		}
		return false;
	});
	$itemSidebarList.on('click', '.btn-describe-block-item', function() {
		$(this).toggleClass('active');
		return false;
	});
	$('body').on('click', '.save-bloc-description', function() {
		var $popover = $($(this).parents('.popover').data('source')).find('.btn-describe-block-item');
		
		$popover.parents('.item-sidebar-block-li').attr('data-description', $(this).parents('.popover-inner').find('.popover-content').find('textarea').val());
		
		$popover.popover('hide');
		$popover.toggleClass('active');
		return false;
	});
	$('body').on('click', '.cancel-save-bloc-description', function() {
		var $popover = $($(this).parents('.popover').data('source')).find('.btn-describe-block-item');
		
		$(this).parents('.popover-inner').find('.popover-content').find('textarea').val($popover.parents('.item-sidebar-block-li').attr('data-description'));
		
		$popover.popover('hide');
		$popover.toggleClass('active');
		return false;
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
	isBuildSaved = false;
	saveInProgress = false;
	processScrollRecItems();
	
	$window.on('scroll', processScrollRecItems);
	
	//On charge le build depuis le storage s il y en a un
	if (getItemFromLS('storedItemBuild')) {
		initWithStoredItemBuild();
	}
	
	// Écoute sur l'événement d'un clique sur un des modes de jeu
	initGameMode();
	
	//Hover un item
	initPopoverItem($itemIsotopeList);
	
	//Clic sur un item
	initModalItem($itemIsotopeList);
	
	//On rends chaque item draggable
	initItemDraggable();
	
	//On rends la rec item list capable d accepter les items
	initItemDroppable($itemSidebarBlockDivs);
	
	//Lors du clic sur un item présent dans un block
	initItemClickInBlock();
	
	//On active le module sortable pour les blocks d items
	initItemBlocksSortable();
	
	//On active le module sortable pour les items présents dans les blocks
	initItemPortraitInBlocksSortable()
	
	//Lors du clic sur le bouton ajouter un block
	initItemAddBlock();
	
	//Lors du clic sur un bouton de suppression de block
	initItemRemoveBlock();
	
	//Lors du clic sur un champion
	initChampionClick();
	
	//Lors de la prise de focus par les input des block dédiés a l edition des noms des bocks
	initBlockInputName();
	
	//Lors du clic sur le bouton de description d un bloc
	initDescribeBlock();
	
	nbBlocks = $itemSidebarList.children('.item-sidebar-block-li').length + 1;
	
	//Bouton de generation du build uniquement
	$('#only-generate-build, #modal-btn-only-generate-build').click(function(e){
		e.preventDefault();
		if(isBuildValid()) {
			$('#modal-authenticate-build').modal('hide');
			$('#modal-btn-download').attr('data-save-build', 'false');
			$('#modal-dl-build').modal('show');
		}
	});
	$('#save-and-generate-build').click(function(e) {
		e.preventDefault();
		generateRecItemBuilder(true);
	});
	$('#save-and-generate-build-not-authenticated').click(function(e) {
		e.preventDefault();
		if(isBuildValid()) {
			$('#modal-authenticate-build').modal('show');
		}
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
	
	//CustomScrollBar
	$itemSidebarList.parent('li').mCustomScrollbar({
		advanced:{
			updateOnContentResize: true
		}
	});
	
	initPopoverItem($('#item_sidebar_blocks_list'));

	$('li#item_sidebar_blocks_li').on('click', 'a.btn-toggle-block-size', function(event)
	{
		event.preventDefault();
		$(this).parent().parent().find('div.item-sidebar-block-div').slideToggle();
		$(this).find('i').toggleClass('icon-resize-small icon-resize-full');
	});
});