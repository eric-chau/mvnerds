var SUCCESS_ALERT = 'success';
var ERROR_ALERT = 'error';

var timeoutID;

//Permet de récupérer les messages d'alerte du serveur
function getAlertMessage(type)
{
	$.ajax({
		type: 'GET',
		url: Routing.generate('champion_handler_comparison_get_'+type+'_message', {_locale: locale}),
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
	var alert = '<div class="fade in hide alert alert-fixed alert-'+type+'"><button type="button" class="close" data-dismiss="alert">×</button>'+message+'</div>';
	//On crée le nouveau message
	$('body').append(alert);
	$('.alert-fixed').show(400);
	timeoutID = setTimeout(function(){
		hideMessages();
	},
	3000);
}
//Permet de retirer les messages d'alerte actuellement affichés
function hideMessages(){
	clearTimeout(timeoutID);
	$('div.alert-fixed').animate({
		opacity: 0.25,
		bottom: '+=100'
	},
	800,
	function(){
		$(this).remove();
	});
}