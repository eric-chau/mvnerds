//Permet de trier les champions en cours de comparaison selon une colonne

var SORT_DESC = 'desc',
		SORT_ASC  = 'asc';

var $championComparator = $('#champion-comparison'),
	$championHeader = $('#champion-comparator div#compare-champion-div-header div.table-header'),
	$championList = $('#champion-comparator div.champion-list');

//Permet de trier la liste de comparaison en fonction d'une colonne et d'un ordre
//Par défaut l'ordre de tri est l'ordre décroissant
function sortByColumn(column, order){
	var sortValue,
		sortSlug,
		$filterHeader,
		sortArray = new Array();
	
	order = typeof order !== 'undefined' ? order : SORT_DESC;

	//On parcours tous les champions en récupérant la valeur de leur colonne dans un tableau
	$('div[data-filter="'+column+'"]').each(function(){
		sortValue = $(this).data('value');
		sortSlug = $(this).parent('div.champion-row').data('slug');
		sortArray.push({
			'slug'	: sortSlug,
			'value'	: sortValue,
			'dom'	: $(this).parent('div.champion-row')
		});
	});

	sortArray.sort(function(a, b){
		return (column == 'champion-name') ? (a.value.localeCompare(b.value)) : (parseFloat(a.value) -  parseFloat(b.value));
	});

	$filterHeader = $championHeader.find('div[data-target="'+column+'"]');

	if(order == SORT_DESC)
	{
		sortArray.reverse();
		$filterHeader.addClass('filter-desc');
		$filterHeader.removeClass('filter-asc');
		$filterHeader.find('i').removeClass('icon-sort-up');
		$filterHeader.find('i').addClass('icon-sort-down');
	}
	else
	{
		$filterHeader.addClass('filter-asc')
		$filterHeader.removeClass('filter-desc');
		$filterHeader.find('i').removeClass('icon-sort-down');
		$filterHeader.find('i').addClass('icon-sort-up');
	}

	$championList.html('');
	$.each(sortArray, function(index, item){
		$championList.append(item.dom);
	});

}

jQuery(function(){
	//Lors du click sur un header de stat
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort:not(.filter-asc):not(.filter-desc)', function(){
		$(this).find('i').removeClass('icon-sort');
		sortByColumn($(this).data('target'));
	});
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort.filter-asc', function(){
		sortByColumn($(this).data('target'), SORT_DESC);
	});
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort.filter-desc', function(){
		sortByColumn($(this).data('target'), SORT_ASC);
	});

});