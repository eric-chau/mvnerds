$(document).ready(function() {
	
	var 	$usernameFilterInput = $('input#username-filter-input');
	
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
	
	membersTable = $('#users-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('users_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 12,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'avatar'},//Avatar
                      {"bSearchable": false, "bSortable":false, 'sClass': 'username'},//Pseudo
                      {"bSearchable": false, "bSortable":false, 'sClass': 'game-account'},//Date inscription
		    {"bVisible": false, "bSearchable": true, "bSortable":true},//Pseudo
                      {"bVisible": false, "bSearchable": false, "bSortable":true},//create_time
		],
		"sPaginationType": 'full_numbers',
		"oLanguage": langage
	});
	
	var sortTable = [
		[3, 'asc'],//Les + anciens
		[3, 'desc'],//Les + récents
		[1, 'asc'],//Pseudo croissant
		[1, 'desc'],//Pseudo décroissant
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		membersTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$usernameFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			membersTable.fnFilter($(this).val(), 3);
		}
	});
	
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($usernameFilterInput.val() != '') {
			membersTable.fnFilter($usernameFilterInput.val(), 3);
		}
	});
	
	$('#item-builds-table_length').addClass('pull-left');
	$('#item-builds-table_filter').addClass('pull-right');
	
	//CHAMP DE RECHERCHE
	$('#item-builds-table_filter label').addClass('search-box');
});