var filter, newsTable;

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
	
	var langage;
	if(locale != undefined && locale == 'en') {
		langage = {
			"sProcessing":     "Processing...",
			"sLengthMenu":     "Show _MENU_ news per page",
			"sZeroRecords":    "There is no news to show",
			"sInfo":           "Showing news from _START_ to _END_ on a total of _TOTAL_ news",
			"sInfoEmpty":      "Empty list",
			"sInfoFiltered":   "(filtered from a total of _MAX_ news)",
			"sInfoPostFix":    "",
			"sSearch":         "<i class='icon-search icon-white'></i>",
			"sLoadingRecords": "Loading...",
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
		"sProcessing":     "Traitement en cours...",
		"sLengthMenu":     "Afficher _MENU_ news par page",
		"sZeroRecords":    "Aucune news à afficher",
		"sInfo":           "Affichage des news de _START_ à _END_ sur un total de _TOTAL_ news",
		"sInfoEmpty":      "Affichage des news 0 à 0 sur 0 news",
		"sInfoFiltered":   "(filtré de _MAX_ news au total)",
		"sInfoPostFix":    "",
		"sSearch":         "<i class='icon-search icon-white'></i>",
		"sLoadingRecords": "Chargement...",
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
		"bLengthChange": false,
		"iDisplayLength": 3,
		"aoColumns": [
                      {"bSearchable": true, "bSortable":false},
                      {"bSearchable": false, "bSortable":false},
                      {"bSearchable": false, "bSortable":false},
                      {"bSearchable": false, "bSortable":false},
                      {"bSearchable": false, "bSortable":false}
		],
		"sPaginationType": 'bootstrap',
		"oLanguage": langage
	});
	
	$('#news-table_length').addClass('pull-left');
	$('#news-table_filter').addClass('pull-right');
	
	//CHAMP DE RECHERCHE
	$('#news-table_filter label').addClass('search-box');
	
	if(locale != undefined && locale == 'en') {
		$('#news-table_filter label input').attr('placeholder', 'Search in the title');
	} else {
		$('#news-table_filter label input').attr('placeholder', 'Rechercher dans le titre');
	}
	
});