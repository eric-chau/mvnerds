var filter, itemBuildsTable;

$(document).ready(function() {
	
	filter = $('#item-builds-table').data('filter');
	
	var 	$titleFilterInput = $('input#title-filter-input');
	
	var langage;
	if(locale != undefined && locale == 'en') {
		langage = {
			"sProcessing":     "<i class='icon-spinner icon-spin'></i> Loading...",
			"sLengthMenu":     "Show _MENU_ videos per page",
			"sZeroRecords":    "There is no videos to show",
			"sInfo":           "Showing videos from _START_ to _END_ on a total of _TOTAL_ videos",
			"sInfoEmpty":      "Empty list",
			"sInfoFiltered":   "(filtered from a total of _MAX_ videos)",
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
			"sLengthMenu":     "Afficher _MENU_ vidéos par page",
			"sZeroRecords":    "Aucune vidéo à afficher",
			"sInfo":           "Affichage des vidéos de _START_ à _END_ sur un total de _TOTAL_ vidéos",
			"sInfoEmpty":      "Liste vide",
			"sInfoFiltered":   "(filtré de _MAX_ vidéos au total)",
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
	
	itemBuildsTable = $('#videos-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('videos_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 12,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'preview'},//Aperçu
                      {"bSearchable": false, "bSortable":true, 'sClass': 'title'},//Titre + stats + auteur
                      {"bSearchable": false, "bSortable":true},//Catégorie
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//Auteur
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Date de publication
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Comment count
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//Titre
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//Vues
		],
		"sPaginationType": 'full_numbers',
		"oLanguage": langage
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
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		itemBuildsTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$('#title-filter-input').on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			itemBuildsTable.fnFilter($(this).val(), 9);
		}
	});
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($('#title-filter-input').val() != '') {
			itemBuildsTable.fnFilter($('#title-filter-input').val(), 9);
		}
	});
	
	$('#item-builds-table_length').addClass('pull-left');
	$('#item-builds-table_filter').addClass('pull-right');
	
	//CHAMP DE RECHERCHE
	$('#item-builds-table_filter label').addClass('search-box');

	// Vérifie si c'est la première fois ou non que l'utilisateur accède au module PMRI
	var howItWorksValue = getItemFromLS('display_how_it_works_videos');
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
		saveItemInLS('display_how_it_works_videos', $label.hasClass('disabled'));
	});
});