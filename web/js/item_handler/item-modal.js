var 
	itemModalArray = new Array(),
	$itemModal;

function initModalItem($container) {
	$container.on('click', '.item', function(){
		var slug = $(this).attr('id');
		getItemForModal(slug);
	});
}

function getItemForModal(slug) {
	if (itemModalArray[slug] == undefined) {
		$.ajax({
			type: 'POST',
			url:  Routing.generate('item_builder_get_item_modal_content', {_locale: locale}),
			data: {slug: slug},
			dataType: 'json'
		}).done(function(item){
			if (item.slug != undefined) {
				itemModalArray[slug] = item;
				setItemModalContent(item);
			} else {
				if (locale == 'en') {
				displayMessage('Unable to show the item\'s detail.', 'error');
				} else {
					displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
				}
			}
		}).fail(function() {
			if (locale == 'en') {
			displayMessage('Unable to show the item\'s detail.', 'error');
			} else {
				displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
			}
		});
	} else {
		setItemModalContent(itemModalArray[slug]);
	}
}

function setItemModalContent(item) {

	console.log(item);

	if (item != undefined) {
		$itemModal.find('.modal-body .item-modal-geneology').html(item.slug);
		$itemModal.find('.modal-body .item-modal-detail item-modal-header item-modal-header-image img').attr('src', '/images/items/' + item.code + 'png');
		$itemModal.find('.modal-body .item-modal-detail item-modal-header item-modal-header-data item-modal-header-name').html(item.name);
		$itemModal.find('.modal-body .item-modal-detail item-modal-header item-modal-header-data item-modal-header-cost-value').html(item.totalCost + ' ('+item.cost+')');
		$itemModal.modal('show');
	} else {
		if (locale == 'en') {
			displayMessage('Unable to show the item\'s detail.', 'error');
		} else {
			displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
		}
	}
}

$(function() {
	$itemModal = $('div#item-modal');
	$itemModal.modal('hide');
});