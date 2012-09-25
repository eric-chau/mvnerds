
//Permet d'ajouter un champion au format html à la liste de comparaison
function appendChampion(data)
{		
	//On ajoute le champion à la liste
	$(data).appendTo('ul#comparison-list');
	var id = $(data).attr('id');
	
	$('#'+id).on('click', function(){
		return removeChampionFromList($(this).find('span.slug').html());		
	});
	$('.tooltip-anchor').tooltip('destroy');
	$('.tooltip-anchor').tooltip();
}

//Permet de retirer un champion de la liste en ajax
function removeChampionFromList(slug)
{
	$('#comparison-list-loading').show();
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
		}
		$('#comparison-list-loading').hide();
	}).fail(function(data){console.log('remove fail');
		console.log(data);
		$('#comparison-list-loading').hide();
	});
	return false;
}

//Permet de vider la liste de comparaison en ajax
function cleanList()
{
	$('#comparison-list-dropdown').addClass('open');
	$('#comparison-list-loading').show();
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
			//On désactive les deux boutons
			deactivateCleanButton();
			deactivateCompareButton();
		}
		$('#comparison-list-loading').hide();
	}).fail(function(data){console.log('remove fail');
		$('#comparison-list-loading').hide();
	});
	return false;
}

//Permet de récupérer les messages d erreur du serveur
function getErrorMessage()
{
	$.ajax({
		type: 'GET',
		url: Routing.generate('champion_handler_comparison_get_error_message'),
		dataType: 'text'
	}).done(function(message){
		if (message != undefined && message != '')
		{
			displayMessage(message, 'error');
		}
	});
}

//Permet de récupérer les messages de succes du serveur
function getSuccessMessage()
{
	$.ajax({
		type: 'GET',
		url: Routing.generate('champion_handler_comparison_get_success_message'),
		dataType: 'text'
	}).done(function(message){
		if (message != undefined && message != '')
		{
			displayMessage(message, 'success');
		}
	});
}

//Permet d afficher les messages d erreur et de succes
function displayMessage(message, type)
{			
	//On crée le nouveau message
	$('#champion-comparison').before('<div class="alert alert-'+type+'"><button type="button" class="close" data-dismiss="alert">×</button>'+message+'</div>');
}

//permet d activer le bouton de comparaison de champions
function activateCompareButton()
{
	$('a#btn-compare').removeClass('disabled');
	$('a#btn-compare').unbind('click');
}

//Permet d activer le bouton de vidage de la liste des champions
function activateCleanButton()
{
	$('a#btn-clean').removeClass('disabled');
	$('#btn-clean').on('click', function(){
		return cleanList();
	});
}
//Permet de désactiver le bouton de vidage de la liste des champions
function deactivateCleanButton()
{
	var $cleanButton = $('a#btn-clean');
	$cleanButton.addClass('disabled');
	$cleanButton.off('click');
	$cleanButton.on('click', function(){return false;});
}
//Permet de désactiver le bouton de comparaison
function deactivateCompareButton()
{
	var $compareBtn = $('a#btn-compare');
	$compareBtn.addClass('disabled');
	$compareBtn.off('click');
	$compareBtn.on('click', function(){return false;});
}

jQuery(function($) {
	$('.tooltip-anchor').tooltip();	
	$('#comparison-list-help').popover();
	
	$('#btn-clean').click(function(){return cleanList();});
	
	//Désactivation des liens désactivés
	$('a.disabled').click(function(e){e.preventDefault();});
		
	//Récupération de la liste des champions et de la comparison list
	var $champions = $('#champion-list'),
		$comparisonList = $('#comparison-list');
	
	//On rends chaque champion draggable
	$('li.champion', $champions).draggable({
		helper: 'clone',
		revert: 'invalid',
		revertduration: 300,
		zIndex: 1100,
		opacity: 1,
		start: function(){
			//$('div.main-container').fadeTo('fast', 0.2);
			$('#comparison-list').css('border', '1px dashed black')
			$('#comparison-list-dropdown').addClass('open');
		},
		stop: function(){
			$('#comparison-list').css('border', 'none');
			//$('div.main-container').fadeTo('fast', 1);
		}
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
			//S'il existe deja un message on l'enleve
			$('div.container div.alert').remove();

			//On affiche l'icone de chargement
			$('li#comparison-list-loading').show();

			//On récupère le slug du champion concerné
			var slug = ui.draggable.context.id;
			
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
					getErrorMessage();
				}
				else
				{
					//Sinon on ajoute le champion à la liste
					appendChampion(data);
					//Et on affiche le message de succes
					getSuccessMessage();

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
				$('#comparison-list-loading').hide();
			}).fail(function(data){
				console.log(data);
				$('#comparison-list-loading').hide();
			});
		}
	});
	
	$('a.champion-comparable-remove').on('click', function(){
		return removeChampionFromList($(this).find('span.slug').html());
	});
});