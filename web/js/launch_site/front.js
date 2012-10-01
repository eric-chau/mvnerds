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

	$('div.presentation-container div.link').on('click', function()
	{
		var $this = $(this);

		$('div.presentation-container div.content div.' + $this.data('content-name')).slideToggle();
	});

	/**
	 * GESTION DES PRÉFÉRENCES UTILISATEURS POUR AFFICHER/MASQUER DU CONTENU
	 */	
	/*$('div.presentation-container').find('h2').each(function()
	{
		var value = getItemFromLS('display-' + $(this).data('content-name'));
		if (value == 'true' || value == undefined) {
			toggleContentDisplay($(this));
		}
	});

	/**
	 * ON CHANGE LE LABEL 'LAISSER MON E-MAIL' EN MESSAGE DE REMERCIEMENT SI L'UTILISATEUR A DÉJA LAISSÉ LE SIEN
	 */
	/*$registrationH2 = $('div.registration-container h2');
	if (getItemFromLS('already-leave-email') == 'true') 
	{
		$registrationH2.find('span.h2-msg').html($registrationH2.data('success-label'));
	}*/

	/**
	 * LISTENER D'EVENEMENT
	 */

	// Écoute sur le clic d'un titre h2 pour masquer ou afficher le contenu (seulement les h2 contenu dans la div.presentation-container)
	$('div.presentation-container h2').on('click', function()
	{
		toggleContentDisplay($(this));
	});

	/**
	 * GESTION DU DÉPOT D'EMAIL
	 */

	// variable qui contient l'objet jQuery pour sélectionner le container du formulaire qui permet à l'utilisateur de laisser son e-mail
	var $leaveEmailFormContainer = $('div#leave-email-form-container');

	// Écoute du clic sur le bouton de soumission du formulaire
	$leaveEmailFormContainer.on('click', 'a.submit-btn', function(event)
	{
		event.preventDefault();
		if ($(this).hasClass('disabled')) {
			return false;
		}
		else if ($submitBtnLabel.html() == $submitBtnLabel.data('submit-label')) {
			$form.submit();
		}
	});

	// Écoute de chaque pression sur le clavier lorsque le focus est sur le champ de texte d'ajout d'e-mail
	$leaveEmailFormContainer.on('keypress', 'input[type="email"]#user_email', function(event)
	{
		var $this = $(this);
		if (event.which == 13) {
			event.preventDefault();
			if ($submitBtnLabel.html() == $submitBtnLabel.data('submit-label')) {
				$form.submit();
			}
		}
	});

	// Écoute de chaque pression sur le clavier lorsque le focus est sur le champ de texte d'ajout d'e-mail
	$leaveEmailFormContainer.on('keyup', 'input[type="email"]#user_email', function(event)
	{
		event.preventDefault();
		// Si l'utilisateur appuie sur les touches directionnelles, cela ne doit pas engendré de vérification, ni pour la touche entrée qui
		// bénéficie d'un traitement spécial
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

	$('li.search-action').on('click', function()
	{
		$(this).find('input[type="text"]').focus();
	});

	// Écoute de la soumission du formulaire de dépôt d'e-mail pour soumettre le formulaire en AJAX
	$leaveEmailFormContainer.on('submit', 'form#leave-email-form', function(event) {
		event.preventDefault();
		$(this).ajaxSubmit({
			target: 'div#leave-email-form-container',
			success: function(response)
			{
				$('div#leave-email-form-container').on('hide', 'div#leave-email-success-modal', function()
				{
					toggleContentDisplay($registrationH2);
					$registrationH2.find('span.h2-msg').html($registrationH2.data('success-label'));
				});

				saveItemInLS('already-leave-email', true);
			}
		});
	});

	/**
	 * Permet d'afficher ou de masquer le contenu d'une div XXX-container de la div.presentation-container; toggle également le label
	 * et l'icône des spans 
	 *
	 * @param jQuery<Object> $this correspond à un objet jQuery qui représente un h2 de la div.presentation-container
	 */
	function toggleContentDisplay($this)
	{
		var $icon = $this.find('i'),
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

		saveItemInLS('display-' + $this.data('content-name'), $this.hasClass('no-margin'));
	}

	var $win = $(window), 
		$nav = $('div.actions-bar'),
		navTop = $nav.length && $nav.offset().top,
		isFixed = 0;
 
	processScroll();
 
	 $win.on('scroll', processScroll);
	 
	 //Fixation du subnav en fonction du scroll
	 function processScroll()
	 {
	  	var scrollTop = $win.scrollTop();
	  
	  	if (scrollTop >= navTop && !isFixed) {
	    	isFixed = 1;
	    	$nav.addClass('active');
	  	} 
	  	else if (scrollTop <= navTop && isFixed) {
	    	isFixed = 0;
	    	$nav.removeClass('active');
	 	}
	}
});