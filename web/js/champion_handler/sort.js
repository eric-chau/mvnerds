//Permet de trier les champions en cours de comparaison selon une colonne

var SORT_DESC = 'desc',
		SORT_ASC  = 'asc';

var $championComparator = $('#champion-comparison'),
	$championHeader,
	$championList;

//Permet de trier la liste de comparaison en fonction d'une colonne et d'un ordre
//Par défaut l'ordre de tri est l'ordre décroissant
function sortByColumn(column, order){
	var sortValue,
		sortSlug,
		$sortHeader,
		sortArray = new Array();

	$championHeader = $('#champion-comparator div#compare-champion-div-header div.table-header');
	$championList = $('#champion-comparator div.champion-list');
	
	order = typeof order !== 'undefined' ? order : SORT_DESC;

	//On parcours tous les champions en récupérant la valeur de leur colonne dans un tableau
	$('div[data-sort="'+column+'"]').each(function(){
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

	$sortHeader = $championHeader.find('div[data-target="'+column+'"]');

	$championHeader.find('div.sort').removeClass('sort-desc sort-asc');
	$championHeader.find('div.sort i').attr('class',  'icon-sort icon-white');

	if(order == SORT_DESC)
	{
		sortArray.reverse();
		$sortHeader.addClass('sort-desc');
		$sortHeader.find('i').attr('class', 'icon-sort-down icon-white');
	}
	else
	{
		$sortHeader.addClass('sort-asc')
		$sortHeader.find('i').attr('class', 'icon-sort-up icon-white');
	}

	$championList.html('');
	$.each(sortArray, function(index, item){
		$championList.append(item.dom);
	});

}

jQuery(function(){
	//Lors du click sur un header de stat
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort:not(.sort-asc):not(.sort-desc)', function(){console.log('sort');
		$(this).find('i').removeClass('icon-sort');
		sortByColumn($(this).data('target'));
	});
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort.sort-asc', function(){
		sortByColumn($(this).data('target'), SORT_DESC);
	});
	$championComparator.on('click', 'div#compare-champion-div-header div.table-header div.sort.sort-desc', function(){
		sortByColumn($(this).data('target'), SORT_ASC);
	});

});