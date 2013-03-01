$(document).ready(function() {
	
	var 	$usernameFilterInput = $('input#title-filter-input');
	
	var langage;
	if(locale != undefined && locale == 'en') {
		langage = {
			"sProcessing":     "<i class='icon-spinner icon-spin'></i> Loading...",
			"sLengthMenu":     "Show _MENU_ members per page",
			"sZeroRecords":    "There is no members to show",
			"sInfo":           "Showing members from _START_ to _END_ on a total of _TOTAL_ members",
			"sInfoEmpty":      "Empty list",
			"sInfoFiltered":   "(filtered from a total of _MAX_ members)",
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
			"sLengthMenu":     "Afficher _MENU_ membres par page",
			"sZeroRecords":    "Aucun membre à afficher",
			"sInfo":           "Affichage des membres de _START_ à _END_ sur un total de _TOTAL_ membres",
			"sInfoEmpty":      "Liste vide",
			"sInfoFiltered":   "(filtré de _MAX_ membres au total)",
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
	
	membersTable = $('#videos-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('videos_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 10,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'preview'},//Avatar
                      {"bSearchable": false, "bSortable":true, 'sClass': 'title'},//Pseudo + stats
                      {"bSearchable": false, "bSortable":true, 'sClass': 'category'},//Catégorie xlf
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//username
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//create_time
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//update_time
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//title
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//view
                      {"bVisible": false, "bSearchable": true, "bSortable":true},//category
		    {"bVisible": false, "bSearchable": false, "bSortable":true},//comment_count
		    {"bVisible": false, "bSearchable": false, "bSortable":true},//rating
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
		[9, 'desc'],//Les + commentées
		[10, 'desc'],//Les mieux notées
		[10, 'asc']//Les moins bien notées
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		membersTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$titleFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			membersTable.fnFilter($(this).val(), 6);
		}
	});
	$authorFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			membersTable.fnFilter($(this).val(), 3);
		}
	});
	$categoryFilterInput.on('change', function(e){
			membersTable.fnFilter($(this).val(), 8);
	});
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($titleFilterInput.val() != '') {
			membersTable.fnFilter($titleFilterInput.val(), 6);
		}
		if ($authorFilterInput.val() != '') {
			membersTable.fnFilter($authorFilterInput.val(), 3);
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