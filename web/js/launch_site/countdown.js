var dayLabel = 'jour',
	hourLabel = 'heure',
	minuteLabel = 'minute',
	secondLabel = 'seconde';
	
if (locale != 'fr') {
	dayLabel = 'day';
	hourLabel = 'hour';
	secondLabel = 'second';
}

$(document).ready(function() 
{
	/**
	 * GESTION DU COMPTE A REBOUR AVANT LA DATE DE SORTIE
	 */

	// Variable qui contient la date de lancement souhaité
	var launchDate = new Date(2012, 11, 15, 12, 00, 00);
	// Variables contiennent les sélecteurs jQuery sur les div qui afficheront le nombre de jour, heure, minute et seconde restant
	var $daysDiv = $('div.days'),
		$hoursDiv = $('div.hours'),
		$minutesDiv = $('div.minutes'),
		$secondsDiv = $('div.seconds');

	updateCountDown();
	
	function updateCountDown() 
	{
		var now = new Date();
		// On calcule le nombre de seconde qui sépare la date de lancement et la date du jour
		// Note : on retire la différence de minute entre notre fuseau horaire et l'UTC référence
		var diffSeconds = (launchDate.getTime() - now.getTime()) / 1000 - (now.getTimezoneOffset() * 60);

		// On calcule le nombre de jour
		var days = Math.floor(diffSeconds / 86400);
		$daysDiv.html('<strong>'+ days + '</strong> ' + dayLabel + (days > 1? 's' : ''));

		// On calcule le nombre d'heure
		diffSeconds -= days * 86400;
		var hours = Math.floor(diffSeconds / 3600);
		$hoursDiv.html('<strong>' + hours + '</strong> ' + hourLabel + (hours >1? 's' : ''));

		// On calcule le nombre de minute
		diffSeconds -= hours * 3600;
		var minutes = Math.floor(diffSeconds / 60);
		$minutesDiv.html('<strong>' + minutes + '</strong> ' + minuteLabel + (minutes >1? 's' : ''));
		
		// On affiche le nombre de seconde
		var seconds = Math.floor(diffSeconds - minutes * 60);
		$secondsDiv.html('<strong>' + seconds + '</strong> ' + secondLabel + (seconds >1? 's' : ''));

		setTimeout(updateCountDown, 1000);
	}
});