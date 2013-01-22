function initPopoverItem($container) {
	$container.on('hover', '.item', function(e) {
		if($(this).data('popover') == undefined) {
			var title = "<img class='tooltip-item-img' src='/images/items/" + $(this).data('code') + ".png'/>" + $(this).data('title');
			
			$(this).popover({
				trigger: 'hover',
				placement: 'bottom',
				delay: {show: 0, hide: 0}
			});
			$(this).data('popover').options.title = title;
			$(this).data('popover').options.placement = 'right';
			$(this).data('popover').options.animation = false;
			$(this).data('popover').options.template = '<div class="popover item-popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
			$(this).popover('show');
			setItemPopoverContent($(this).data('slug'), $(this));
		}
	});
}
function setItemPopoverContent(slug, $item) {
	try {
		var item = getItemForModal(slug);
		var data = '';
		for (var i = 0; i < item.primaryEffects.length; i++) {
			data += item.primaryEffects[i] + '<br />';
		}
		if (item.primaryEffects.length > 0 && item.secondaryEffects.length > 0) {
			data += '<br />';
		}
		for (var j = 0; j < item.secondaryEffects.length; j++) {
			data += item.secondaryEffects[j] + '<br />';
		}	
		var cost = '';
		if (locale == 'en') {
			cost = 'Cost';
		} else {
			cost = 'Coût';
		}
		data += '<br />' + cost + ' : ' + item.totalCost + ' (' + item.cost + ')';
		//$wrapper = $('<div>');
		//$wrapper.append($item.data('popover').options.template).find('.popover-content').html(data);
		//$item.data('popover').options.template = $wrapper.html();
		$item.data('popover').$tip.find(".popover-content").html(data);
		console.log($item.data('popover'.$tip.find('.item-popover')));
		$item.data('ajax-loaded', true);
	} catch (err) {
		if (locale == 'en') {
			displayMessage('Unable to show the item\'s detail.', 'error');
		} else {
			displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
		}
	}
}