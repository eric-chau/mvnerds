var filter, itemBuildsTable;

/***************************** BOOTSTRAP PAGINATION ********************************/
/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
{
    return {
        "iStart":         oSettings._iDisplayStart,
        "iEnd":           oSettings.fnDisplayEnd(),
        "iLength":        oSettings._iDisplayLength,
        "iTotal":         oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage":          Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
        "iTotalPages":    Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
    };
}
 
/* Bootstrap style pagination control */
$.extend( $.fn.dataTableExt.oPagination, {
    "bootstrap": {
        "fnInit": function( oSettings, nPaging, fnDraw ) {
            var oLang = oSettings.oLanguage.oPaginate;
            var fnClickHandler = function ( e ) {
                e.preventDefault();
                if ( oSettings.oApi._fnPageChange(oSettings, e.data.action) ) {
                    fnDraw( oSettings );
                }
            };
 
            $(nPaging).addClass('pagination').append(
                '<ul>'+
                    '<li class="prev disabled"><a href="#">&larr; '+oLang.sPrevious+'</a></li>'+
                    '<li class="next disabled"><a href="#">'+oLang.sNext+' &rarr; </a></li>'+
                '</ul>'
            );
            var els = $('a', nPaging);
            $(els[0]).bind( 'click.DT', { action: "previous" }, fnClickHandler );
            $(els[1]).bind( 'click.DT', { action: "next" }, fnClickHandler );
        },
 
        "fnUpdate": function ( oSettings, fnDraw ) {
            var iListLength = 5;
            var oPaging = oSettings.oInstance.fnPagingInfo();
            var an = oSettings.aanFeatures.p;
            var i, j, sClass, iStart, iEnd, iHalf=Math.floor(iListLength/2);
 
            if ( oPaging.iTotalPages < iListLength) {
                iStart = 1;
                iEnd = oPaging.iTotalPages;
            }
            else if ( oPaging.iPage <= iHalf ) {
                iStart = 1;
                iEnd = iListLength;
            } else if ( oPaging.iPage >= (oPaging.iTotalPages-iHalf) ) {
                iStart = oPaging.iTotalPages - iListLength + 1;
                iEnd = oPaging.iTotalPages;
            } else {
                iStart = oPaging.iPage - iHalf + 1;
                iEnd = iStart + iListLength - 1;
            }
 
            for ( i=0, iLen=an.length ; i<iLen ; i++ ) {
                // Remove the middle elements
                $('li:gt(0)', an[i]).filter(':not(:last)').remove();
 
                // Add the new list items and their event handlers
                for ( j=iStart ; j<=iEnd ; j++ ) {
                    sClass = (j==oPaging.iPage+1) ? 'class="active"' : '';
                    $('<li '+sClass+'><a href="#">'+j+'</a></li>')
                        .insertBefore( $('li:last', an[i])[0] )
                        .bind('click', function (e) {
                            e.preventDefault();
                            oSettings._iDisplayStart = (parseInt($('a', this).text(),10)-1) * oPaging.iLength;
                            fnDraw( oSettings );
                        } );
                }
 
                // Add / remove disabled classes from the static elements
                if ( oPaging.iPage === 0 ) {
                    $('li:first', an[i]).addClass('disabled');
                } else {
                    $('li:first', an[i]).removeClass('disabled');
                }
 
                if ( oPaging.iPage === oPaging.iTotalPages-1 || oPaging.iTotalPages === 0 ) {
                    $('li:last', an[i]).addClass('disabled');
                } else {
                    $('li:last', an[i]).removeClass('disabled');
                }
            }
        }
    }
} );
/***************************** FIN BOOTSTRAP PAGINATION ********************************/

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
	
	//Recherche par défaut
	var defaultSearch = '';
	if (location.hash != '') {
		$('#champion-filter-input').val(location.hash.slice(1));
		$moreFilterIcon.toggleClass('icon-plus-sign icon-minus-sign');
		$('ul.filters-list.more-filter').slideToggle();
		defaultSearch = $('#champion-filter-input').val();
	}
	
	itemBuildsTable = $('#item-builds-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": Routing.generate('item_builder_list_ajax', {_locale: locale}),
		"oSearch" :{"sSearch":  ((filter != undefined) ? filter : '')},
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
	];
	
	var $dropdownSort = $('ul#sort-list');
	$dropdownSort.find('li a.sort-link').on('click', function(e){
		e.preventDefault();
		$dropdownSort.find('li a.sort-link').removeClass('selected');
		$(this).addClass('selected');
		itemBuildsTable.fnSort([sortTable[$(this).attr('data-option-value')]]);
	});
	
	//On change filter input
	$('#champion-filter-input').on('keyup', function(e){
		if (e.keyCode == 13 || e.keyCode == 8) {
			itemBuildsTable.fnFilter($(this).val(), 3);
		}
	});
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
		url:  Routing.generate('champion_handler_front_get_champions_name', {_locale: locale}), 
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