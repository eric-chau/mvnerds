/**
 * @Author Eric Chau
 */

$(document).ready(function()
{
	/**
	 * GESTION DU COMPTE A REBOUR AVANT LA DATE DE SORTIE
	 */

	// Variable qui contient la date de lancement souhaité
	var launchDate = new Date(2012, 10, 5, 12, 00, 00);
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
		$daysDiv.html('<strong>'+ days + '</strong> jour' + (days > 1? 's' : ''));

		// On calcule le nombre d'heure
		diffSeconds -= days * 86400;
		var hours = Math.floor(diffSeconds / 3600);
		$hoursDiv.html('<strong>' + hours + '</strong> heure' + (hours >1? 's' : ''));

		// On calcule le nombre de minute
		diffSeconds -= hours * 3600;
		var minutes = Math.floor(diffSeconds / 60);
		$minutesDiv.html('<strong>' + minutes + '</strong> minute' + (minutes >1? 's' : ''));
		
		// On affiche le nombre de seconde
		var seconds = Math.floor(diffSeconds - minutes * 60);
		$secondsDiv.html('<strong>' + seconds + '</strong> seconde' + (seconds >1? 's' : ''));

		setTimeout(updateCountDown, 1000);
	}


	/**
	 * LISTENER D'EVENEMENT
	 */

	$('div.presentation-container h2').click(function()
	{
		var $this = $(this),
			$icon = $this.find('i'),
			$label = $this.find('span.msg'),
			$span = $this.find('span.show-hide-label'), 
			tmpIconClass = $icon.attr('class'), 
			tmpLabel = $label.html();

		$icon.attr('class', $span.data('toggle-icon'));
		$label.html($span.data('toggle-label'));

		$span.data('toggle-icon', tmpIconClass);
		$span.data('toggle-label', tmpLabel);

		$this.parent().find('div.content').slideToggle('slow');
		$this.toggleClass('no-margin');

		$.cookie('display-' + $this.data('content-name'), $this.hasClass('no-margin'));
	});

	/**
	 * GESTION DES COOKIES POUR AFFICHER/MASQUER DU CONTENU
	 */	
	$('div.presentation-container').find('h2').each(function()
	{
		if ($.cookie('display-' + $(this).data('content-name')) == 'true') {
			$(this).trigger('click');
		}
	});

	/**
	 * GESTION DU DÉPOT D'EMAIL
	 */

	var isValidEmail = false,
		$submitFormButton = $('form a.btn.submit-btn'),
		$loaderImg = $('img.loader'),
		$submitBtnLabel = $('span.submit-btn-label'),
		$form = $('form#leave-email-form');

	if ($.trim($submitBtnLabel.val()) == '') {
		$submitFormButton.addClass('disabled');
	}

	$('a.submit-btn').click(function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}
		else if ($submitBtnLabel.html() == $submitBtnLabel.data('submit-label')) {
			$form.submit();
		}
	});

	$('input[type="email"]#user_email').keypress(function(event)
	{
		var $this = $(this);
		if (event.which == 13) {
			event.preventDefault();
			if ($submitBtnLabel.html() == $submitBtnLabel.data('submit-label')) {
				$form.submit();
			}
		}
	});

	$('input[type="email"]#user_email').keyup(function(event)
	{
		event.preventDefault();
		if (event.which == 13 || (event.which >= 37 && event.which <= 40)) {
			return false;
		}

		var $this = $(this);
		if($.trim($this.val()) != '') {
			if (validateEmail($this.val())) {
				$loaderImg.show();
				$submitBtnLabel.html($submitBtnLabel.data('checking-label'));
				$.ajax({
					url: Routing.generate('launch_site_check_email'),
					data: {
						email_to_check: $this.val()
					},
					type: 'POST',
					dataType: 'json',
					success: function(response)
					{
						console.log(response);
						$loaderImg.hide();
						if (response) {
							$submitFormButton.addClass('disabled');
							$submitBtnLabel.html($submitBtnLabel.data('used-email-label'));
							$this.focus();
						}
						else {
							$submitBtnLabel.html($submitBtnLabel.data('submit-label'));
							$submitFormButton.removeClass('disabled');
						}
					}
				});
			}
			else {
				$submitBtnLabel.html($submitBtnLabel.data('check-label'));
				$submitFormButton.addClass('disabled');
			}
		} 
	});
});