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

	// Objet jQuery sur tous les liens permettant de masquer un contenu
	var $showHideLabel = $('span.show-hide-label');

	$showHideLabel.mouseover(function() 
	{
		$(this).find('i').addClass('icon-white');
	});

	$showHideLabel.mouseout(function() 
	{
		$(this).find('i').removeClass('icon-white');
	});

	$showHideLabel.click(function()
	{
		var $icon = $(this).find('i'),
			$label = $(this).find('span.msg'), 
			tmpIconClass = $icon.attr('class'), 
			tmpLabel = $label.html(),
			$h2 = $(this).parent();

		$icon.attr('class', $(this).data('toggle-icon'));
		$label.html($(this).data('toggle-label'));

		$(this).data('toggle-icon', tmpIconClass);
		$(this).data('toggle-label', tmpLabel);

		$(this).parent().parent().find('p, form').slideToggle('slow');
		$(this).parent().toggleClass('no-margin');
		
		$.cookie('display-' + $h2.data('content-name'), $h2.hasClass('no-margin'), { expires: 30});
	});

	/**
	 * GESTION DES COOKIES
	 */

	$('div.presentation-container').find('h2').each(function()
	{
		if ($.cookie('display-' + $(this).data('content-name')) == 'true') {
			$(this).find('span.show-hide-label').trigger('click');
		}
	});

	/**
	 * GESTION DU DÉPOT D'EMAIL
	 */

	var isValidEmail = false,
		$submitFormButton = $('input[type="email"]#user_email').parent().find('a.btn.submit-btn'),
		$loaderImg = $('img.loader'),
		$submitBtnLabel = $('span.submit-btn-label');

	$('a.submit-btn').click(function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}
		else {
			switch ($submitBtnLabel.html()) {
				case $submitBtnLabel.data('checking-label'):
					break;
				case $submitBtnLabel.data('submit-label'):
					$('form#leave-email-form').submit();
					break;
				case $submitBtnLabel.data('check-label'):
					console.log('Il faut vérifier !');
					break;
			}
		}
	});

	$('input[type="email"]#user_email').keypress(function(event)
	{
		var $this = $(this);
		if (event.which == 13) {
			event.preventDefault();
			if (validateEmail($(this).val())) {
				$loaderImg.show();
				$submitBtnLabel.html($submitBtnLabel.data('checking-label'));
				$.ajax({
					url: Routing.generate('launch_site_check_email'),
					type: 'POST',
					dataType: 'json',
					success: function(response)
					{
						$loaderImg.hide();
						if (response) {
							$('li.email-already-used').slideToggle();
							$this.focus();
						}
						else {
							$submitFormButton.toggleClass('btn-inverse');
							$submitBtnLabel.html($submitBtnLabel.data('submit-label'));

						}
					}
				});
			}
		}
	});

	$('input[type="email"]#user_email').keyup(function(event)
	{
		event.preventDefault();
		if($.trim($(this).val()) != '') {
			if (validateEmail($(this).val())) {
				$submitFormButton.removeClass('disabled');
			}
			else {
				$submitFormButton.addClass('disabled');
			}
		} 
	});
});