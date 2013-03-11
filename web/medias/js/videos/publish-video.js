var $title, $category, $link, $description, errorMsgs = [], $slug, $loading, $modalPublish;

//Permet de vérifier si le formlaire de publication de vidéo est valide
//Lance une exception s il y a un problème
function isVideoValid() {
	if ($title == undefined || $title.val() == '') {
		throw errorMsgs['title'][locale];
	}
	if ($category == undefined || $category.val() == '') {
		throw errorMsgs['category'][locale];
	}
	if ($link == undefined || $link.val() == '' || !isVideoLinkValid($link.val())) {
		throw errorMsgs['link'][locale];
	}
}

//Permet de vérifier si le lien passé en paramètres est valide (youtube ou dailymotion)
function isVideoLinkValid(link) {
	if (	link.indexOf('youtube.com/watch?v=', 0) >= 0 || 
		link.indexOf('youtube.com/v/', 0) >= 0 || 
		link.indexOf('youtu.be/', 0) >= 0 ||
		link.indexOf('dailymotion.com', 0) >= 0) {
		return true;
	}
	return false;
}

//Permet de publier la vidéo
//Peut déclencher une exception
function publishVideo() {
	//On vérifie la validité des champs
	isVideoValid();
	
	//On prépare les données à envoyer
	var data = {title: $title.val(), category: $category.val(), link: $link.val(), description: $description.val(), slug: $slug.val()};
	
	//On demande la création de la vidéo en AJAX
	$.ajax({
		type: 'POST',
		url:  Routing.generate('videos_publish_ajax', {_locale: locale}),
		data: data,
		dataType: 'json'
	}).done(function(slug){
		$loading.hide();
		$modalPublish.modal('hide');
		window.location = Routing.generate('videos_detail', {_locale: locale, slug: slug});
	}).fail(function(msg){
		$loading.hide();
		$modalPublish.modal('hide');
		displayMessage(msg.responseText, ERROR_ALERT);
	});
}

//Permet de récupérer les champs d une modal via son id
function initModalData(modalId) {
	$modalPublish = $(modalId);
	
	//Récupération des champs du formulaire
	$title = $modalPublish.find('.video-publish-title');
	$category = $modalPublish.find('.video-publish-category');
	$link = $modalPublish.find('.video-publish-link');
	$description = $modalPublish.find('.video-publish-description');
	$slug = $modalPublish.find('.video-publish-slug');
	$loading = $modalPublish.find('.modal-video-loading-img');
}

//Permet d'organiser les catégories dans l'ordre alphabétique
function orderCategories($target) {
	$target.parent().html($target.sort( function(a, b) {
		return $(a).html().toLowerCase() > $(b).html().toLowerCase() ? 1 : -1
	}));
}

$(document).ready(function() {	
	//Initialisation des messages d'erreurs
	errorMsgs['title'] = [];
	errorMsgs['title']['fr'] = 'Le titre de la vidéo n\'est pas valide.';
	errorMsgs['title']['en'] = 'The video title is not valid.';
	errorMsgs['category'] = [];
	errorMsgs['category']['fr'] = 'La catégorie de la vidéo n\'est pas valide.';
	errorMsgs['category']['en'] = 'The video category is not valid.';
	errorMsgs['link'] = [];
	errorMsgs['link']['fr'] = 'La lien de la vidéo fourni n\'est pas valide.';
	errorMsgs['link']['en'] = 'The video link is not valid.';
	
	//Clic sur le bouton publish de la page de listing des vidéos ou de la page de détail
	$('#video-publish-action, #video-edit-action').click(function() {
		var target = $(this).data('target');
		if (target != undefined && target != '') {
			initModalData('#' + target);
		} else {
			initModalData('#modal-video-publish-');
		}
		$modalPublish.modal('show');
		return false;
	});
	
	//Clic sur le bouton publish de la modal
	$('.modal .modal-btn-publish').click(function(e) {
		e.preventDefault();
		$loading.show();
		try {
			publishVideo()
		} catch (err) {
			displayMessage(err, ERROR_ALERT);
			$loading.hide();
		}
	});
	
	$('div.video-list a.edit-action').click(function() {
		var target = $(this).data('target');
		initModalData('#' + target);
		
		$modalPublish.modal('show');
		
		return false;
	});
	
	orderCategories($('.video-publish-category option'));
});