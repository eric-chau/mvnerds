$(function() {
	initPopoverItem($('div.item-container'));
	
	$('li.champion').tooltip();
	
	$('a.download-action.start-dl').click();
});