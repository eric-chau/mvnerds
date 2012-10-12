/**
 * @Author Eric Chau
 */
// Déclaration de variable pour gérer la fixation de la barre en haut de page ou non
var $win = $(window), 
	$actionBar,
	actionBarTop,
	isFixed = 0,
	$benchmarkChampion,
	benchmarkChampionTop,
	isBenchmarkChampionFixed,
	isGuideTourDisplay = false;
		
function initBenchmark(){
	$benchmarkChampion = $('div#compare-champion-div-header'),
	benchmarkChampionTop = $benchmarkChampion.length && $benchmarkChampion.offset().top,
	isBenchmarkChampionFixed = 0;
	processScroll();
}

 //Fixation du subnav en fonction du scroll
function processScroll()
{
	if (getItemFromLS('is_not_first_visit') != 'true') {
		$('li.help-action').trigger('click');
	}

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

$(document).ready(function()
{	
	$actionBar = $('div.actions-bar');
	actionBarTop =  $actionBar.length && $actionBar.offset().top;
	
	initBenchmark();
	
	//On active les tooltips
	$('#wrapper').on('mouseover', '.tooltip-anchor', function(){
		$(this).tooltip('show');
	});
	
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
 
	 $win.on('scroll', processScroll);
	 
	 $('.dropdown-toggle').dropdown();
});
