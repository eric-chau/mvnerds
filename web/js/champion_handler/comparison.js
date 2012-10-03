var $comparisonListLoading = $('#comparison-list-loading');

function addChampionToList(slug){
	hideMessages();
	//On affiche l'icone de chargement
	$comparisonListLoading.show();

	//On fait un appel ajax pour demander à ajouter le champion
	$.ajax({
		type: 'GET',
		url:  Routing.generate('champion_handler_comparison_add_to_compare', {'slug': slug}),
		dataType: 'html'
	}).done(function(data){
		//Si data est vide ça veut dire qu'on est confrontés à une erreur
		if(data == undefined || data == '')
		{
			//Il ne faut donc pas afficher de nouveau champion dans la liste mais afficher un message d erreur
			getAlertMessage(ERROR_ALERT);
		}
		else
		{
			//Sinon on ajoute le champion à la liste
			appendChampion(data);
			//Et on affiche le message de succes
			getAlertMessage(SUCCESS_ALERT);

			if ($('li.champion-comparable').length >0)
			{
				//On active le bouton de vidage
				activateCleanButton();
				//On retire le message d'information
				$('li.indication').hide();
			}
			//Si les champions peuvent etre comparés
			if ($('li.champion-comparable').length >= 2)
			{
				//On active le bouton de comparaison
				activateCompareButton();
			}
		}
		$comparisonListLoading.hide();
	}).fail(function(){
		$comparisonListLoading.hide();
	});
};
	
//Permet d'ajouter un champion au format html à la liste de comparaison
function appendChampion(data)
{		
	//On ajoute le champion à la liste
	$(data).insertBefore('li#li-clean');
}

//permet d activer le bouton de comparaison de champions
function activateCompareButton()
{
	$('a#btn-compare').removeClass('disabled');
	$('a#btn-compare').parent('li').removeClass('disabled');
}
//Permet d activer le bouton de vidage de la liste des champions
function activateCleanButton()
{
	$('a#btn-clean').removeClass('disabled');
	$('a#btn-clean').parent('li').removeClass('disabled');
}
//Permet de désactiver le bouton de vidage de la liste des champions
function deactivateCleanButton()
{
	$('a#btn-clean').addClass('disabled');
	$('a#btn-clean').parent('li').addClass('disabled');
}
//Permet de désactiver le bouton de comparaison
function deactivateCompareButton()
{
	$('a#btn-compare').addClass('disabled');
	$('a#btn-compare').parent('li').addClass('disabled');
}


jQuery(function($) {
	//On active les tooltips
	$('#wrapper').on('mouseover', '.tooltip-anchor', function(){
		$(this).tooltip('show');
	});
	
	//On active le popover du bouton d aide de l actionbar
	$('#comparison-list-help').popover();
		
	//Désactivation des liens qui ont pour classe disabled
	$('#wrapper').on('click', 'a.disabled', function(e){
		e.preventDefault();
	});
	
	//Lors du clic sur le bouton de vidage de la liste s'il n'a pas la classe disabled'
	$('div.actions-bar').on('click', 'a#btn-clean:not(.disabled)', function(){
		hideMessages();
		$('#comparison-list-dropdown').addClass('open');
		$comparisonListLoading.show();
		//On fait un appel ajax pour demander à ajouter le champion
		$.ajax({
			type: 'GET',
			url:  Routing.generate('champion_handler_comparison_clean_comparison'),
			dataType: 'json'
		}).done(function(data){
			//Si data vaut true
			if(data[0]){
				//On retire tous les champions
				$('ul#comparison-list li.champion-comparable').each(function(){
					$(this).remove();
				});
				//On affiche l indication
				$('ul#comparison-list li.indication').show();
				//On affiche le message de succes
				getAlertMessage(SUCCESS_ALERT);
				
				//On désactive les deux boutons
				deactivateCleanButton();
				deactivateCompareButton();
			}
			$comparisonListLoading.hide();
		}).fail(function(){
			$comparisonListLoading.hide();
		});
		return false;
	});
		
	//Récupération de la liste des champions et de la comparison list
	var $comparisonList = $('#comparison-list');
	
	//On rends chaque champion draggable
	$('#champion-comparison').on('mouseover', 'li.champion:not(.champion-maxi)', function(){
		$(this).draggable({
			diabled: false,
			helper: 'clone',
			revert: 'invalid',
			revertduration: 300,
			zIndex: 1100,
			opacity: 1,
			distance: 20,
			start: function(){
				$('#comparison-list').css('border', '1px dashed black')
				$('#comparison-list-dropdown').addClass('open');
			},
			stop: function(){
				$('#comparison-list').css('border', 'none');
			}
		});
	});
	
	//On rends la comparison list capable d accepter les champions
	$comparisonList.droppable({
		accept: '#champion-list li.champion',
		over: function(){
			$('#comparison-list').css('border', '1px solid black');
		},
		out: function(){
			$('#comparison-list').css('border', '1px dashed black');
		},
		drop: function( event, ui ) {
			addChampionToList(ui.draggable.context.id);
		}
	});
	
	
	
	$('#comparison-list').on('click', 'a.champion-comparable-remove', function(){
		var slug = $(this).find('span.slug').html();
		$(this).tooltip('destroy');
		$comparisonListLoading.show();
		
		hideMessages();
		
		//On fait un appel ajax pour demander à ajouter le champion
		$.ajax({
			type: 'GET',
			url:  Routing.generate('champion_handler_comparison_remove_from_compare', {'slug': slug}),
			dataType: 'json'
		}).done(function(data){
			//Si data vaut true
			if(data[0]){
				//On retire le champion et son tooltip
				$('ul#comparison-list li#comparable-'+slug+' a').tooltip('destroy')
				$('ul#comparison-list li#comparable-'+slug).remove();
				//On vérifie la taille de la liste
				var listSize = $('ul#comparison-list li.champion-comparable').size();
				if( listSize < 1){
					//On désactive les deux boutons
					deactivateCleanButton();
					deactivateCompareButton();
					//On affiche l indication
					$('ul#comparison-list li.indication').show();
				}
				else if(listSize < 2){
					deactivateCompareButton();
				}
				//Et on affiche le message de succes
				getAlertMessage(SUCCESS_ALERT);
			}
			$comparisonListLoading.hide();
		}).fail(function(){
			$comparisonListLoading.hide();
		});
		return false;
	});
});