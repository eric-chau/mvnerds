$(document).ready(function() {
	var 	$titleFilterInput = $('input#title-filter-input');
	var 	$authorFilterInput = $('input#author-filter-input');
	var 	$categoryFilterInput = $('#category-filter-input');
	
	var langage;
	if(locale != undefined && locale == 'en') {
		langage = {
			"sProcessing":     "<i class='icon-spinner icon-spin'></i> Loading...",
			"sLengthMenu":     "Show _MENU_ news per page",
			"sZeroRecords":    "There is no news to show",
			"sInfo":           "Showing news from _START_ to _END_ on a total of _TOTAL_ news",
			"sInfoEmpty":      "Empty list",
			"sInfoFiltered":   "(filtered from a total of _MAX_ news)",
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
			"sLengthMenu":     "Afficher _MENU_ news par page",
			"sZeroRecords":    "Aucune news à afficher",
			"sInfo":           "Affichage des news de _START_ à _END_ sur un total de _TOTAL_ news",
			"sInfoEmpty":      "Liste vide",
			"sInfoFiltered":   "(filtré de _MAX_ news au total)",
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
	
	newsTable = $('#news-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('news_list_ajax', {_locale: locale}),
		"bLengthChange": false,
		"aaSorting": [],
		"iDisplayLength": 10,
		"sDom": "lrtip",
		"aoColumns": [
                      {"bSearchable": false, "bSortable":false, 'sClass': 'preview'},//Aperçu
                      {"bSearchable": false, "bSortable":true, 'sClass': 'title'},//Titre + stats + auteur
                      {"bSearchable": false, "bSortable":true, 'sClass': 'category'},//Catégorie xlf
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
		[9, 'desc'],//Les + commentées
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		newsTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$titleFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			newsTable.fnFilter($(this).val(), 6);
		}
	});
	$authorFilterInput.on('keyup change', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			newsTable.fnFilter($(this).val(), 3);
		}
	});
	$categoryFilterInput.on('change', function(e){
			newsTable.fnFilter($(this).val(), 8);
	});
	$('#search-filter-btn').on('click', function(e){
		e.preventDefault();
		if ($titleFilterInput.val() != '') {
			newsTable.fnFilter($titleFilterInput.val(), 6);
		}
		if ($authorFilterInput.val() != '') {
			newsTable.fnFilter($authorFilterInput.val(), 3);
		}
	});
	
	$('#item-builds-table_length').addClass('pull-left');
	$('#item-builds-table_filter').addClass('pull-right');
	
	//CHAMP DE RECHERCHE
	$('#item-builds-table_filter label').addClass('search-box');
	
	orderCategories($('#category-filter-input'));
});