var	
	$window,
	$itemIsotopeList,
	itemIsotopeOptions,
	itemIsotopeFilters,
	itemTypeaheadValue, 
	$itemFilterInput,
	itemPopover
;

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

//Lors du clic sur un objet
function initItemClick() {
	//On redirige sur la page dédiée
	$itemIsotopeList.find('li.item').click(function() {
		location.href = Routing.generate('items_detail', {_locale: locale, slug: $(this).data('slug')});
	});
}

$(document).ready(function()
{
	$window = $(window);
	$recItemList = $('#item-topbar');
	$itemIsotopeList = $('#item-isotope-list');
	recItemsTop = $recItemList.length && $recItemList.offset().top;
	
	//Hover un item
	initPopoverItem($itemIsotopeList);
	
	//Click sur un item
	initItemClick();
	
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
		filter: '.classic, .dominion, .aram, .twisted-treeline, .shared',
		containerStyle: {
			position: 'relative',
			overflow: 'visible'
		}
	};
	itemIsotopeFilters = {
		tags : [],
		name: ''
	};
	
	$itemIsotopeList.imagesLoaded( function(){
		$itemIsotopeList.isotope(itemIsotopeOptions);
	});
	
	//Activation des filtres
	$itemFilterInput = $('#item-filter-input');
	//On set les options sur l objet isotope pour y acceder plus facilement
	$itemIsotopeList.options = itemIsotopeOptions;
	$itemIsotopeList.filters = itemIsotopeFilters;
	$itemIsotopeList.initTypeahead($itemFilterInput, Routing.generate('item_builder_get_items_name',{_locale: locale}), $('li#item-li-clean-filter'));
	initItemFilterList($itemIsotopeList, $('ul#item-filters-list li'));
	initItemCleanAction($itemIsotopeList, $itemFilterInput);

});