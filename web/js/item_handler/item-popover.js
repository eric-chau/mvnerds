function initPopoverItem($container) {
	var popoverTimer;
	$container.find('li.item').hover(function(e) {
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