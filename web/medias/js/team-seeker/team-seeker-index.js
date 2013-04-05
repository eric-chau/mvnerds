var $searchTeamButton;

function seekTeam() {
	/**
	 * TO RO0NY regionVal et teamVal
	 * CE SONT LES DEUX VARIABLES UTILISEES POUR EFFECTUER LA RECHERCHE
	 * DES L ARRIVEE SUR LA PAGE
	 * SO PLS SET LES DEUX VARS AVEC LA REGION ET LA TEAM
	 */
	if (typeof regionVal != 'undefined' && typeof teamVal != 'undefined') {
		$('div#team-seeker-result').hide();
		$('div.loader-container').slideDown('slow');
		$searchTeamButton.addClass('disabled');
		$('#team-seeker-region-selector').attr('disabled', 'disabled');
		$('input#team-seeker-input').attr('disabled', 'disabled');
		$('div.form-errors').html('');
		$searchTeamButton.addClass('disabled');
		if (regionVal != '' && teamVal != '') {
			$.ajax({
				type: 'POST',
				url:  Routing.generate('team_seeker_seek_ajax', {'_locale': locale}),
				data: {
					'region': regionVal,
					'team_tag_or_name': teamVal
				},
				dataType: 'html'
			}).done(function(response) {
				$('div.loader-container').hide();
				$('div#team-seeker-result').html(response);
				$('div#team-seeker-result').fadeIn('fast');
				//$searchTeamButton.removeClass('disabled');
				$('input#team-seeker-input').removeAttr('disabled');
				$('#team-seeker-region-selector').removeAttr('disabled');
			}).fail(function(response) {
				$('div.form-errors').html(response.responseText);
				$('div.loader-container').hide();
				$searchTeamButton.removeClass('disabled');
				$('input#team-seeker-input').removeAttr('disabled');
				$('#team-seeker-region-selector').removeAttr('disabled');
			})
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
	});
	
	//Soumission du formulaire de recherche de team
	$('form#team-seeker-form').on('submit', function(event) {
		event.preventDefault();
		
		regionVal = $('#team-seeker-region-selector').val();
		teamVal = $('#team-seeker-input').val();

		seekTeam();
	});
});