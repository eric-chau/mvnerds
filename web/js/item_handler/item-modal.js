var 
	itemModalArray = new Array(),
	$itemModal;

function initModalItem($container) {
	$container.on('click', '.item', function(){
		var slug = $(this).attr('id');
		try {
			var item = getItemForModal(slug);
			setItemModalContent(item);
		} catch (err) {
			console.log('error : ' + err);
		}
	});
	$itemModal.on('click', '.item-parent, .item-geneology', function() {
		try {
			var item = getItemForModal($(this).attr('data-slug'));
			setItemModalContent(item);
		} catch (err) {
			console.log('error : ' + err);
		}
	});
	$('.item-modal-detail-body').mCustomScrollbar({
		advanced:{
			updateOnContentResize: true
		}
	});
}
function initItemModalArray() {
	$.ajax({
		type: 'POST',
		url:  Routing.generate('item_builder_init_item_modal_array', {_locale: locale}),
		dataType: 'json',
		success: function(array){
			if (array != undefined) {
				itemModalArray = array;
			} else {
				throw "Unable to load items details";
			}
		},
		error: function() {
			throw "Unable to load items details";
		}
	});
}

function getItemForModal(slug) {
	if (itemModalArray[slug] == undefined) {
		$.ajax({
			type: 'POST',
			url:  Routing.generate('item_builder_get_item_modal_content', {_locale: locale}),
			data: {slug: slug},
			dataType: 'json',
			async: false,
			success: function(item){
				if (item.slug != undefined) {
					itemModalArray[slug] = item;
				} else {
					throw "Unable to access item detail";
				}
			},
			error: function() {
				throw "Unable to access item detail";
			}
		});
	}
	return itemModalArray[slug];
}

function setItemModalContent(item) {
	cleanItemModalContent();
	if (item != undefined) {
		$itemModal.modal('show');
		
		var parentsLength = item.parents.length;
		for (var i = 0; i < parentsLength; i++) {
			var parent = getItemForModal(item.parents[i]);
			$itemModal.find('.modal-body .item-modal-parents .item-modal-parent-list').append(
				'<li class="item-parent" data-slug="'+ parent.slug +'"><img class="item-parent-img" src="/images/items/' + parent.code + '.png"/></li>'
			);
		}
		
		var $geneology = showGeneology(item.slug);
		
		$itemModal.find('.modal-body .item-modal-geneology').html($geneology);
		
		$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-image img').attr('src', '/images/items/' + item.code + '.png');
		$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-data .item-modal-header-name').html(item.name);
		$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-data .item-modal-header-cost-value').html(item.totalCost + ' ('+item.cost+')');
		
		var primaryEffectsLength = item.primaryEffects.length;
		for (var j = 0; j < primaryEffectsLength; j++) {
			$itemModal.find('.modal-body .item-modal-detail .item-modal-primary-effects').append(
				'<li class="item-primary-effect">'+ item.primaryEffects[j] +'</li>'
			);
		}
		
		var secondaryEffectsLength = item.secondaryEffects.length;
		for (var k = 0; k < secondaryEffectsLength; k++) {
			$itemModal.find('.modal-body .item-modal-detail .item-modal-secondary-effects').append(
				'<li class="item-secondary-effect">'+ item.secondaryEffects[k] +'</li>'
			);
		}
	} else {
		if (locale == 'en') {
			displayMessage('Unable to show the item\'s detail.', 'error');
		} else {
			displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
		}
	}
}

function showGeneology(slug, isRoot) {
	isRoot = isRoot != undefined ? isRoot : true;
	var item = getItemForModal(slug);
	var returnValue = '';
	if (isRoot) {
		returnValue = '<div class="item-geneology" data-slug="'+item.slug+'"><img src="/images/items/'+item.code+'.png" />';
		if (item.children.length > 1) {
			var largeurBrancheRacine = 100 - (item.children.length > 0 ? (100 / item.children.length) : 100);
			returnValue += '<div class="branche verticale"></div><div class="branche horizontale" style="width: '+largeurBrancheRacine+'%"></div>'
		} else if (item.children.length > 0) {
			returnValue += '<div class="branche verticale"></div>'
		}
		returnValue += '</div>';
	} else if (item.children.length == 0) {
		return '<div class="branche verticale"></div><img src="/images/items/'+item.code+'.png" />';
	} else if (item.children.length == 1) {
		returnValue +=  '<div class="item-geneology" data-slug="'+item.slug+'" style="width: 100%"><div class="branche verticale"></div><img src="/images/items/'+item.code+'.png" /><div class="branche verticale"></div></div>';
	} else {
		var largeurBranche = 100 - (item.children.length > 0 ? (100 / item.children.length) : 100);
		returnValue +=  '<div class="item-geneology" data-slug="'+item.slug+'" style="width: 100%"><div class="branche verticale"></div><img src="/images/items/'+item.code+'.png" /><div class="branche verticale"></div><div class="branche horizontale" style="width:'+ largeurBranche +'%"></div></div>';
	}
	var diviseur = item.children.length > 0 ? (100 / item.children.length) : 100;
	
	for (var i = 0; i < item.children.length; i++) {
		returnValue += '<div class="item-geneology" data-slug="'+item.children[i]+'" style="width:'+ diviseur +'%">' + showGeneology(item.children[i], false) + '</div>';
	}
	return returnValue;
}

function cleanItemModalContent() {
	$itemModal.find('.modal-body .item-modal-parents .item-modal-parent-list').html('');
	$itemModal.find('.modal-body .item-modal-geneology').html('');
	$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-image img').attr('src', '');
	$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-data .item-modal-header-name').html('');
	$itemModal.find('.modal-body .item-modal-detail .item-modal-header .item-modal-header-data .item-modal-header-cost-value').html('');
	$itemModal.find('.modal-body .item-modal-detail .item-modal-primary-effects').html('');
	$itemModal.find('.modal-body .item-modal-detail .item-modal-secondary-effects').html('');
}

$(function() {
	try {
		initItemModalArray();
	} catch(err) {
		if (locale == 'en') {
			displayMessage('Unable to load item\'s details.', 'error');
		} else {
			displayMessage('Impossible de charger le détail des objets.', 'error');
		}
	}
	$itemModal = $('div#item-modal');
	$itemModal.modal('hide');
});