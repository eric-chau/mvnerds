var $voteContainer;

$(document).ready(function() {
	$voteContainer = $('.feed-content .feed-infos, .comments-list');
	
	//Lors du clic sur un bouton de vote sur la page de détail
	$voteContainer.on('click', '.vote-actions .vote', function(){
		//On détermine si c'est un vote positif ou non
		var like = $(this).hasClass('vote-up') ? true : false;
		//on récupère le conteneur parent afin de pouvoir récupérer l'ID et le type de l'objet
		var $parent = $(this).parent('.vote-actions');
		
		//Préparation des paramètres
		var params = {
			object_id		: $parent.data('object-id'), 
			object_type		: $parent.data('object-type'),
			like			: like
		};
		
		//On essaie d'envoyer le vote en ajax
		$.ajax({
			type: 'POST',
			url:  Routing.generate('vote_rate', {_locale: locale}),
			data: params,
			dataType: 'json'
		}).done(function(data){
			console.log('success');
			var rating = data.rating;
			$parent.find('.rating').html(rating);
			displayMessage(data.message, SUCCESS_ALERT);
		}).fail(function(msg){
			console.log('fail');
			console.log(msg.responseText);
			displayMessage(msg.responseText, ERROR_ALERT);
		});
	});
});
