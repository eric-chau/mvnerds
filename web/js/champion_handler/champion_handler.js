var $comparisonListLoading = $('#comparison-list-loading');
var $filterListAddChampionLoading = $('#filter-list-add-champions-loading');
var nbComparedChampions = 0;

function addChampionToList(slug){
	hideMessages();
	//On affiche l'icone de chargement
	$comparisonListLoading.show();
	toggleProgressCursor(true, '#'+slug);
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
		toggleProgressCursor(false, '#'+slug, 'pointer');
	}).fail(function(){
		$comparisonListLoading.hide();
		toggleProgressCursor(false,'#'+slug, 'pointer');
	});
}

//Permet d ajouter plusieurs champions simultanément en fournissant un tableau de slugs de champions
function addManyChampionsToList(championsSlugArray){
	hideMessages();
	//On affiche l'icone de chargement
	$comparisonListLoading.show();
	toggleProgressCursor(true, '#li-compare-filtered a');

	//On fait un appel ajax pour demander à ajouter le champion
	$.ajax({
		type: 'POST',
		url:  Routing.generate('champion_handler_comparison_add_many_to_compare'),
		data: {championsSlug: championsSlugArray},
		dataType: 'html'
	}).done(function(data){
		
		cleanComparisonList();
		//Sinon on ajoute le champion à la liste
		appendManyChampions(data);

		//Et on affiche les messages
		getAlertMessage(SUCCESS_ALERT);
		getAlertMessage(ERROR_ALERT);

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

		$comparisonListLoading.hide();
		toggleProgressCursor(false, '#li-compare-filtered a');
	}).fail(function(data){
		$comparisonListLoading.hide();
		toggleProgressCursor(false, '#li-compare-filtered a');
	});
}

//Permet d'ajouter un champion au format html à la liste de comparaison
function appendChampion(data)
{		
	//On ajoute le champion à la liste
	$(data).insertBefore('#comparison-list li#li-clean');
	nbComparedChampions++;
	if (nbComparedChampions > 15){
		$('#comparison-list').addClass('scrollable');
	}
	setNbComparedChampionsLabel(nbComparedChampions);
}
function appendManyChampions(data)
{		
	//On ajoute le champion à la liste
	$(data).insertBefore('#comparison-list li#li-clean');
	nbComparedChampions+= getNbComparedChampions();
	if (nbComparedChampions > 15){
		$('#comparison-list').addClass('scrollable');
	}
	setNbComparedChampionsLabel(nbComparedChampions);
}

function removeChampionFromComparisonList(slug)
{
	//On retire le champion
	$('ul#comparison-list li#comparable-'+slug).remove();
	//On vérifie la taille de la liste
	if( nbComparedChampions < 1){
		//On désactive les deux boutons
		deactivateCleanButton();
		deactivateCompareButton();
		//On affiche l indication
		$('ul#comparison-list li.indication').show();
	}
	else if(nbComparedChampions < 2){
		deactivateCompareButton();
	}
	else if (nbComparedChampions <= 16){
		$('#comparison-list').removeClass('scrollable');
	}
	nbComparedChampions--;
	setNbComparedChampionsLabel(nbComparedChampions);
	//Et on affiche le message de succes
	getAlertMessage(SUCCESS_ALERT);
}

function cleanComparisonList()
{
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
	$('#comparison-list').removeClass('scrollable');
	nbComparedChampions = 0;
	setNbComparedChampionsLabel(nbComparedChampions);
}
function getNbComparedChampions(){
	return $('#comparison-list li.champion-comparable').size();
}
function setNbComparedChampionsLabel(value)
{
	$('#comparison-list-size').html(' ('+value+')');
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





var options, $isotope; 
	
function initIsotope($isotope){
	//$isotope.imagesLoaded( function(){
		$isotope.isotope(options);
	//});
	
	//Bloquage du drag sur les champions agrandis
	$isotope.on('mouseover', 'li.champion-maxi', function(){
		$(this).draggable('disable');
	});
	
	//Lors du clic sur un champion miniature
	var timeout, dblClic = false, that;
	$isotope.on('click', 'li.champion:not(.champion-maxi)', function(e){
		that = this;
		
		if(!$(that).hasClass('animating')){
			e.preventDefault();
			timeout = setTimeout(function() {
				if (!dblClic){
					timeout = null;
					maximizeChampion($(that), $isotope);
				}
				else {
					dblClic = false;
				}
			}, 200);
		}
	}).on('dblclick', function(){
		if(!$(that).hasClass('champion-maxi') && !$(that).hasClass('animating')){
			clearTimeout(timeout);
			timeout = null;
			dblClic = true;
			addChampionToList($(that).attr('id'));
		}
	});
	//Lors du clic sur le bouton close d un champion maximisé
	$isotope.on('click', 'li.champion-maxi div.preview-header', function(){
		return minimizeChampion($('#'+$(this).attr('data-dissmiss')), $isotope);
	});
}
	
function maximizeChampion($champ, $isotope){	
	//Si on trouve un autre champion déjà maximisé on le referme
	var $maxiChampion = $isotope.find('li.champion-maxi');
	if($maxiChampion != undefined){
		minimizeChampion($maxiChampion , $isotope);
	}
	
	$champ.find('div.portrait').fadeOut(250);
	$champ.addClass('champion-maxi');
	setTimeout(function() 
	{
		$champ.find('div.preview').fadeIn(250);
		$champ.find('div.champion-portrait').fadeIn(250);
		
		$isotope.isotope( 'reLayout', function(){
			
			setTimeout(function(){
				scrollToChampion($('#'+$champ.attr('id')))				
				},
				150
			);
		});
	},
	320);

	return false;
}

function minimizeChampion($champ, $isotope){
	$champ.find('div.champion-portrait').fadeOut(150);
	$champ.find('div.preview').fadeOut(150);
	setTimeout(function() 
	{
		$champ.find('div.portrait').fadeIn(300);
		
		$champ.toggleClass('animate-champion-portrait champion-maxi');
		setTimeout(function() 
		{
			$isotope.isotope( 'reLayout');
			$champ.removeClass('animate-champion-portrait');
		},
		320);
	},
	150);

	$champ.draggable('enable');

	return false;
}

function scrollToChampion($champ){
	var position = $champ.position().top + $('#champion-comparison-center').position().top - 200;
	$('body,html').animate({scrollTop:position},500);
}




var typeaheadValue, $filterValue;

function initTypeahead($isotope) {
	$filterValue.off('keyup change');
	$filterValue.keyup(function(){
		filter($isotope);
	});
	$filterValue.change(function(){
		filter($isotope);
	});
}
function initFilterList($isotope) {
	$('#filters-list  li').off('click', ' a.filter-link:not(.selected)');
	$('#filters-list li').off('click', 'a.selected');
	
	$('#filters-list  li').on('click', ' a.filter-link:not(.selected)', function(){
		$(this).addClass('selected');
		$('a#drop-filter-list').addClass('active');
		activateCompareFilteredButton();
		addFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		return false;
	});
	$('#filters-list li').on('click', 'a.selected',function() {
		$(this).removeClass('selected');
		removeFilterValue($isotope, '.'+$(this).attr('data-option-value'));
		if($('#filters-list li a.selected').size() <= 0)
		{
			$('a#drop-filter-list').removeClass('active');
			deactivateCompareFilteredButton();
		}
		return false;
	});
	$('#li-clean-filter').on('click', '#btn-clean-fitler', cleanFilter);
	$('#li-compare-filtered').on('click', '#btn-compare-filtered', addFilteredChampions);
}

function setTypeaheadChampionsName() {
	$.ajax({
		type: 'POST',
		url:  Routing.generate('champion_handler_front_get_champions_name'),
		dataType: 'html'
	}).done(function(data){
		$filterValue.attr('data-source', data);
	});
}

function filter($isotope) {
	var filterValue = $filterValue.val();
	if(filterValue != '')
	{
		setTypeaheadValue($isotope, "[data-name*='" + filterValue.toLowerCase()+"']");
	}
	else
	{
		removeFilterValue($isotope, typeaheadValue);
		$isotope.isotope(options);
	}
}
function addFilterValue($isotope, value) {
	options['filter'] = options['filter'] + value;
	activateCleanFilterButton();
	$isotope.isotope(options);
}
function removeFilterValue($isotope, value) {
	var oldOptions = options['filter'];
	var newOptions;
	if(value != undefined && value != '')
	{
		var splitedOptions = oldOptions.split(value);
		newOptions = splitedOptions[0].concat(splitedOptions[1]);
	}
	else
	{
		newOptions = oldOptions;
	}
	typeaheadValue = undefined;
	if(newOptions == undefined || newOptions == '')
	{
		deactivateCleanFilterButton();
	}
	options['filter'] = newOptions;
	$isotope.isotope(options);
}
function setTypeaheadValue($isotope, value){
	removeFilterValue($isotope, typeaheadValue);
	typeaheadValue = value;
	addFilterValue($isotope,typeaheadValue);
	$isotope.isotope(options);
}

//Permet de vider le filtre
function cleanFilter() {
	$('a#drop-filter-list').removeClass('active');
	$filterValue.val('');
	options['filter'] = '';
	$('ul#filters-list ul.tags-group a.filter-link.selected').each(function(){
		$(this).removeClass('selected');
	});
	deactivateCleanFilterButton();
	deactivateCompareFilteredButton();
	$isotope.isotope(options);
	return false;
}
function activateCleanFilterButton(){
	$('#li-clean-filter').removeClass('disabled hide');
	$('#li-clean-filter').find('a').removeClass('disabled');
}
function deactivateCleanFilterButton(){
	$('#li-clean-filter').addClass('disabled hide');
	$('#li-clean-filter').find('a').addClass('disabled');
}

//Permet d ajouter tous les champions filtrés à la liste
function addFilteredChampions(){
	var championsSlug = new Array();
	$('#isotope-list li.isotope-item.champion:not(.isotope-hidden)').each(function(){
		championsSlug.push($(this).attr('id'));
	});
	addManyChampionsToList(championsSlug);
	return false;
}
function activateCompareFilteredButton(){
	$('#li-compare-filtered').removeClass('disabled hide');
	$('#li-compare-filtered').find('a').removeClass('disabled');
}
function deactivateCompareFilteredButton(){
	$('#li-compare-filtered').addClass('disabled hide');
	$('#li-compare-filtered').find('a').addClass('disabled');
}





jQuery(function() {
	
	nbComparedChampions = $('#comparison-list li.champion-comparable').size();
	
	if (nbComparedChampions > 15){
		$('#comparison-list').addClass('scrollable');
	}
		
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
				cleanComparisonList();
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
			disabled: false,
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
				removeChampionFromComparisonList(slug);
			}
			$comparisonListLoading.hide();
		}).fail(function(data){
			$('div.presentation-container').html('<div>'+data.responseText+'</div>');
			$comparisonListLoading.hide();
		});
		return false;
	});
	
	options =  getIsotopeOptions();
	$isotope = $('#isotope-list');
	initIsotope($isotope);
	
	
	$filterValue = $('#filter-value');
	setTypeaheadChampionsName();
	initTypeahead($isotope);
	initFilterList($isotope);
});
