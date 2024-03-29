function initPopoverItem($container) {
	$container.on('hover', '.item', function(e) {
		if($(this).data('popover') == undefined) {
			var title = "<img class='tooltip-item-img' src='/medias/images/items/" + $(this).data('code') + ".png'/>" + $(this).data('title');
						
			$(this).popover({
				trigger: 'hover',
				placement: 'bottom',
				delay: {show: 0, hide: 0}
			});
			$(this).data('popover').options.title = title;
			$(this).data('popover').options.placement = 'bottom';
			$(this).data('popover').options.animation = false;
			$(this).data('popover').options.template = '<div class="popover item-popover"><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>';
			
			try {
				var item = getItemForModal($(this).data('slug'));
				
				var data = '';
				for (var i = 0; i < item.primaryEffects.length; i++) {
					data += '<span class="primary-effect">' + item.primaryEffects[i] + '<br /></span>';
				}
				if (item.primaryEffects.length > 0 && item.secondaryEffects.length > 0) {
					data += '<br />';
				}
				for (var j = 0; j < item.secondaryEffects.length; j++) {
					var strArray = item.secondaryEffects[j].split(':');
					data += '<strong>' + strArray[0] + ('1' in strArray? ':</strong>' + strArray[1] : '</strong>') + '<br />';
				}	
				var cost = '<span class="cost">';
				if (locale == 'en') {
					cost += 'Cost';
				} else {
					cost += 'Coût';
				}
				data += '<br />' + cost + ' : ' + item.totalCost + ' (' + item.cost + ')</span>';
				
				$(this).data('popover').options.content = data;
			} catch (err) {
				if (locale == 'en') {
					displayMessage('Unable to show the item\'s detail.', 'error');
				} else {
					displayMessage('Impossible d\'accéder au détail de l\'objet.', 'error');
				}
			}
			
			$(this).popover('show');
		}
	});
}