var filter, itemBuildsTable;

$(document).ready(function() {
	
	filter = $('#item-builds-table').data('filter');
	
	var 	$titleFilterInput = $('input#title-filter-input');
	var 	$categoryFilterInput = $('#category-filter-input');
	
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
	
	videosTable = $('#videos-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('videos_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 3,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'preview'},//Aperçu
                      {"bSearchable": false, "bSortable":true, 'sClass': 'title'},//Titre + stats + auteur
                      {"bSearchable": false, "bSortable":true},//Catégorie xlf
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//username
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//create_time
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//update_time
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//title
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//view
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//category
		    {"bVisible": false, "bSearchable": false, "bSortable":true},//comment_count
		],
		"sPaginationType": 'full_numbers',
		"oLanguage": langage
	});
	
	var sortTable = [
		[4, 'asc'],//Les + anciens
		[4, 'desc'],//Les + récents
		[5, 'desc'],//Les dernières MAJ
		[6, 'asc'],//Titre croissant
		[6, 'desc'],//Titre décroissant
		[7, 'desc'],//Les + vues
		[9, 'desc']//Les + commentées
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		videosTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$titleFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			videosTable.fnFilter($(this).val(), 6);
		}
	});
	$categoryFilterInput.on('change', function(e){
			videosTable.fnFilter($(this).val(), 8);
	});
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($titleFilterInput.val() != '') {
			videosTable.fnFilter($titleFilterInput.val(), 6);
		}
	});
	
	$('#item-builds-table_length').addClass('pull-left');
	$('#item-builds-table_filter').addClass('pull-right');
	
	//CHAMP DE RECHERCHE
	$('#item-builds-table_filter label').addClass('search-box');

	// Vérifie si c'est la première fois ou non que l'utilisateur accède au module Video
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