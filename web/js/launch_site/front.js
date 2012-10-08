/**
 * @Author Eric Chau
 */
// Déclaration de variable pour gérer la fixation de la barre en haut de page ou non
var $win = $(window), 
	$actionBar = $('div.actions-bar'),
	actionBarTop = $actionBar.length && $actionBar.offset().top,
	isFixed = 0,
	$benchmarkChampion,
	benchmarkChampionTop,
	isBenchmarkChampionFixed;
		
function initBenchmark(){
	$benchmarkChampion = $('div#compare-champion-div-header'),
	benchmarkChampionTop = $benchmarkChampion.length && $benchmarkChampion.offset().top,
	isBenchmarkChampionFixed = 0;
	processScroll();
}

 //Fixation du subnav en fonction du scroll
function processScroll()
{
	var scrollTop = $win.scrollTop();

	if (scrollTop >= actionBarTop - 9.85 && !isFixed) {
		isFixed = 1;
		$actionBar.addClass('active');
		$actionBar.parent('.champions-handler-container').addClass('active');
		$benchmarkChampion.addClass('active');
		$benchmarkChampion.parent('#champion-comparator').addClass('active');
	} 
	else if (scrollTop <= actionBarTop - 9.85 && isFixed) {
		isFixed = 0;
		$actionBar.removeClass('active');
		$actionBar.parent('.champions-handler-container').removeClass('active');
		$benchmarkChampion.removeClass('active');
		$benchmarkChampion.parent('#champion-comparator').removeClass('active');
	}
}

var	$btnCompare = $('a#btn-compare'),
	$searchInputText = $('input[type="text"]#filter-value'),
	$btnComparisonList = $('a#drop-comparison-list'),
	$btnHelp = $('li.help-action'),
	$btnFilter = $('a#drop-filter-list'),
	isFirstActionAfterGT = false;

function shortcutListener(event) {
	if (isFirstActionAfterGT) {
		isFirstActionAfterGT = false;
		
		return false;
	}

	switch(event.which) {
		case 67: // touche 'c'
			if ($btnCompare.hasClass('disabled')) {
				return false;
			}

			$btnCompare.trigger('click');
			break;
		case 82: // touche 'r'
			$('.dropdown.open .dropdown-toggle').dropdown('toggle');
			$searchInputText[0].selectionStart = $searchInputText[0].selectionEnd = $searchInputText.val().length;
			break;
		case 76:
			$btnComparisonList.trigger('click');
			break;
		case 70:
			$btnFilter.trigger('click');
			break;
		case 86:
			$('.dropdown.open .dropdown-toggle').dropdown('toggle');
			$btnHelp.trigger('click');
		default:
			break;

	}
}

$(document).ready(function()
{	
	$('body').bind('keyup', function(event) 
	{
		shortcutListener(event);
	});

	$('input[type="text"]#filter-value').on('focus', function()
	{
		$('body').unbind('keyup');
	});

	$('input[type="text"]#filter-value').on('blur', function()
	{
		$('body').bind('keyup', function(event)
		{
			shortcutListener(event);
		});
	});

	//On active les tooltips
	$('#wrapper').on('mouseover', '.tooltip-anchor', function(){
		$(this).tooltip('show');
	});
	
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


	// Détection du clic sur les boutons "Qui sommes-nous" et "Je laisse mon e-mail"
	$('div.presentation-container div.link').on('click', function()
	{
		var $this = $(this);

		$('div.presentation-container div.content div.' + $this.data('content-name')).slideToggle(300, function() {
			actionBarTop = $actionBar.length && $actionBar.offset().top;
			benchmarkChampionTop = $benchmarkChampion.length && $benchmarkChampion.offset().top;
			processScroll();
		});
	});

	/**
	 * GESTION DES PRÉFÉRENCES UTILISATEURS POUR AFFICHER/MASQUER DU CONTENU
	 */	

	if (getItemFromLS('already-leave-email') == 'true') {
		$('div.presentation-container div.content div.registration').hide();
		actionBarTop = $actionBar.length && $actionBar.offset().top;
		if ($benchmarkChampion != undefined) {
			benchmarkChampionTop = $benchmarkChampion.length && $benchmarkChampion.offset().top;
		}
		processScroll();
	}

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

	// Écoute de la soumission du formulaire de dépôt d'e-mail pour soumettre le formulaire en AJAX
	$leaveEmailFormContainer.on('submit', 'form#leave-email-form', function(event) {
		event.preventDefault();
		$(this).ajaxSubmit({
			target: 'div#leave-email-form-container',
			success: function(response)
			{
				$('div#leave-email-form-container').on('hide', 'div#leave-email-success-modal', function()
				{
					$('div.presentation-container div.content div.registration').slideToggle();
				});

				saveItemInLS('already-leave-email', true);
			}
		});
	});
 
	initBenchmark();
 
	processScroll();
 
	 $win.on('scroll', processScroll);
});
