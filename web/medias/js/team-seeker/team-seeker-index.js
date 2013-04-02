var $searchTeamButton;

function seekTeam() {
	/**
	 * TO RO0NY regionVal et teamVal
	 * CE SONT LES DEUX VARIABLES UTILISEES POUR EFFECTUER LA RECHERCHE
	 * DES L ARRIVEE SUR LA PAGE
	 * SO PLS SET LES DEUX VARS AVEC LA REGION ET LA TEAM
	 */
	if (typeof regionVal != 'undefined' && typeof teamVal != 'undefined') {
		if (regionVal != '' && teamVal != '') {
			$.ajax({
				type: 'POST',
				url:  Routing.generate('team_seeker_seek_ajax', {'_locale': locale}),
				data: {
					'region': regionVal,
					'team_tag_or_name': teamVal
				},
				dataType: 'html'
			}).done(function(response){
				$('div#team-seeker-container').append(response);
				console.log('done');
			}).fail(function(){
				console.log('fail');
			})
		} else {
			console.log('not valid');
		}
	}
}

$(document).ready(function() {
	
	$searchTeamButton = $('#team-seeker-submit');
	
	//Recherche lancée dès l'arrivée sur la page pour le cas ou la recherche n'a pas été faite depuis la page dédiée.
	seekTeam();
	
	// Event d'écoute sur le change du texte sur le champ de texte #team-seeker-input
	$('input#team-seeker-input').on('click keyup change', function(event)
	{
		if ($.trim($(this).val()) != '') {
			$searchTeamButton.removeClass('disabled');
		} else {
			$searchTeamButton.addClass('disabled');
		}

		if (event.which == 13) {
			$searchTeamButton.trigger('click');
		}
	});
	
	//Soumission du formulaire de recherche de team
	$searchTeamButton.click(function(e) {
		e.preventDefault();
		
		$('#team-seeker-spinner').removeClass('hide');
		regionVal = $('#team-seeker-region-selector').val();
		teamVal = $('#team-seeker-input').val();

		seekTeam();
	});
	
	// Vérifie si c'est la première fois ou non que l'utilisateur accède au module Team Seeker
	var howItWorksValue = getItemFromLS('display_how_it_works_team_seeker');
	if (howItWorksValue == undefined || howItWorksValue == 'true') {
		$('a.how-it-works-toggle').find('span.label').toggleClass('disabled');
		$('div.how-it-works').slideDown();

		if (howItWorksValue == undefined) {
			saveItemInLS('display_how_it_works_team_seeker', false);
		}
	}
	// Toggle du "comment ça marche ?"
	$('a.how-it-works-toggle').on('click', function(event) {
		event.stopPropagation();
		$('div.how-it-works').slideToggle();
		var $label = $(this).find('span.label');
		$label.toggleClass('disabled');
		saveItemInLS('display_how_it_works_team_seeker', $label.hasClass('disabled'));
	});
});