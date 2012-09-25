function paginateContent(link){
	var container = $('#champion-comparison');	

	var href = link.href;
	
	var pos = link.rel == 'next' ? '-150%' : '150%';
	
	container.find('div.data-scrollable').animate({
		left: pos,
		opacity: 0
	}, 'slow', function(){
		$.get(
			href,
			{format: 'html' },
			function(data){
				container.html(data);
				 $('.data-pagination').off('click');
				 $('.data-pagination').on('click', function(){
					paginateContent(this);
					return false;
				});
			},
			'html'
		);
	});
	return false;
}

jQuery(function(){
	 $('.data-pagination').on('click', function(){
		paginateContent(this);
		return false;
	});
});