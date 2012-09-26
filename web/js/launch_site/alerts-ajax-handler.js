const SUCCESS_ALERT = 'success';
const ERROR_ALERT = 'error';
const HIDE_SPEED = 800;
//Permet de récupérer les messages d'alerte du serveur
function getAlertMessage(type)
{
	$.ajax({
		type: 'GET',
		url: Routing.generate('champion_handler_comparison_get_'+type+'_message'),
		dataType: 'text'
	}).done(function(message){
		if (message != undefined && message != '')
		{
			displayMessage(message, type);
		}
	});
}
//Permet d afficher les messages d erreur et de succes
function displayMessage(message, type)
{			
	//On crée le nouveau message
	$('#champion-comparison').before('<div class="fade in alert alert-fixed alert-'+type+'"><button type="button" class="close" data-dismiss="alert">×</button>'+message+'</div>');
}
//Permet de retirer les messages d'alerte actuellement affichés
function hideMessages(){
	$('div.container div.alert').animate({
		opacity: 0.25,
		bottom: '+=100'
	},
	HIDE_SPEED,
	function(){
		$(this).remove();
	});
}