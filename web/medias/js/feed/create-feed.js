$(document).ready(function() {
	// Déclaration de variables globales
	superTags = $('input#tags-input').data('source'); // correspond au tableau contenant tous les super tags que l'utilisateur peut utiliser
	$hiddenSuperTagsInput = $('input#' + hiddenSuperTagsInputID); // variable représentant l'objet jQuery du champ caché de super tags
	
	// Détection du clic sur le widget de tags pour donner le focus au champ de recherche
	$('div.tags-widget').on('click', function() {
		$(this).find('input[type="text"]').focus();
	});

	// Détection de l'event du clic de l'utilisateur sur la croix d'un tag pour annuler une association feed/super tag
	$('ul.tags-container').on('click', 'li.tag a', function(event) {
		event.preventDefault();
		superTag = $(this).parent().find('em').html();
		superTags.push(superTag);
		$(this).parent().remove();
		$hiddenSuperTagsInput.val($hiddenSuperTagsInput.val().replace(superTag + ',', ''));
	});
	
	// Annulation de la soumission du formulaire sur la touche <entrée> et on associe le tag s'il existe au contenu
	$('input#tags-input').on('change', function(event) {
		if (event.which == 13) {
			event.preventDefault();
		}

		userValue = $(this).val();
		valuePosition = $.inArray(userValue, superTags);
		if (valuePosition !== -1) {
			if ($('li.tag').length > 0) {
				$('<li class="tag"><em>'+ userValue +'</em><a href="#">x</a></li>').insertAfter('li.tag:last');
			}
			else {
				$('<li class="tag"><em>'+ userValue +'</em><a href="#">x</a></li>').prependTo('ul.tags-container');
			}

			superTags.splice(valuePosition, 1);
			
			$hiddenSuperTagsInput.val($hiddenSuperTagsInput.val() + userValue + ',');
		}

		$(this).val('');
	});
	
	// Tweak pour pouvoir utiliser l'apparence custom des selectbox
	$('select').on('change', function() {
		$(this).parent().find('div.selectbox-widget span.option-value').html($(this).find(":selected").text());
	});
});
