var filter, itemBuildsTable;

$(document).ready(function() {
	
	filter = $('#item-builds-table').data('filter');
	
	var $championFilterInput = $('input#champion-filter-input'),
		$authorFilterInput = $('input#author-filter-input'),
		$titleFilterInput = $('input#title-filter-input'),
		$moreFilterIcon = $('li.more-filters-button').find('i');
	
	var langage;
	if(locale != undefined && locale == 'en') {
		langage = {
			"sProcessing":     "<i class='icon-spinner icon-spin'></i> Loading...",
			"sLengthMenu":     "Show _MENU_ lists per page",
			"sZeroRecords":    "There is no lists to show",
			"sInfo":           "Showing lists from _START_ to _END_ on a total of _TOTAL_ lists",
			"sInfoEmpty":      "Empty list",
			"sInfoFiltered":   "(filtered from a total of _MAX_ lists)",
			"sInfoPostFix":    "",
			"sSearch":         "<i class='icon-search icon-white'></i>",
			"sLoadingRecords": "<img src='/images/commons/loader-16-bg-gray.gif' alt='chargement'/> Loading...",
			"sUrl":            "",
			"oPaginate": {
				"sFirst":    "First",
				"sPrevious": "Previous",
				"sNext":     "Next",
				"sLast":     "Last"
			}
		};
	} else {
		langage = {
			"sProcessing":     "<i class='icon-spinner icon-spin'></i> Chargement...",
			"sLengthMenu":     "Afficher _MENU_ builds par page",
			"sZeroRecords":    "Aucun build à afficher",
			"sInfo":           "Affichage des builds de _START_ à _END_ sur un total de _TOTAL_ builds",
			"sInfoEmpty":      "Affichage du build 0 à 0 sur 0 builds",
			"sInfoFiltered":   "(filtré de _MAX_ builds au total)",
			"sInfoPostFix":    "",
			"sSearch":         "<i class='icon-search icon-white'></i>",
			"sLoadingRecords": "<img src='/images/commons/loader-16-bg-gray.gif' alt='chargement'/> Chargement...",
			"sUrl":            "",
			"oPaginate": {
				"sFirst":    "Premier",
				"sPrevious": "Précédent",
				"sNext":     "Suivant",
				"sLast":     "Dernier"
			}
		};
	}
	
	
	
	itemBuildsTable = $('#item-builds-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('item_builder_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 12,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'champion'},//Champions
                      {"bSearchable": false, "bSortable":true, 'sClass': 'name'},//Nom du build
                      {"bSearchable": false, "bSortable":false},//Mode de jeu
                      {"bVisible": false, "bSearchable": true, "bSortable":false},//Champions
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Downloads
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Update Time
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//Auteur
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Date de creation
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Comment count
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//Build Name
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Views
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Rating
		],
		"aoSearchCols": [
			null,
			null,
			null,
			null,
			{'sSearch' : defaultSearch}
		],
		"sPaginationType": 'full_numbers',
		"oLanguage": langage,
		"fnInitComplete": function() {
			$('#item-builds-table_wrapper').on('mouseenter', '.bootstrap-popover', function(){
				$(this).popover('show');
			});
			$('#item-builds-table_wrapper').on('mouseleave', '.bootstrap-popover', function(){
				$(this).popover('hide');
			});
		}
	});
	
	var sortTable = [
		[9, 'asc'],//Nom croissant
		[9, 'desc'],//Nom décroissant
		[4, 'desc'],//Les + DL
		[4, 'asc'],//Les - DL
		[7, 'asc'],//Les + anciens
		[7, 'desc'],//Les + récents
		[5, 'desc'],//Les dernières MAJ
		[8, 'desc'],//Les + commentées
		[10, 'desc'],//Les + vues
		[11, 'desc'],//Les mieux notées
		[11, 'asc']//Les moins bien notées
	];
	
	itemBuildsTable.fnSort([sortTable[2]]);
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		itemBuildsTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	itemBuildsTable.fnFilter(filter, 3);
	
	$('#author-filter-input').on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			itemBuildsTable.fnFilter($(this).val(), 6);
		}
	});
	$('#title-filter-input').on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			itemBuildsTable.fnFilter($(this).val(), 9);
		}
	});
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($('#champion-filter-input').val() != '') {
			itemBuildsTable.fnFilter($('#champion-filter-input').val(), 3);
		}
		if ($('#author-filter-input').val() != '') {
			itemBuildsTable.fnFilter($('#author-filter-input').val(), 6);
		}
		if ($('#title-filter-input').val() != '') {
			itemBuildsTable.fnFilter($('#title-filter-input').val(), 9);
		}
	});
	
	$('#item-builds-table_length').addClass('pull-left');
	$('#item-builds-table_filter').addClass('pull-right');
	
	$('#item-builds-table tbody tr.item_build_row').click(function() {
		window.location = Routing.generate('item_builder_builds', {_locale: locale, itemBuildSlug: $(this).data('target')});
	});
	
	//CHAMP DE RECHERCHE
	$('#item-builds-table_filter label').addClass('search-box');
	$('#item-builds-table_filter label input').attr('data-provide', 'typeahead');
	
	if(locale != undefined && locale == 'en') {
		$('#item-builds-table_filter label input').attr('placeholder', 'Search by champion');
	} else {
		$('#item-builds-table_filter label input').attr('placeholder', 'Rechercher par champion');
	}
	
	//Auto complete champions
	$.ajax({
		type: 'POST',
		url:  Routing.generate('champions_names', {_locale: locale}), 
		dataType: 'html'
	}).done(function(data){
		$('#champion-filter-input').attr('data-source', data);
	}).fail(function() {
		console.log('error');
	});

	// Vérifie si c'est la première fois ou non que l'utilisateur accède au module PMRI
	var howItWorksValue = getItemFromLS('display_how_it_works');
	console.log(howItWorksValue);
	if (howItWorksValue == undefined || howItWorksValue == 'true') {
		$('a.how-it-works-toggle').find('span.label').toggleClass('disabled');
		$('div.how-it-works').slideDown();

		if (howItWorksValue == undefined) {
			saveItemInLS('display_how_it_works', false);
		}
	}

	// Toggle du "comment ça marche ?"
	$('a.how-it-works-toggle').on('click', function(event)
	{
		event.stopPropagation();
		$('div.how-it-works').slideToggle();
		$label = $(this).find('span.label');
		$label.toggleClass('disabled');
		saveItemInLS('display_how_it_works', $label.hasClass('disabled'));
	});
});