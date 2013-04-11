$(document).ready(function() {
	var localPlayerID = playerID;
	var localTryCounter
	$.ajax({
		url: Routing.generate('team_seeker_get_player_solo_league', {'_locale': locale}),
		type: 'POST',
		data: {
			'region': region,
			'team_tag': teamTagOrName,
			'player_id': localPlayerID
		},
		dataType: 'html',
		success: function(response) {
			console.log(playerID);
			var $wrapper = $('<div>').append(response);
			var $rowPlayer = $wrapper.find('div.row-player');
			$('div.row-player#' + localPlayerID).html($rowPlayer.html());

			if ($('div.players-container').find('span.league-label.loading').length == 0) {
				$('a#team-seeker-submit').removeClass('disabled');
				$('input#team-seeker-input').removeAttr('disabled');
				$('#team-seeker-region-selector').removeAttr('disabled');
			}
		},
		error: function(response) {
			/*var $formErrors = $('div.form-errors');
			$formErrors.hide();
			$formErrors.html(response.responseText);
			$formErrors.show();
			$('a#team-seeker-submit').removeClass('disabled');
			$('input#team-seeker-input').removeAttr('disabled');
			$('#team-seeker-region-selector').removeAttr('disabled');*/
			$('div.row-player#' + localPlayerID).find('span.league-label.loading').html(response.responseText);
		}
	});
});