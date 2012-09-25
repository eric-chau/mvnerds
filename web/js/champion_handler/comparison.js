//Fonction appelée lorsqu'on commence à faire glisser un champion
function dragStart(event) {
	//On récupère le slug du champion en cours de déplacement
	var slug = event.target.getAttribute('id');
	console.log('slug  :'+slug);
	//On crée une image qui va être déplacée graphiquement
	var dragImg = new Image(); 
	dragImg.src = $('img#' + slug).attr('src');

	event.dataTransfer.effectAllowed = 'copy';
	event.dataTransfer.setData("Text", slug);
	event.dataTransfer.setDragImage(dragImg, 50, 50);

	$('#comparison-list').css('border', '1px dashed black')

	$('div.main-container').fadeTo('fast', 0.2);
	$('div.navbar-fixed-top').fadeTo('fast', 0.2);
}

//Lorsque l'utilisateur arrete de faire glisser l'objet ou qu'il appuye sur echap
function dragEnd(event){
	$('#comparison-list').css('border', 'none');

	$('div.main-container').fadeTo('fast', 1);
	$('div.navbar-fixed-top').fadeTo('fast', 1);
}

//Lorsqu'on est au dessus de la zone de dépot
function dragOver(event) {
	event.preventDefault();
	$('#comparison-list').css('border', '1px solid black')
}
//lorsqu'on entre dans la zone de dépot
function dragEnter(event) {
	event.preventDefault();
	$('#comparison-list').css('border', '1px solid black')
}
//lorsqu'on sort de la zone de dépot
function dragLeave(event) {
	event.preventDefault();
	$('#comparison-list').css('border', '1px dashed black')
}

//lorsqu'on dépose le champion dans la zone de dépot
function drop(event){
	event.preventDefault();

	//On remet la bordure à son état normal
	$('#comparison-list').css('border', 'none')

	//On retire le message s'il y en a un
	//S'il existe deja un message on l'enleve
	$('div.container div.alert').remove();

	//On affiche l'icone de chargement
	$('li#comparison-list-loading').show();

	//On récupère le slug du champion concerné
	var slug = event.dataTransfer.getData('Text');
	
	//On fait un appel ajax pour demander à ajouter le champion
	$.ajax({
		type: 'GET',
		url:  Routing.generate('champion_handler_comparison_add_to_compare', {'slug': slug}),
		dataType: 'html'
	}).done(function(data){
		//Si data est vide ça veut dire qu'on est confrontés à une erreur
		if(data == undefined || data == '')
		{console.log('error');
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

	$('div.main-container').fadeTo('fast', 1);
	$('div.navbar-fixed-top').fadeTo('fast', 1);
}

//Permet d'ajouter un champion au format html à la liste de comparaison
function appendChampion(data)
{		
	//On ajoute le champion à la liste
	var newChamp = $(data).appendTo('#comparison-list ul.nav');
	console.log(newChamp);
	//Si la taille de la liste est minimisée
	if ( ! isMaximized())
	{
		//On cache les champs texte et l'icone de suppression
		newChamp.find('span.champion-comparable-name').hide();
		newChamp.find('a.champion-comparable-remove').hide();
		console.log('minified');
	}
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
	$('#comparison-list-sidebar').after('<div class="alert alert-'+type+'"><button type="button" class="close" data-dismiss="alert">×</button>'+message+'</div>');
}

//permet d activer le bouton de comparaison de champions
function activateCompareButton()
{
	$('a#btn-compare').removeClass('disabled');
	$('a#btn-compare').unbind('click').click();
}

//Permet d activer le bouton de vidage de la liste des champions
function activateCleanButton()
{
	$('a#btn-clean').removeClass('disabled');
	$('a#btn-clean').unbind('click').click();
}

function minimize()
{
	var championsComparables = $('#comparison-list ul.nav-list li.champion-comparable');
	championsComparables.find('span.champion-comparable-name').hide(300);
	championsComparables.find('a.champion-comparable-remove').hide(300);

	$('#comparison-list ul.nav-list li.indication').hide();

	$('#comparison-list ul.nav-list li.nav-header').hide(300);

	$('#comparison-list a.btn span.btn-text').hide(300);

	$('#comparison-list-sidebar').animate({
		width: '100px'
	}, 1000);

	$('#resize-comparison-list').removeClass('minimize');
	$('#resize-comparison-list').addClass('maximize');

	$('#resize-comparison-list i').removeClass('icon-resize-small');
	$('#resize-comparison-list i').addClass('icon-resize-full');

	//Maximisation de la liste de comparaison lors du clic sur le bouton de redimentionnement
	$('#resize-comparison-list').one('click', function(){
		maximize();
	});
}

function maximize()
{
	var championsComparables = $('#comparison-list ul.nav-list li.champion-comparable');


	$('#comparison-list-sidebar').animate({
		width: '241px'
	}, 1000, function(){
		championsComparables.find('span.champion-comparable-name').show(300);
		championsComparables.find('a.champion-comparable-remove').show(300);

		$('#comparison-list ul.nav-list li.nav-header').show(300);

		$('#comparison-list a.btn span.btn-text').show(300);
	});			

	$('#resize-comparison-list').removeClass('maximize');
	$('#resize-comparison-list').addClass('minimize');

	$('#resize-comparison-list i').removeClass('icon-resize-full');
	$('#resize-comparison-list i').addClass('icon-resize-small');

	//Minimisation de la liste de comparaison lors du clic sur le bouton de minimisation
	$('#resize-comparison-list').one('click', function(){
		minimize();
	});
}

function isMaximized()
{
	return $('#resize-comparison-list').hasClass('minimize');
}

jQuery(function($) {
	$('.tooltip-anchor').tooltip();	

	//Désactivation des liens désactivés
	$('a.disabled').click(function(e){e.preventDefault();});

	//Minimisation de la liste de comparaison lors du clic sur le bouton de redimentionnement
	$('#resize-comparison-list.minimize').one('click', function(){
		minimize();
	});

	//Si lors du chargement de la page la fenetre n'est pas assez grande pour contenir la 
	//version maximisée de la  liste de comparaison
	if ($(window).width() <= 1550)
	{
		//On retire l'évenement de clic affecté
		$('#resize-comparison-list').off('click');
		//On minimise la liste de comparaison
		minimize();
	}

	//Si on redimentionne la fenetre
	$(window).resize(function(){
		//Et que sa largeur devient inférieure ou égale à 1550
		if ($(window).width() <= 1550)
		{
			//On retire l'évenement de clic affecté
			$('#resize-comparison-list').off('click');
			//On minimise la liste de comparaison
			minimize();
		}
	});

	$('#comparison-list-help').popover();
});